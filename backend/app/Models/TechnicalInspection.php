<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'inspection_date',
        'expiration_date',
        'result',
        'organization',
        'report_number',
        'observations',
        'cost',
        'report_file',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'expiration_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the vehicle that owns the controle technique.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
