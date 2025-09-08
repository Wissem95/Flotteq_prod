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
        'user_id',
        'maintenance_type',        // Colonne réelle dans DB
        'reason',
        'description',
        'maintenance_date',        // Colonne réelle dans DB
        'mileage',
        'cost',
        'workshop',                // Colonne réelle dans DB
        'status',                  // Existe dans DB originale
        'notes',                   // Existe dans DB originale
        'next_maintenance',        // Colonne réelle dans DB
        // Nouvelles colonnes (ajoutées par migration future)
        'scheduled_date',          
        'priority',                
        'next_maintenance_km',     
        'completed_at',            
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance' => 'date',
        'scheduled_date' => 'date',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2',
        'mileage' => 'integer',
        'next_maintenance_km' => 'integer',
    ];

    /**
     * Get the vehicle that owns the maintenance.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created the maintenance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for 'type' (compatibility with new naming)
     */
    public function getTypeAttribute(): ?string
    {
        return $this->maintenance_type;
    }

    /**
     * Mutator for 'type' (compatibility with new naming)
     */
    public function setTypeAttribute($value): void
    {
        $this->attributes['maintenance_type'] = $value;
    }

    /**
     * Accessor for 'date' (compatibility with new naming)
     */
    public function getDateAttribute(): ?string
    {
        return $this->maintenance_date ? $this->maintenance_date->format('Y-m-d') : null;
    }

    /**
     * Mutator for 'date' (compatibility with new naming)
     */
    public function setDateAttribute($value): void
    {
        $this->attributes['maintenance_date'] = $value;
    }

    /**
     * Accessor for 'garage' (compatibility with new naming)
     */
    public function getGarageAttribute(): ?string
    {
        return $this->workshop;
    }

    /**
     * Mutator for 'garage' (compatibility with new naming)
     */
    public function setGarageAttribute($value): void
    {
        $this->attributes['workshop'] = $value;
    }

    /**
     * Scope for pending maintenances
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue maintenances (compatible avec structure actuelle)
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('status', '!=', 'cancelled')
                     ->where(function ($q) {
                         // Si scheduled_date existe, l'utiliser, sinon utiliser next_maintenance
                         $q->where('scheduled_date', '<', now())
                           ->orWhere(function ($subQ) {
                               $subQ->whereNull('scheduled_date')
                                    ->where('next_maintenance', '<', now());
                           });
                     });
    }

    /**
     * Scope for upcoming maintenances (compatible avec structure actuelle)
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('status', 'scheduled')
                     ->where(function ($q) use ($days) {
                         // Si scheduled_date existe, l'utiliser, sinon utiliser next_maintenance
                         $q->whereBetween('scheduled_date', [now(), now()->addDays($days)])
                           ->orWhere(function ($subQ) use ($days) {
                               $subQ->whereNull('scheduled_date')
                                    ->whereBetween('next_maintenance', [now(), now()->addDays($days)]);
                           });
                     });
    }

    /**
     * Check if maintenance is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'completed' 
            && $this->status !== 'cancelled'
            && $this->scheduled_date 
            && $this->scheduled_date < now();
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'scheduled' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            'overdue' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority ?? 'medium') {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'blue'
        };
    }
}
