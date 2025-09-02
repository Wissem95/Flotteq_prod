<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'description',
        'type',
        'status',
        'rollout_percentage',
        'target_users',
        'target_tenants',
        'target_plans',
        'target_regions',
        'exclude_users',
        'exclude_tenants',
        'conditions',
        'variants',
        'default_variant',
        'start_date',
        'end_date',
        'dependencies',
        'parent_flag_id',
        'category',
        'tags',
        'risk_level',
        'impact_areas',
        'rollback_plan',
        'monitoring_metrics',
        'success_criteria',
        'failure_threshold',
        'auto_disable_on_error',
        'error_count',
        'last_error_at',
        'enabled_by',
        'enabled_at',
        'disabled_by',
        'disabled_at',
        'tested_by',
        'tested_at',
        'approved_by',
        'approved_at',
        'documentation_url',
        'jira_ticket',
        'notes',
        'analytics_enabled',
        'track_usage',
        'usage_count',
        'unique_users_count',
        'conversion_impact',
        'performance_impact',
        'metadata'
    ];

    protected $casts = [
        'rollout_percentage' => 'integer',
        'target_users' => 'array',
        'target_tenants' => 'array',
        'target_plans' => 'array',
        'target_regions' => 'array',
        'exclude_users' => 'array',
        'exclude_tenants' => 'array',
        'conditions' => 'array',
        'variants' => 'array',
        'dependencies' => 'array',
        'tags' => 'array',
        'impact_areas' => 'array',
        'rollback_plan' => 'array',
        'monitoring_metrics' => 'array',
        'success_criteria' => 'array',
        'auto_disable_on_error' => 'boolean',
        'analytics_enabled' => 'boolean',
        'track_usage' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_error_at' => 'datetime',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
        'tested_at' => 'datetime',
        'approved_at' => 'datetime',
        'conversion_impact' => 'decimal:2',
        'performance_impact' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function parentFlag()
    {
        return $this->belongsTo(FeatureFlag::class, 'parent_flag_id');
    }

    public function childFlags()
    {
        return $this->hasMany(FeatureFlag::class, 'parent_flag_id');
    }

    public function enabledBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'enabled_by');
    }

    public function disabledBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'disabled_by');
    }

    public function testedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'tested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(InternalEmployee::class, 'approved_by');
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    public function isEnabledFor($userId = null, $tenantId = null)
    {
        // Vérifier le statut global
        if ($this->status !== 'enabled') {
            return false;
        }

        // Vérifier les dates
        if ($this->start_date && $this->start_date > now()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        // Vérifier les exclusions
        if ($userId && $this->exclude_users && in_array($userId, $this->exclude_users)) {
            return false;
        }

        if ($tenantId && $this->exclude_tenants && in_array($tenantId, $this->exclude_tenants)) {
            return false;
        }

        // Vérifier les cibles spécifiques
        if ($this->target_users && !empty($this->target_users)) {
            if (!$userId || !in_array($userId, $this->target_users)) {
                return false;
            }
        }

        if ($this->target_tenants && !empty($this->target_tenants)) {
            if (!$tenantId || !in_array($tenantId, $this->target_tenants)) {
                return false;
            }
        }

        // Vérifier le pourcentage de déploiement
        if ($this->rollout_percentage < 100) {
            $hash = crc32($userId . $this->key);
            $bucket = $hash % 100;
            
            if ($bucket >= $this->rollout_percentage) {
                return false;
            }
        }

        // Vérifier les dépendances
        if ($this->dependencies) {
            foreach ($this->dependencies as $dependencyKey) {
                $dependency = self::where('key', $dependencyKey)->first();
                if (!$dependency || !$dependency->isEnabledFor($userId, $tenantId)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getVariantFor($userId)
    {
        if (!$this->variants || empty($this->variants)) {
            return $this->default_variant;
        }

        // Déterminer la variante basée sur l'utilisateur
        $hash = crc32($userId . $this->key . 'variant');
        $bucket = $hash % 100;
        
        $cumulative = 0;
        foreach ($this->variants as $variant) {
            $cumulative += $variant['percentage'] ?? 0;
            if ($bucket < $cumulative) {
                return $variant['name'];
            }
        }

        return $this->default_variant;
    }

    public function enable($employeeId)
    {
        $this->update([
            'status' => 'enabled',
            'enabled_by' => $employeeId,
            'enabled_at' => now()
        ]);
    }

    public function disable($employeeId, $reason = null)
    {
        $this->update([
            'status' => 'disabled',
            'disabled_by' => $employeeId,
            'disabled_at' => now(),
            'notes' => $reason
        ]);
    }

    public function recordUsage($userId = null)
    {
        if (!$this->track_usage) {
            return;
        }

        $this->increment('usage_count');
        
        if ($userId) {
            // Enregistrer l'utilisateur unique
            FeatureUsageStat::create([
                'feature_key' => $this->key,
                'user_id' => $userId,
                'used_at' => now()
            ]);
        }
    }

    public function recordError()
    {
        $this->increment('error_count');
        $this->update(['last_error_at' => now()]);

        // Auto-désactivation si le seuil est atteint
        if ($this->auto_disable_on_error && 
            $this->failure_threshold && 
            $this->error_count >= $this->failure_threshold) {
            $this->disable(null, 'Auto-disabled due to error threshold');
        }
    }

    public static function checkFeature($key, $userId = null, $tenantId = null)
    {
        $flag = self::where('key', $key)->first();
        
        if (!$flag) {
            return false;
        }

        return $flag->isEnabledFor($userId, $tenantId);
    }
}