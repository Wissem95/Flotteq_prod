<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EtatDesLieux extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'etat_des_lieux';

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'tenant_id',
        'type',
        'conducteur',
        'kilometrage',
        'notes',
        'photos',
        'is_validated',
        'validated_at',
        'validated_by',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_validated' => 'boolean',
        'validated_at' => 'datetime',
        'kilometrage' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('etat_des_lieux_photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('etat_des_lieux_photos');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->performOnCollections('etat_des_lieux_photos');
    }

    public function getPhotoPositions(): array
    {
        return [
            'avant_droit' => 'Avant Droit',
            'avant' => 'Avant',
            'avant_gauche' => 'Avant Gauche',
            'arriere_gauche' => 'Arrière Gauche',
            'arriere' => 'Arrière',
            'arriere_droit' => 'Arrière Droit',
            'interieur_avant' => 'Intérieur Avant',
            'interieur_arriere' => 'Intérieur Arrière',
            'compteur' => 'Compteur Kilométrique'
        ];
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}