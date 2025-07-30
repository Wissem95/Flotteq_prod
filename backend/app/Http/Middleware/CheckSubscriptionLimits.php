<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tenant;

class CheckSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $feature
     * @param  string|null  $limitType
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $feature, ?string $limitType = null)
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant) {
            return response()->json([
                'error' => 'No tenant found',
                'message' => 'Tenant information is required'
            ], 403);
        }

        $tenant = $user->tenant;

        // Check feature access
        if (!$tenant->hasFeatureAccess($feature)) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "The feature '{$feature}' is not available in your current subscription plan",
                'upgrade_required' => true,
                'current_plan' => $tenant->activeSubscription()?->subscription?->name ?? 'No active plan'
            ], 403);
        }

        // Check limits if specified
        if ($limitType && $tenant->hasReachedLimit($limitType)) {
            $subscription = $tenant->activeSubscription();
            $limit = $subscription?->subscription?->getLimit($limitType);
            
            return response()->json([
                'error' => 'Limit reached',
                'message' => "You have reached the limit for {$limitType} in your current subscription plan",
                'current_limit' => $limit,
                'upgrade_required' => true,
                'current_plan' => $subscription?->subscription?->name ?? 'No active plan'
            ], 403);
        }

        return $next($request);
    }
}