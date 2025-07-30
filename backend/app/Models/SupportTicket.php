<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_id',
        'subject',
        'description',
        'priority',
        'status',
        'category',
        'messages',
        'metadata',
        'internal_notes',
        'first_response_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'messages' => 'array',
        'metadata' => 'array',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the tenant for this ticket.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent assigned to this ticket.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by priority.
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Unassigned tickets.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('agent_id');
    }

    /**
     * Scope: Open tickets (not resolved or closed).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_user']);
    }

    /**
     * Scope: Overdue tickets (no response in 24h for high priority, 48h for others).
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('priority', 'high')
              ->where('created_at', '<', now()->subHours(24))
              ->whereNull('first_response_at');
        })->orWhere(function ($q) {
            $q->where('priority', '!=', 'high')
              ->where('created_at', '<', now()->subHours(48))
              ->whereNull('first_response_at');
        });
    }

    /**
     * Add a message to the ticket.
     */
    public function addMessage(array $message): void
    {
        $messages = $this->messages ?? [];
        $message['timestamp'] = now()->toISOString();
        $messages[] = $message;
        
        $this->update(['messages' => $messages]);

        // Mark first response if from agent
        if ($message['type'] === 'agent' && !$this->first_response_at) {
            $this->update(['first_response_at' => now()]);
        }
    }

    /**
     * Assign ticket to agent.
     */
    public function assignTo(int $agentId): void
    {
        $this->update([
            'agent_id' => $agentId,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Close ticket.
     */
    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Reopen ticket.
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Get response time in hours.
     */
    public function getResponseTimeAttribute(): ?int
    {
        if (!$this->first_response_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->first_response_at);
    }

    /**
     * Get resolution time in hours.
     */
    public function getResolutionTimeAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->resolved_at);
    }

    /**
     * Check if ticket is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->first_response_at) {
            return false;
        }

        $hoursLimit = $this->priority === 'high' ? 24 : 48;
        return $this->created_at->diffInHours(now()) > $hoursLimit;
    }
}