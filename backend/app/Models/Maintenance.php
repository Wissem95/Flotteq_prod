<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'reason',
        'description',
        'maintenance_date',
        'mileage',
        'cost',
        'workshop',
        'next_maintenance',
        'status',
        'notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance' => 'date',
        'cost' => 'decimal:2',
        'mileage' => 'integer',
    ];

    /**
     * Get the vehicle that owns the maintenance.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
