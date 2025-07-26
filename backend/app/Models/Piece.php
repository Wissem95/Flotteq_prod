<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piece extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'reference',
        'prix_unitaire',
        'categorie',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    /**
     * Get all piece changees using this piece.
     */
    public function piecesChangees(): HasMany
    {
        return $this->hasMany(PieceChangee::class);
    }
}
