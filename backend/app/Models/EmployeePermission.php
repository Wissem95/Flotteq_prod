<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'module',
        'resource',
        'action',
        'scope',
        'conditions',
        'dependencies',
        'is_system',
        'is_dangerous',
        'requires_2fa',
        'requires_approval',
        'approval_level',
        'audit_log',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_dangerous' => 'boolean',
        'requires_2fa' => 'boolean',
        'requires_approval' => 'boolean',
        'audit_log' => 'boolean',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'dependencies' => 'array',
        'metadata' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(EmployeeRole::class, 'role_permissions', 'permission_id', 'role_id')
                    ->withPivot('granted_at', 'granted_by')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByResource($query, $resource)
    {
        return $query->where('resource', $resource);
    }

    public function scopeDangerous($query)
    {
        return $query->where('is_dangerous', true);
    }

    public function hasDependency($permissionCode)
    {
        return $this->dependencies && in_array($permissionCode, $this->dependencies);
    }

    public function getDependentPermissions()
    {
        if (!$this->dependencies) {
            return collect();
        }

        return self::whereIn('code', $this->dependencies)->get();
    }

    public function checkConditions($context = [])
    {
        if (!$this->conditions) {
            return true;
        }

        // Implémenter la logique de vérification des conditions
        // basée sur le contexte fourni
        foreach ($this->conditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition($condition, $context)
    {
        // Logique d'évaluation des conditions
        // À personnaliser selon les besoins
        return true;
    }

    public static function getSystemPermissions()
    {
        return self::where('is_system', true)->get();
    }

    public static function getPermissionsByRole($roleId)
    {
        return self::whereHas('roles', function ($query) use ($roleId) {
            $query->where('employee_roles.id', $roleId);
        })->get();
    }
}