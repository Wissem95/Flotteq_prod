<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'feature',
        'page',
        'action',
        'metadata',
        'properties',
        'session_id',
        'user_agent',
        'ip_address',
        'referrer',
        'duration_ms',
        'is_error',
        'error_message',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'properties' => 'array',
        'duration_ms' => 'integer',
        'is_error' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    /**
     * Get the tenant for this event.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user for this event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by event type.
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: Filter by feature.
     */
    public function scopeFeature($query, string $feature)
    {
        return $query->where('feature', $feature);
    }

    /**
     * Scope: Filter by date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Only errors.
     */
    public function scopeErrors($query)
    {
        return $query->where('is_error', true);
    }

    /**
     * Scope: By session.
     */
    public function scopeSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Create a page view event.
     */
    public static function pageView(int $tenantId, ?int $userId, string $page, array $metadata = []): self
    {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'page_view',
            'page' => $page,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Create an action event.
     */
    public static function action(int $tenantId, ?int $userId, string $feature, string $action, array $metadata = []): self
    {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'action',
            'feature' => $feature,
            'action' => $action,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Create an error event.
     */
    public static function error(int $tenantId, ?int $userId, string $errorMessage, array $metadata = []): self
    {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'error',
            'is_error' => true,
            'error_message' => $errorMessage,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Create a feature usage event.
     */
    public static function featureUsage(int $tenantId, ?int $userId, string $feature, ?int $durationMs = null, array $properties = []): self
    {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'event_type' => 'feature_usage',
            'feature' => $feature,
            'duration_ms' => $durationMs,
            'properties' => $properties,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Get events aggregated by feature for a tenant.
     */
    public static function getFeatureUsageByTenant(int $tenantId, string $startDate, string $endDate): array
    {
        return self::where('tenant_id', $tenantId)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->selectRaw('feature, COUNT(*) as usage_count, AVG(duration_ms) as avg_duration')
            ->whereNotNull('feature')
            ->groupBy('feature')
            ->orderByDesc('usage_count')
            ->get()
            ->toArray();
    }

    /**
     * Get most popular pages for a tenant.
     */
    public static function getPopularPagesByTenant(int $tenantId, string $startDate, string $endDate): array
    {
        return self::where('tenant_id', $tenantId)
            ->where('event_type', 'page_view')
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->selectRaw('page, COUNT(*) as view_count')
            ->whereNotNull('page')
            ->groupBy('page')
            ->orderByDesc('view_count')
            ->get()
            ->toArray();
    }

    /**
     * Get error rate for a tenant.
     */
    public static function getErrorRateByTenant(int $tenantId, string $startDate, string $endDate): float
    {
        $totalEvents = self::where('tenant_id', $tenantId)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();

        if ($totalEvents === 0) {
            return 0;
        }

        $errorEvents = self::where('tenant_id', $tenantId)
            ->where('is_error', true)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->count();

        return ($errorEvents / $totalEvents) * 100;
    }
}