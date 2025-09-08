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
        'type',                    // maintenance_type → type (selon la DB actuelle)
        'reason',
        'description',
        'date',                    // maintenance_date → date (selon la DB actuelle)
        'scheduled_date',          // Nouvelle colonne pour planification
        'mileage',
        'cost',
        'garage',                  // workshop → garage (selon la DB actuelle)
        'status',                  // Nouvelle colonne
        'priority',                // Nouvelle colonne
        'notes',                   // Nouvelle colonne
        'next_maintenance_km',     // Nouvelle colonne
        'completed_at',            // Nouvelle colonne
    ];

    protected $casts = [
        'date' => 'date',
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
     * Scope for pending maintenances
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for overdue maintenances
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
                     ->where('status', '!=', 'cancelled')
                     ->where('scheduled_date', '<', now());
    }

    /**
     * Scope for upcoming maintenances
     */
    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('status', 'scheduled')
                     ->whereBetween('scheduled_date', [now(), now()->addDays($days)]);
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
