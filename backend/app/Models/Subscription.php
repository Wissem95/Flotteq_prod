<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Subscription extends Model
{
    use HasFactory, UsesLandlordConnection;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features',
        'limits',
        'is_active',
        'is_popular',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get user subscriptions for this plan.
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Scope: Only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if subscription includes a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get limit for a specific resource.
     */
    public function getLimit(string $resource): ?int
    {
        $limits = $this->limits ?? [];
        return $limits[$resource] ?? null;
    }

    /**
     * Check if subscription allows unlimited usage for a resource.
     */
    public function isUnlimited(string $resource): bool
    {
        return $this->getLimit($resource) === null;
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Get features as a formatted list.
     */
    public function getFeatureListAttribute(): array
    {
        return $this->features ?? [];
    }

    /**
     * Get limits as a formatted list.
     */
    public function getLimitListAttribute(): array
    {
        return $this->limits ?? [];
    }
}
