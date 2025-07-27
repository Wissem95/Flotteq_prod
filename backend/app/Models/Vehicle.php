<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'marque',
        'modele',
        'immatriculation',
        'vin',
        'annee',
        'couleur',
        'kilometrage',
        'carburant',
        'transmission',
        'puissance',
        'purchase_date',
        'purchase_price',
        'status',
        'last_ct_date',
        'next_ct_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'last_ct_date' => 'date',
        'next_ct_date' => 'date',
        'kilometrage' => 'integer',
        'puissance' => 'integer',
        'annee' => 'integer',
    ];

    /**
     * Get the user that owns the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns the vehicle.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all invoices for this vehicle.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all repairs for this vehicle.
     */
    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Get all maintenances for this vehicle.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get all technical inspections for this vehicle.
     */
    public function technicalInspections(): HasMany
    {
        return $this->hasMany(TechnicalInspection::class);
    }

    /**
     * Get all photos for this vehicle.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get all transactions for this vehicle.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all états des lieux for this vehicle.
     */
    public function etatsDesLieux(): HasMany
    {
        return $this->hasMany(EtatDesLieux::class);
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/x-empty']);
    }

    /**
     * Register media conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('images');
    }
}
