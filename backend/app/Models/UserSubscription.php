<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class UserSubscription extends Model
{
    use UsesLandlordConnection;

    protected $fillable = [
        'user_id',
        'tenant_id',         // New tenant-centric approach
        'subscription_id',
        'is_active',
        'status',            // New status field
        'starts_at',         
        'ends_at',           
        'trial_ends_at',
        'auto_renew',
        'billing_cycle',
        'amount_paid',       // New billing field
        'price_at_subscription',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'price_at_subscription' => 'decimal:2',
        'metadata' => 'array'
    ];

    /**
     * Get the subscription plan
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the tenant that owns the subscription (tenant-centric approach)
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
    
    /**
     * Get the user that created the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope for tenant subscriptions
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for tenant active subscription
     */
    public function scopeActiveTenantSubscription($query, int $tenantId)
    {
        return $query->forTenant($tenantId)->active();
    }

    /**
     * Check if subscription is currently active
     */
    public function isActive(): bool
    {
        return $this->is_active &&
               $this->status === 'active' &&
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Check if subscription is in trial period
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription has expired
     */
    public function hasExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Get days remaining in subscription
     */
    public function daysRemaining(): ?int
    {
        if (!$this->ends_at) {
            return null; // No end date = unlimited
        }

        return max(0, now()->diffInDays($this->ends_at, false));
    }

    /**
     * Get days remaining in trial
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->trial_ends_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Static method to get active subscription for tenant
     */
    public static function getActiveTenantSubscription(int $tenantId)
    {
        return static::activeTenantSubscription($tenantId)->with('subscription')->first();
    }
}
