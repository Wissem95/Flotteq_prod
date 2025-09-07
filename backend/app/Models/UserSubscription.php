<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class UserSubscription extends Model
{
    use UsesLandlordConnection;

    protected $fillable = [
        'user_id',           // Correspond à tenant_id dans la logique métier
        'subscription_id',
        'is_active',
        'starts_at',         // Correspond à start_date dans la logique métier
        'ends_at',           // Correspond à end_date dans la logique métier
        'trial_ends_at',
        'auto_renew',
        'billing_cycle',
        'price_at_subscription',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
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
     * Get the tenant that owns the subscription
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'user_id');
    }
}
