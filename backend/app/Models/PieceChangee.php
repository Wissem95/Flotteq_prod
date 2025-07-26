<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PieceChangee extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_id',
        'piece_id',
        'quantite',
        'prix_unitaire',
        'prix_total',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'prix_total' => 'decimal:2',
    ];

    /**
     * Get the repair that owns this piece changee.
     */
    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    /**
     * Get the piece used.
     */
    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }
}
