<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'description',
        'repair_date',
        'total_cost',
        'workshop',
        'mileage',
        'status',
        'notes',
    ];

    protected $casts = [
        'repair_date' => 'date',
        'total_cost' => 'decimal:2',
        'mileage' => 'integer',
    ];

    /**
     * Get the vehicle that owns the repair.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get all pieces changed during this repair.
     */
    public function piecesChangees(): HasMany
    {
        return $this->hasMany(PieceChangee::class);
    }
}
