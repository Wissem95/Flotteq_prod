<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'tenant_id',
        'type', // 'purchase' or 'sale'
        'date',
        'price',
        'mileage',
        'seller_buyer_name',
        'seller_buyer_contact',
        'reason',
        'status', // 'pending', 'completed', 'cancelled'
        'notes',
        'contract_file_path',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'mileage' => 'integer',
    ];

    /**
     * Get the vehicle that owns the transaction.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns the transaction.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}