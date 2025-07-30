<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPartnerRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'partner_id',
        'distance',
        'is_preferred',
        'tenant_rating',
        'tenant_comment',
        'booking_count',
        'last_booking_at',
        'last_interaction_at',
        'custom_pricing',
    ];

    protected $casts = [
        'distance' => 'decimal:2',
        'is_preferred' => 'boolean',
        'tenant_rating' => 'decimal:2',
        'booking_count' => 'integer',
        'last_booking_at' => 'datetime',
        'last_interaction_at' => 'datetime',
        'custom_pricing' => 'array',
    ];

    /**
     * Get the tenant for this relation.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the partner for this relation.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Record a new booking.
     */
    public function recordBooking(): void
    {
        $this->increment('booking_count');
        $this->update([
            'last_booking_at' => now(),
            'last_interaction_at' => now(),
        ]);
    }

    /**
     * Record an interaction (rating, comment, etc.).
     */
    public function recordInteraction(): void
    {
        $this->update(['last_interaction_at' => now()]);
    }

    /**
     * Update tenant rating and update partner global rating.
     */
    public function updateRating(float $rating, ?string $comment = null): void
    {
        $oldRating = $this->tenant_rating;
        
        $this->update([
            'tenant_rating' => $rating,
            'tenant_comment' => $comment,
        ]);

        // Update partner's global rating
        $partner = $this->partner;
        if ($oldRating) {
            // Replace existing rating
            $totalRating = ($partner->rating * $partner->rating_count) - $oldRating + $rating;
            $partner->rating = $totalRating / $partner->rating_count;
        } else {
            // Add new rating
            $partner->updateRating($rating);
        }

        $this->recordInteraction();
    }
}