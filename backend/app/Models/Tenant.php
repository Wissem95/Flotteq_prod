<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory, UsesLandlordConnection;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users for this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all vehicles for this tenant.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get partners that have a relation with this tenant.
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'tenant_partner_relations')
            ->withPivot([
                'distance',
                'is_preferred',
                'tenant_rating',
                'tenant_comment',
                'booking_count',
                'last_booking_at',
                'last_interaction_at',
                'custom_pricing'
            ])
            ->withTimestamps();
    }

    /**
     * Get support tickets for this tenant.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get analytics events for this tenant.
     */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    /**
     * Get subscriptions for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscription for this tenant.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()->where('is_active', true)->first();
    }

    /**
     * Check if tenant has access to a specific feature.
     */
    public function hasFeatureAccess(string $feature): bool
    {
        $subscription = $this->activeSubscription();
        
        if (!$subscription || !$subscription->subscription) {
            return false;
        }

        $features = $subscription->subscription->features ?? [];
        return in_array($feature, $features);
    }

    /**
     * Check if tenant has reached a specific limit.
     */
    public function hasReachedLimit(string $limitType): bool
    {
        $subscription = $this->activeSubscription();
        
        if (!$subscription || !$subscription->subscription) {
            return true; // No subscription = all limits reached
        }

        $limits = $subscription->subscription->limits ?? [];
        
        if (!isset($limits[$limitType])) {
            return false; // No limit defined = unlimited
        }

        $currentUsage = $this->getCurrentUsage($limitType);
        return $currentUsage >= $limits[$limitType];
    }

    /**
     * Get current usage for a specific limit type.
     */
    private function getCurrentUsage(string $limitType): int
    {
        return match ($limitType) {
            'vehicles' => $this->vehicles()->count(),
            'users' => $this->users()->count(),
            'support_tickets' => $this->supportTickets()->whereMonth('created_at', now()->month)->count(),
            default => 0,
        };
    }
}
