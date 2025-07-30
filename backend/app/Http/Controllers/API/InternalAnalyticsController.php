<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class InternalAnalyticsController extends Controller
{
    /**
     * Get global platform analytics (Internal only).
     */
    public function globalMetrics(Request $request): JsonResponse
    {
        if (!$request->user()->isInternal()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $dateRange = $this->getDateRange($request);

        $metrics = [
            // Platform overview
            'platform_overview' => [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('is_active', true)->count(),
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_vehicles' => Vehicle::count(),
                'total_events' => AnalyticsEvent::whereBetween('occurred_at', $dateRange)->count(),
            ],

            // Usage metrics
            'usage_metrics' => [
                'daily_active_users' => $this->getDailyActiveUsers($dateRange),
                'popular_features' => $this->getPopularFeatures($dateRange),
                'page_views' => $this->getPageViews($dateRange),
                'error_rate' => $this->getGlobalErrorRate($dateRange),
            ],

            // Tenant metrics
            'tenant_metrics' => [
                'most_active_tenants' => $this->getMostActiveTenants($dateRange),
                'tenant_growth' => $this->getTenantGrowth($dateRange),
                'subscription_distribution' => $this->getSubscriptionDistribution(),
            ],

            // Support metrics
            'support_metrics' => [
                'total_tickets' => SupportTicket::whereBetween('created_at', $dateRange)->count(),
                'avg_resolution_time' => SupportTicket::whereBetween('created_at', $dateRange)
                    ->whereNotNull('resolved_at')
                    ->avg('resolution_time'),
                'tickets_by_priority' => SupportTicket::whereBetween('created_at', $dateRange)
                    ->selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->get()
                    ->pluck('count', 'priority'),
            ],
        ];

        return response()->json($metrics);
    }

    /**
     * Get analytics for a specific tenant (Internal only).
     */
    public function tenantAnalytics(Request $request, int $tenantId): JsonResponse
    {
        if (!$request->user()->isInternal()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $tenant = Tenant::findOrFail($tenantId);
        $dateRange = $this->getDateRange($request);

        $analytics = [
            'tenant_info' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'is_active' => $tenant->is_active,
                'created_at' => $tenant->created_at,
            ],

            'usage_overview' => [
                'total_users' => $tenant->users()->count(),
                'active_users' => $tenant->users()->where('is_active', true)->count(),
                'total_vehicles' => $tenant->vehicles()->count(),
                'total_events' => $tenant->analyticsEvents()
                    ->whereBetween('occurred_at', $dateRange)
                    ->count(),
            ],

            'feature_usage' => AnalyticsEvent::getFeatureUsageByTenant(
                $tenantId,
                $dateRange[0],
                $dateRange[1]
            ),

            'popular_pages' => AnalyticsEvent::getPopularPagesByTenant(
                $tenantId,
                $dateRange[0],
                $dateRange[1]
            ),

            'error_rate' => AnalyticsEvent::getErrorRateByTenant(
                $tenantId,
                $dateRange[0],
                $dateRange[1]
            ),

            'daily_activity' => $this->getTenantDailyActivity($tenantId, $dateRange),

            'support_tickets' => [
                'total' => $tenant->supportTickets()->whereBetween('created_at', $dateRange)->count(),
                'open' => $tenant->supportTickets()->open()->count(),
                'avg_resolution_time' => $tenant->supportTickets()
                    ->whereBetween('created_at', $dateRange)
                    ->whereNotNull('resolved_at')
                    ->avg('resolution_time'),
            ],
        ];

        return response()->json($analytics);
    }

    /**
     * Record analytics event (Both Internal and Tenant).
     */
    public function recordEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
            'feature' => 'nullable|string',
            'page' => 'nullable|string',
            'action' => 'nullable|string',
            'metadata' => 'nullable|array',
            'properties' => 'nullable|array',
            'session_id' => 'nullable|string',
            'duration_ms' => 'nullable|integer',
            'is_error' => 'nullable|boolean',
            'error_message' => 'nullable|string',
        ]);

        $user = $request->user();
        
        // For internal users, we might not have a tenant_id
        $tenantId = $user->tenant_id ?? 1; // Default to system tenant

        $event = AnalyticsEvent::create([
            ...$validated,
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'referrer' => $request->header('referer'),
            'occurred_at' => now(),
        ]);

        return response()->json(['success' => true, 'event_id' => $event->id]);
    }

    /**
     * Get user behavior analytics (Internal only).
     */
    public function userBehavior(Request $request): JsonResponse
    {
        if (!$request->user()->isInternal()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $dateRange = $this->getDateRange($request);

        $behavior = [
            'user_journeys' => $this->getUserJourneys($dateRange),
            'session_duration' => $this->getSessionDurations($dateRange),
            'bounce_rate' => $this->getBounceRate($dateRange),
            'feature_adoption' => $this->getFeatureAdoption($dateRange),
            'user_retention' => $this->getUserRetention($dateRange),
        ];

        return response()->json($behavior);
    }

    /**
     * Get performance metrics (Internal only).
     */
    public function performanceMetrics(Request $request): JsonResponse
    {
        if (!$request->user()->isInternal()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $dateRange = $this->getDateRange($request);

        $performance = [
            'average_page_load_time' => AnalyticsEvent::whereBetween('occurred_at', $dateRange)
                ->whereNotNull('duration_ms')
                ->avg('duration_ms'),
                
            'slowest_pages' => AnalyticsEvent::whereBetween('occurred_at', $dateRange)
                ->whereNotNull('duration_ms')
                ->selectRaw('page, AVG(duration_ms) as avg_duration')
                ->groupBy('page')
                ->orderByDesc('avg_duration')
                ->limit(10)
                ->get(),
                
            'error_frequency' => AnalyticsEvent::whereBetween('occurred_at', $dateRange)
                ->where('is_error', true)
                ->selectRaw('error_message, COUNT(*) as count')
                ->groupBy('error_message')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
                
            'api_response_times' => $this->getAPIResponseTimes($dateRange),
        ];

        return response()->json($performance);
    }

    // Helper methods

    private function getDateRange(Request $request): array
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        
        return [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ];
    }

    private function getDailyActiveUsers(array $dateRange): array
    {
        return AnalyticsEvent::whereBetween('occurred_at', $dateRange)
            ->selectRaw('DATE(occurred_at) as date, COUNT(DISTINCT user_id) as active_users')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getPopularFeatures(array $dateRange): array
    {
        return AnalyticsEvent::whereBetween('occurred_at', $dateRange)
            ->whereNotNull('feature')
            ->selectRaw('feature, COUNT(*) as usage_count')
            ->groupBy('feature')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getPageViews(array $dateRange): array
    {
        return AnalyticsEvent::whereBetween('occurred_at', $dateRange)
            ->where('event_type', 'page_view')
            ->selectRaw('page, COUNT(*) as view_count')
            ->groupBy('page')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getGlobalErrorRate(array $dateRange): float
    {
        $totalEvents = AnalyticsEvent::whereBetween('occurred_at', $dateRange)->count();
        
        if ($totalEvents === 0) {
            return 0;
        }

        $errorEvents = AnalyticsEvent::whereBetween('occurred_at', $dateRange)
            ->where('is_error', true)
            ->count();

        return ($errorEvents / $totalEvents) * 100;
    }

    private function getMostActiveTenants(array $dateRange): array
    {
        return AnalyticsEvent::whereBetween('occurred_at', $dateRange)
            ->selectRaw('tenant_id, COUNT(*) as event_count')
            ->with('tenant:id,name')
            ->groupBy('tenant_id')
            ->orderByDesc('event_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'tenant' => $item->tenant,
                    'event_count' => $item->event_count,
                ];
            })
            ->toArray();
    }

    private function getTenantGrowth(array $dateRange): array
    {
        return Tenant::whereBetween('created_at', $dateRange)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as new_tenants')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getSubscriptionDistribution(): array
    {
        // TODO: Implement subscription distribution
        return [];
    }

    private function getTenantDailyActivity(int $tenantId, array $dateRange): array
    {
        return AnalyticsEvent::where('tenant_id', $tenantId)
            ->whereBetween('occurred_at', $dateRange)
            ->selectRaw('DATE(occurred_at) as date, COUNT(*) as events, COUNT(DISTINCT user_id) as active_users')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getUserJourneys(array $dateRange): array
    {
        // TODO: Implement user journey analysis
        return [];
    }

    private function getSessionDurations(array $dateRange): array
    {
        // TODO: Implement session duration calculation
        return [];
    }

    private function getBounceRate(array $dateRange): float
    {
        // TODO: Implement bounce rate calculation
        return 0;
    }

    private function getFeatureAdoption(array $dateRange): array
    {
        // TODO: Implement feature adoption metrics
        return [];
    }

    private function getUserRetention(array $dateRange): array
    {
        // TODO: Implement user retention analysis
        return [];
    }

    private function getAPIResponseTimes(array $dateRange): array
    {
        // TODO: Implement API response time tracking
        return [];
    }
}