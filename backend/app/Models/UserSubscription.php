<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class UserSubscription extends Model
{
    use UsesLandlordConnection;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'is_active',
        'start_date',
        'end_date',
        'trial_ends_at',
        'auto_renew',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'trial_ends_at' => 'datetime',
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
        return $this->belongsTo(Tenant::class);
    }
}
