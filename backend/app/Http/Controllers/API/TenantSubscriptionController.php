<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantSubscriptionController extends Controller
{
    /**
     * Get available subscription plans for tenant selection
     */
    public function getAvailablePlans(): JsonResponse
    {
        try {
            $plans = Subscription::active()
                ->ordered()
                ->get()
                ->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'currency' => $plan->currency ?? 'EUR',
                        'billing_cycle' => $plan->billing_cycle,
                        'features' => $plan->features ?? [],
                        'limits' => $plan->limits ?? [],
                        'is_popular' => $plan->is_popular ?? false,
                        'formatted_price' => $plan->formatted_price
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plans'
            ], 500);
        }
    }

    /**
     * Get current subscription for the authenticated tenant
     */
    public function getCurrentSubscription(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->user()->tenant_id ?? $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID not found'
                ], 400);
            }

            $userSubscription = UserSubscription::with(['subscription'])
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();

            if (!$userSubscription) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No active subscription found'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $userSubscription->id,
                    'plan' => [
                        'id' => $userSubscription->subscription->id,
                        'name' => $userSubscription->subscription->name,
                        'description' => $userSubscription->subscription->description,
                        'price' => $userSubscription->subscription->price,
                        'currency' => $userSubscription->subscription->currency ?? 'EUR',
                        'billing_cycle' => $userSubscription->subscription->billing_cycle,
                        'features' => $userSubscription->subscription->features ?? [],
                        'limits' => $userSubscription->subscription->limits ?? []
                    ],
                    'status' => $userSubscription->is_active ? 'active' : 'inactive',
                    'start_date' => $userSubscription->start_date,
                    'end_date' => $userSubscription->end_date,
                    'trial_ends_at' => $userSubscription->trial_ends_at,
                    'auto_renew' => $userSubscription->auto_renew,
                    'is_trial' => $userSubscription->trial_ends_at && Carbon::now()->lt($userSubscription->trial_ends_at),
                    'days_remaining' => $userSubscription->end_date ? Carbon::now()->diffInDays($userSubscription->end_date, false) : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current subscription'
            ], 500);
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'auto_renew' => 'boolean',
            'trial_days' => 'nullable|integer|min:0|max:90'
        ]);

        try {
            $tenantId = $request->user()->tenant_id ?? $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID not found'
                ], 400);
            }

            // Check if tenant already has an active subscription
            $existingSubscription = UserSubscription::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant already has an active subscription'
                ], 400);
            }

            // Get the subscription plan
            $plan = Subscription::findOrFail($validated['subscription_id']);

            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected plan is not available'
                ], 400);
            }

            // Calculate dates
            $startDate = Carbon::now();
            $trialDays = $validated['trial_days'] ?? 0;
            $trialEndsAt = $trialDays > 0 ? $startDate->copy()->addDays($trialDays) : null;
            
            // Calculate end date based on billing cycle
            if ($validated['billing_cycle'] === 'yearly') {
                $endDate = $trialEndsAt ? $trialEndsAt->copy()->addYear() : $startDate->copy()->addYear();
            } else {
                $endDate = $trialEndsAt ? $trialEndsAt->copy()->addMonth() : $startDate->copy()->addMonth();
            }

            DB::beginTransaction();

            try {
                // Create the subscription
                $userSubscription = UserSubscription::create([
                    'tenant_id' => $tenantId,
                    'subscription_id' => $plan->id,
                    'is_active' => true,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'trial_ends_at' => $trialEndsAt,
                    'auto_renew' => $validated['auto_renew'] ?? true,
                    'metadata' => [
                        'billing_cycle' => $validated['billing_cycle'],
                        'subscribed_at' => $startDate->toISOString(),
                        'trial_days' => $trialDays
                    ]
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully subscribed to the plan',
                    'data' => [
                        'subscription_id' => $userSubscription->id,
                        'plan_name' => $plan->name,
                        'start_date' => $userSubscription->start_date,
                        'end_date' => $userSubscription->end_date,
                        'trial_ends_at' => $userSubscription->trial_ends_at,
                        'is_trial' => $trialEndsAt !== null
                    ]
                ], 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe to the plan'
            ], 500);
        }
    }

    /**
     * Cancel current subscription
     */
    public function cancelSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'cancel_at_period_end' => 'boolean'
        ]);

        try {
            $tenantId = $request->user()->tenant_id ?? $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID not found'
                ], 400);
            }

            $userSubscription = UserSubscription::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();

            if (!$userSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found to cancel'
                ], 404);
            }

            $cancelAtPeriodEnd = $validated['cancel_at_period_end'] ?? false;

            if ($cancelAtPeriodEnd) {
                // Keep subscription active until end date
                $userSubscription->update([
                    'auto_renew' => false,
                    'metadata' => array_merge($userSubscription->metadata ?? [], [
                        'cancelled_at' => Carbon::now()->toISOString(),
                        'cancellation_reason' => $validated['reason'] ?? null,
                        'cancel_at_period_end' => true
                    ])
                ]);

                $message = 'Subscription will be cancelled at the end of the current period';
            } else {
                // Cancel immediately
                $userSubscription->update([
                    'is_active' => false,
                    'auto_renew' => false,
                    'end_date' => Carbon::now(),
                    'metadata' => array_merge($userSubscription->metadata ?? [], [
                        'cancelled_at' => Carbon::now()->toISOString(),
                        'cancellation_reason' => $validated['reason'] ?? null,
                        'cancel_at_period_end' => false
                    ])
                ]);

                $message = 'Subscription cancelled immediately';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cancelled_at' => Carbon::now(),
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                    'access_until' => $userSubscription->end_date
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription'
            ], 500);
        }
    }

    /**
     * Get subscription history for the tenant
     */
    public function getSubscriptionHistory(Request $request): JsonResponse
    {
        try {
            $tenantId = $request->user()->tenant_id ?? $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID not found'
                ], 400);
            }

            $subscriptions = UserSubscription::with(['subscription'])
                ->where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($userSubscription) {
                    return [
                        'id' => $userSubscription->id,
                        'plan_name' => $userSubscription->subscription->name ?? 'Unknown Plan',
                        'price' => $userSubscription->subscription->price ?? 0,
                        'currency' => $userSubscription->subscription->currency ?? 'EUR',
                        'billing_cycle' => $userSubscription->metadata['billing_cycle'] ?? 'monthly',
                        'status' => $userSubscription->is_active ? 'active' : 'inactive',
                        'start_date' => $userSubscription->start_date,
                        'end_date' => $userSubscription->end_date,
                        'trial_ends_at' => $userSubscription->trial_ends_at,
                        'cancelled_at' => $userSubscription->metadata['cancelled_at'] ?? null,
                        'cancellation_reason' => $userSubscription->metadata['cancellation_reason'] ?? null,
                        'created_at' => $userSubscription->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $subscriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription history'
            ], 500);
        }
    }

    /**
     * Check if tenant has access to a specific feature
     */
    public function checkFeatureAccess(Request $request, string $feature): JsonResponse
    {
        try {
            $tenantId = $request->user()->tenant_id ?? $request->header('X-Tenant-ID');
            
            if (!$tenantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID not found'
                ], 400);
            }

            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], 404);
            }

            $hasAccess = $tenant->hasFeatureAccess($feature);

            return response()->json([
                'success' => true,
                'data' => [
                    'feature' => $feature,
                    'has_access' => $hasAccess
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check feature access'
            ], 500);
        }
    }
}