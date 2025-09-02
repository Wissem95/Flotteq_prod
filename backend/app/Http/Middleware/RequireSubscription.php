<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tenant;

class RequireSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Authentication required'
            ], 401);
        }

        // Get tenant ID from user or header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        
        if (!$tenantId) {
            return response()->json([
                'error' => 'No tenant found',
                'message' => 'Tenant information is required'
            ], 403);
        }

        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return response()->json([
                'error' => 'Invalid tenant',
                'message' => 'Tenant not found'
            ], 403);
        }

        // Check if tenant has an active subscription
        $activeSubscription = $tenant->activeSubscription();
        
        if (!$activeSubscription) {
            return response()->json([
                'error' => 'Subscription required',
                'message' => 'This feature requires an active subscription',
                'subscription_required' => true,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name
                ],
                'action_required' => 'select_subscription_plan'
            ], 402); // 402 Payment Required
        }

        // Check if subscription is expired
        if ($activeSubscription->end_date && $activeSubscription->end_date->isPast()) {
            return response()->json([
                'error' => 'Subscription expired',
                'message' => 'Your subscription has expired. Please renew to continue using this feature',
                'subscription_expired' => true,
                'expired_at' => $activeSubscription->end_date,
                'plan_name' => $activeSubscription->subscription?->name ?? 'Unknown Plan',
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name
                ],
                'action_required' => 'renew_subscription'
            ], 402); // 402 Payment Required
        }

        // Check if subscription is in trial and trial has expired
        if ($activeSubscription->trial_ends_at && 
            $activeSubscription->trial_ends_at->isPast() && 
            !$activeSubscription->subscription) {
            return response()->json([
                'error' => 'Trial expired',
                'message' => 'Your free trial has expired. Please select a subscription plan to continue',
                'trial_expired' => true,
                'trial_ended_at' => $activeSubscription->trial_ends_at,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name
                ],
                'action_required' => 'select_subscription_plan'
            ], 402); // 402 Payment Required
        }

        // Add subscription info to request for controllers to use if needed
        $request->merge([
            'tenant' => $tenant,
            'active_subscription' => $activeSubscription
        ]);

        return $next($request);
    }
}