<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Partner extends Model
{
    use HasFactory, UsesLandlordConnection;

    protected $fillable = [
        'name',
        'type',
        'description',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'services',
        'pricing',
        'availability',
        'service_zone',
        'rating',
        'rating_count',
        'is_active',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'services' => 'array',
        'pricing' => 'array',
        'availability' => 'array',
        'service_zone' => 'array',
        'rating' => 'float',
        'rating_count' => 'integer',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get tenants that have a relation with this partner.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_partner_relations')
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
     * Get partner relations.
     */
    public function relations(): HasMany
    {
        return $this->hasMany(TenantPartnerRelation::class);
    }

    /**
     * Scope: Filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Only active partners.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only verified partners.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Partners near location.
     */
    public function scopeNearLocation($query, float $latitude, float $longitude, float $radiusKm = 50)
    {
        return $query->selectRaw('*, (
            6371 * acos(
                cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude))
            )
        ) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radiusKm)
            ->orderBy('distance');
    }

    /**
     * Calculate distance to coordinates.
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Update rating based on new rating.
     */
    public function updateRating(float $newRating): void
    {
        $totalRating = ($this->rating * $this->rating_count) + $newRating;
        $this->rating_count += 1;
        $this->rating = $totalRating / $this->rating_count;
        $this->save();
    }

    /**
     * Check if partner has specific service.
     */
    public function hasService(string $service): bool
    {
        return in_array($service, $this->services ?? []);
    }

    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(string $date): array
    {
        $availability = $this->availability ?? [];
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        return $availability[$dayOfWeek] ?? [];
    }
}