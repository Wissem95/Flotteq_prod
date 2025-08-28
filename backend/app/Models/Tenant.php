<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantCollection;

class Tenant extends Model implements IsTenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'domain',
        'database',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Create a new Eloquent Collection instance for tenants.
     */
    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    /**
     * Get the current tenant
     */
    public static function current(): ?IsTenant
    {
        return app('currentTenant');
    }

    /**
     * Check if this is the current tenant
     */
    public function isCurrent(): bool
    {
        $current = static::current();
        return $current && $current->getKey() === $this->getKey();
    }

    /**
     * Make this tenant the current tenant
     */
    public function makeCurrent(): IsTenant
    {
        app()->instance('currentTenant', $this);
        return $this;
    }

    /**
     * Check if there is a current tenant
     */
    public static function checkCurrent(): bool
    {
        return static::current() !== null;
    }

    /**
     * Forget the current tenant
     */
    public static function forgetCurrent(): void
    {
        app()->forgetInstance('currentTenant');
    }

    /**
     * Execute callback with this tenant as current
     */
    public function execute(callable $callable)
    {
        $originalTenant = static::current();
        $this->makeCurrent();
        
        try {
            return $callable();
        } finally {
            if ($originalTenant) {
                $originalTenant->makeCurrent();
            } else {
                static::forgetCurrent();
            }
        }
    }

    /**
     * Get tenant identifier for tasks
     */
    public function getTenantKey(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Get tenant identifier name
     */
    public function getTenantKeyName(): string
    {
        return $this->getKeyName();
    }

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
     * Get partner relations for this tenant.
     */
    public function partnerRelations(): HasMany
    {
        return $this->hasMany(TenantPartnerRelation::class);
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
