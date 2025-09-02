<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type',
        'severity',
        'category',
        'title',
        'message',
        'details',
        'source',
        'component',
        'affected_services',
        'affected_tenants',
        'affected_users_count',
        'trigger_value',
        'threshold_value',
        'metric_name',
        'metric_value',
        'started_at',
        'detected_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'auto_resolved',
        'escalated',
        'escalated_to',
        'escalated_at',
        'notification_sent',
        'notification_channels',
        'notification_recipients',
        'response_time_seconds',
        'downtime_minutes',
        'impact_score',
        'priority',
        'status',
        'recurring',
        'occurrence_count',
        'last_occurrence',
        'next_check',
        'action_required',
        'action_taken',
        'preventive_measures',
        'root_cause',
        'documentation_link',
        'ticket_id',
        'metadata'
    ];

    protected $casts = [
        'details' => 'array',
        'affected_services' => 'array',
        'affected_tenants' => 'array',
        'trigger_value' => 'decimal:4',
        'threshold_value' => 'decimal:4',
        'metric_value' => 'decimal:4',
        'started_at' => 'datetime',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
        'auto_resolved' => 'boolean',
        'escalated' => 'boolean',
        'notification_sent' => 'boolean',
        'notification_channels' => 'array',
        'notification_recipients' => 'array',
        'impact_score' => 'integer',
        'priority' => 'integer',
        'recurring' => 'boolean',
        'occurrence_count' => 'integer',
        'last_occurrence' => 'datetime',
        'next_check' => 'datetime',
        'action_required' => 'boolean',
        'preventive_measures' => 'array',
        'metadata' => 'array'
    ];

    public function acknowledgedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'resolved_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(InternalEmployee::class, 'escalated_to');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['new', 'acknowledged', 'in_progress']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('detected_at', '>=', now()->subHours($hours));
    }

    public function acknowledge($employeeId)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $employeeId
        ]);
    }

    public function resolve($employeeId, $notes = null)
    {
        $responseTime = $this->detected_at ? 
            now()->diffInSeconds($this->detected_at) : null;
        
        $downtime = $this->started_at ? 
            now()->diffInMinutes($this->started_at) : null;

        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $employeeId,
            'resolution_notes' => $notes,
            'response_time_seconds' => $responseTime,
            'downtime_minutes' => $downtime
        ]);
    }

    public function escalate($toEmployeeId)
    {
        $this->update([
            'escalated' => true,
            'escalated_to' => $toEmployeeId,
            'escalated_at' => now(),
            'priority' => min($this->priority + 1, 5)
        ]);
    }

    public function getImpactLevel()
    {
        if ($this->impact_score >= 80) {
            return 'critical';
        } elseif ($this->impact_score >= 60) {
            return 'high';
        } elseif ($this->impact_score >= 40) {
            return 'medium';
        } elseif ($this->impact_score >= 20) {
            return 'low';
        }
        
        return 'minimal';
    }

    public function shouldEscalate()
    {
        // Auto-escalation après 30 minutes pour les alertes critiques
        if ($this->severity === 'critical' && 
            !$this->escalated && 
            $this->detected_at->diffInMinutes(now()) > 30) {
            return true;
        }

        // Auto-escalation après 2 heures pour les alertes hautes
        if ($this->severity === 'high' && 
            !$this->escalated && 
            $this->detected_at->diffInHours(now()) > 2) {
            return true;
        }

        return false;
    }

    public function sendNotifications()
    {
        if ($this->notification_sent) {
            return;
        }

        // Logique d'envoi de notifications
        // selon les canaux et destinataires configurés
        
        $this->update(['notification_sent' => true]);
    }

    public static function createSystemAlert($type, $severity, $message, $details = [])
    {
        return self::create([
            'alert_type' => $type,
            'severity' => $severity,
            'category' => 'system',
            'title' => $message,
            'message' => $message,
            'details' => $details,
            'detected_at' => now(),
            'started_at' => now(),
            'status' => 'new',
            'priority' => $severity === 'critical' ? 5 : 3
        ]);
    }
}