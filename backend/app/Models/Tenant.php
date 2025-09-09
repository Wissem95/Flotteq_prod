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
    public static function current(): ?static
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
    public function makeCurrent(): static
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
    public static function forgetCurrent(): ?static
    {
        app()->forgetInstance('currentTenant');
        return null;
    }

    /**
     * Execute callback with this tenant as current
     */
    public function execute(callable $callable): mixed
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
     * Forget this tenant instance
     */
    public function forget(): static
    {
        if ($this->isCurrent()) {
            static::forgetCurrent();
        }
        return $this;
    }

    /**
     * Get database name for this tenant
     */
    public function getDatabaseName(): string
    {
        return $this->database ?? config('database.connections.'.config('database.default').'.database');
    }

    /**
     * Execute callback within tenant context
     */
    public function callback(callable $callable): \Closure
    {
        return \Closure::fromCallable($callable);
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
     * Get subscriptions for this tenant (tenant-centric approach).
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'tenant_id');
    }

    /**
     * Get active subscription for this tenant (improved tenant-centric).
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->with('subscription')
            ->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    /**
     * Get current subscription plan for this tenant.
     */
    public function getCurrentPlan()
    {
        $tenantSubscription = $this->activeSubscription();
        if ($tenantSubscription && $tenantSubscription->subscription) {
            return $tenantSubscription->subscription;
        }

        // Fallback: try via users (compatibility during migration)
        $userIds = $this->users()->pluck('id');
        if ($userIds->isNotEmpty()) {
            $userSubscription = UserSubscription::with('subscription')
                ->whereIn('user_id', $userIds)
                ->where('is_active', true)
                ->first();
            
            return $userSubscription?->subscription;
        }

        return null;
    }

    /**
     * Check if tenant can add vehicles.
     */
    public function canAddVehicles(int $count = 1): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return false; // No plan = no additions allowed
        }
        
        $maxVehicles = $plan->max_vehicles ?? 1;
        $currentCount = $this->vehicles()->count();
        
        return ($currentCount + $count) <= $maxVehicles;
    }

    /**
     * Check if tenant can add users.
     */
    public function canAddUsers(int $count = 1): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return false; // No plan = no additions allowed
        }
        
        $maxUsers = $plan->max_users ?? 1;
        $currentCount = $this->users()->count();
        
        return ($currentCount + $count) <= $maxUsers;
    }

    /**
     * Get subscription limits summary.
     */
    public function getSubscriptionLimits(): array
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return [
                'plan_name' => 'Aucun plan',
                'vehicles_used' => $this->vehicles()->count(),
                'vehicles_limit' => 0,
                'users_used' => $this->users()->count(),
                'users_limit' => 0,
                'vehicles_available' => 0,
                'users_available' => 0
            ];
        }

        $vehiclesUsed = $this->vehicles()->count();
        $usersUsed = $this->users()->count();
        $vehiclesLimit = $plan->max_vehicles ?? 1;
        $usersLimit = $plan->max_users ?? 1;

        return [
            'plan_name' => $plan->name,
            'plan_code' => $plan->code ?? 'unknown',
            'vehicles_used' => $vehiclesUsed,
            'vehicles_limit' => $vehiclesLimit,
            'users_used' => $usersUsed,
            'users_limit' => $usersLimit,
            'vehicles_available' => max(0, $vehiclesLimit - $vehiclesUsed),
            'users_available' => max(0, $usersLimit - $usersUsed),
            'vehicles_at_limit' => $vehiclesUsed >= $vehiclesLimit,
            'users_at_limit' => $usersUsed >= $usersLimit
        ];
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

    /**
     * Suspend this tenant.
     */
    public function suspend(string $reason = null): self
    {
        $this->update([
            'status' => 'suspended',
            'is_active' => false
        ]);

        // Log the suspension reason if provided
        if ($reason) {
            $this->analyticsEvents()->create([
                'event_type' => 'tenant_suspended',
                'event_data' => json_encode(['reason' => $reason]),
                'user_id' => null,
                'created_at' => now()
            ]);
        }

        return $this;
    }

    /**
     * Activate this tenant.
     */
    public function activate(): self
    {
        $this->update([
            'status' => 'active',
            'is_active' => true
        ]);

        // Log the activation
        $this->analyticsEvents()->create([
            'event_type' => 'tenant_activated',
            'event_data' => json_encode(['activated_at' => now()]),
            'user_id' => null,
            'created_at' => now()
        ]);

        return $this;
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Scope to get only active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope to get only suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }
}
