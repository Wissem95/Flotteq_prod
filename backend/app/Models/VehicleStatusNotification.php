<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleStatusNotification extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'tenant_id',
        'old_status',
        'new_status',
        'message',
        'reason',
        'is_read',
        'priority',
        'type'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createForStatusChange(Vehicle $vehicle, string $oldStatus, string $newStatus, ?string $reason = null): self
    {
        // Messages courts et intuitifs
        $message = match($newStatus) {
            'active' => '🟢 Remis en service',
            'en_maintenance' => '🔧 Mis en maintenance',
            'en_reparation' => '🔨 Envoyé en réparation',
            'hors_service' => '🔴 Mis hors service',
            'vendu' => '💰 Véhicule vendu',
            default => "Statut modifié: $newStatus"
        };

        $priority = 'medium';
        if ($newStatus === 'hors_service') {
            $priority = 'high';
        } elseif ($newStatus === 'en_maintenance' || $newStatus === 'en_reparation') {
            $priority = 'medium';
        } elseif ($newStatus === 'active') {
            $priority = 'low';
        }

        return self::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $vehicle->user_id,
            'tenant_id' => $vehicle->tenant_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'message' => $message,
            'reason' => $reason,
            'is_read' => false,
            'priority' => $priority,
            'type' => 'status_change'
        ]);
    }
}