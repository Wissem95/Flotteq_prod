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
use Illuminate\Support\Facades\Schema;

class SubscriptionsController extends Controller
{
    /**
     * Get all subscriptions for internal admin view
     */
    public function index(Request $request): JsonResponse
    {
        $query = UserSubscription::with(['subscription', 'tenant'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('subscription_id')) {
            $query->where('subscription_id', $request->subscription_id);
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $subscriptions = $query->paginate($request->get('per_page', 20));

        return response()->json($subscriptions);
    }

    /**
     * Create a new subscription assignment (assign a plan to a tenant)
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'auto_renew' => 'boolean',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'metadata' => 'nullable|array'
        ]);

        try {
            // Find the primary/admin user for this tenant
            $primaryUser = \App\Models\User::where('tenant_id', $validated['tenant_id'])
                ->where('is_active', true)
                ->orderBy('created_at')  // Get the first/primary user
                ->first();

            if (!$primaryUser) {
                return response()->json([
                    'error' => 'No active user found for this tenant',
                    'message' => 'Cannot assign subscription to tenant without active users'
                ], 400);
            }

            // Check if tenant already has an active subscription (via any of its users)
            $existingSubscription = UserSubscription::whereIn('user_id', 
                    \App\Models\User::where('tenant_id', $validated['tenant_id'])->pluck('id')
                )
                ->where('is_active', true)
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'error' => 'Tenant already has an active subscription',
                    'existing_subscription' => $existingSubscription->load('subscription')
                ], 400);
            }

            // Get the subscription plan
            $plan = Subscription::findOrFail($validated['subscription_id']);

            // Prepare subscription data (only use columns that exist in current DB)
            $subscriptionData = [
                'user_id' => $primaryUser->id,                    // Assign to primary user of tenant
                'subscription_id' => $validated['subscription_id'],
                'starts_at' => $validated['start_date'],          
                'ends_at' => $validated['end_date'],               
                'is_active' => true
            ];

            // Add optional fields only if they exist in database
            if (Schema::hasColumn('user_subscriptions', 'billing_cycle')) {
                $subscriptionData['billing_cycle'] = $validated['billing_cycle'];
            }
            if (Schema::hasColumn('user_subscriptions', 'price_at_subscription')) {
                $subscriptionData['price_at_subscription'] = $plan->price;
            }
            if (Schema::hasColumn('user_subscriptions', 'auto_renew')) {
                $subscriptionData['auto_renew'] = $validated['auto_renew'] ?? false;
            }
            if (Schema::hasColumn('user_subscriptions', 'metadata')) {
                $subscriptionData['metadata'] = $validated['metadata'] ?? [];
            }

            // Handle trial period if specified
            if (isset($validated['trial_days']) && $validated['trial_days'] > 0 && 
                Schema::hasColumn('user_subscriptions', 'trial_ends_at')) {
                $subscriptionData['trial_ends_at'] = now()->addDays($validated['trial_days']);
            }

            // Create the subscription
            $userSubscription = UserSubscription::create($subscriptionData);

            // Load relationships for response  
            $userSubscription->load(['subscription']);
            
            // Add tenant info to response
            $response = [
                'message' => 'Subscription created successfully',
                'subscription' => $userSubscription,
                'tenant' => [
                    'id' => $validated['tenant_id'],
                    'name' => Tenant::find($validated['tenant_id'])->name
                ],
                'assigned_to_user' => [
                    'id' => $primaryUser->id,
                    'name' => trim($primaryUser->first_name . ' ' . $primaryUser->last_name)
                ]
            ];

            return response()->json($response, 201);

        } catch (\Exception $e) {
            \Log::error('Error creating subscription: ' . $e->getMessage(), [
                'tenant_id' => $validated['tenant_id'] ?? null,
                'subscription_id' => $validated['subscription_id'] ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to create subscription',
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Get subscription statistics for dashboard
     */
    public function getStats(): JsonResponse
    {
        $totalSubscriptions = UserSubscription::count();
        $activeSubscriptions = UserSubscription::where('is_active', true)->count();
        $expiredSubscriptions = UserSubscription::where('ends_at', '<', now())->count();  // Fixed: end_date → ends_at
        $trialSubscriptions = UserSubscription::whereBetween('created_at', [now()->subDays(30), now()])->count();
        
        // Monthly revenue calculation
        $monthlyRevenue = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->where('user_subscriptions.is_active', true)
            ->sum('subscriptions.price');

        // Revenue distribution by plan
        $revenueByPlan = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->where('user_subscriptions.is_active', true)
            ->select('subscriptions.name as plan', DB::raw('sum(subscriptions.price) as revenue'), DB::raw('count(*) as subscribers'))
            ->groupBy('subscriptions.name')
            ->get()
            ->map(function ($item) {
                return [
                    'plan' => $item->plan,
                    'revenue' => (float) $item->revenue,
                    'subscribers' => (int) $item->subscribers
                ];
            });

        // Subscription distribution by plan (for compatibility)
        $subscriptionsByPlan = UserSubscription::join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->select('subscriptions.name', DB::raw('count(*) as count'))
            ->groupBy('subscriptions.name')
            ->get();

        // Growth statistics
        $thisMonth = UserSubscription::whereBetween('created_at', [now()->startOfMonth(), now()])->count();
        $lastMonth = UserSubscription::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();
        $growthRate = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0;

        return response()->json([
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'trial_subscriptions' => $trialSubscriptions,
            'monthly_revenue' => round((float) $monthlyRevenue, 2),     // Fixed: cast to float
            'revenue_by_plan' => $revenueByPlan,
            'subscriptions_by_plan' => $subscriptionsByPlan,
            'growth_rate' => round((float) $growthRate, 2),             // Fixed: cast to float
            'conversion_metrics' => [
                'trial_to_paid' => 85.4, // Mock data - implement proper calculation
                'churn_rate' => 5.2,
                'lifetime_value' => 1250.0
            ]
        ]);
    }

    /**
     * Get all subscription plans
     */
    public function getPlans(): JsonResponse
    {
        $plans = Subscription::orderBy('sort_order')->get();

        return response()->json($plans);
    }

    /**
     * Create a new subscription plan
     */
    public function createPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:monthly,yearly',
            'features' => 'required|array',
            'limits' => 'nullable|array',
            'max_vehicles' => 'sometimes|integer|min:-1',
            'max_users' => 'sometimes|integer|min:-1',
            'support_level' => 'sometimes|string|in:basic,premium,enterprise',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0',
            'metadata' => 'nullable|array'
        ]);

        // Transform frontend data to backend structure
        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'billing_cycle' => $validated['billing_cycle'],
            'features' => $validated['features'],
            'is_active' => $validated['is_active'] ?? true,
            'is_popular' => $validated['is_popular'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
            'metadata' => $validated['metadata'] ?? []
        ];

        // Handle limits from frontend format
        if (isset($validated['limits'])) {
            $data['limits'] = $validated['limits'];
        } else {
            // Build limits from individual fields if provided
            $limits = [];
            if (isset($validated['max_vehicles'])) {
                $limits['vehicles'] = $validated['max_vehicles'];
            }
            if (isset($validated['max_users'])) {
                $limits['users'] = $validated['max_users'];
            }
            if (isset($validated['support_level'])) {
                $limits['support_tickets'] = match($validated['support_level']) {
                    'enterprise' => -1,
                    'premium' => 20,
                    default => 5
                };
            }
            $data['limits'] = $limits;
        }

        // Store additional pricing info in metadata if provided
        if (isset($validated['price_monthly']) || isset($validated['price_yearly'])) {
            $data['metadata']['pricing'] = [
                'monthly' => $validated['price_monthly'] ?? $validated['price'],
                'yearly' => $validated['price_yearly'] ?? ($validated['price'] * 12 * 0.8)
            ];
        }

        $plan = Subscription::create($data);

        return response()->json([
            'message' => 'Subscription plan created successfully',
            'plan' => $plan
        ], 201);
    }

    /**
     * Update a subscription plan
     */
    public function updatePlan(Request $request, Subscription $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'currency' => 'string|size:3',
            'billing_cycle' => 'in:monthly,yearly',
            'features' => 'array',
            'limits' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Subscription plan updated successfully',
            'plan' => $plan->fresh()
        ]);
    }

    /**
     * Toggle a subscription plan status (active/inactive)
     */
    public function togglePlanStatus(Subscription $plan): JsonResponse
    {
        $plan->update([
            'is_active' => !$plan->is_active
        ]);

        return response()->json([
            'message' => 'Plan status updated successfully',
            'plan' => $plan->fresh()
        ]);
    }

    /**
     * Delete a subscription plan
     */
    public function deletePlan(Subscription $plan): JsonResponse
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = UserSubscription::where('subscription_id', $plan->id)
            ->where('is_active', true)
            ->count();

        if ($activeSubscriptions > 0) {
            return response()->json([
                'error' => 'Cannot delete plan with active subscriptions'
            ], 400);
        }

        $plan->delete();

        return response()->json([
            'message' => 'Subscription plan deleted successfully'
        ]);
    }
}