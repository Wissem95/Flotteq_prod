<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
        'department_specific',
        'department_id',
        'permissions',
        'restrictions',
        'can_manage_employees',
        'can_access_finances',
        'can_manage_tenants',
        'can_manage_partners',
        'can_view_analytics',
        'can_export_data',
        'max_subordinates',
        'approval_limit',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'department_specific' => 'boolean',
        'can_manage_employees' => 'boolean',
        'can_access_finances' => 'boolean',
        'can_manage_tenants' => 'boolean',
        'can_manage_partners' => 'boolean',
        'can_view_analytics' => 'boolean',
        'can_export_data' => 'boolean',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'restrictions' => 'array',
        'metadata' => 'array',
        'approval_limit' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(EmployeeDepartment::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasMany(InternalEmployee::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(EmployeePermission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->withPivot('granted_at', 'granted_by')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where(function ($q) use ($departmentId) {
            $q->where('department_specific', false)
              ->orWhere('department_id', $departmentId);
        });
    }

    public function hasPermission($permission)
    {
        // Vérifier dans les permissions du rôle
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        // Vérifier dans les permissions liées
        return $this->permissions()->where('code', $permission)->exists();
    }

    public function canManageEmployee(InternalEmployee $employee)
    {
        if (!$this->can_manage_employees) {
            return false;
        }

        // Vérifier si l'employé est dans un niveau inférieur
        if ($employee->role && $employee->role->level >= $this->level) {
            return false;
        }

        return true;
    }

    public function getEffectivePermissions()
    {
        $permissions = $this->permissions ?? [];
        
        // Ajouter les permissions liées
        foreach ($this->permissions as $permission) {
            $permissions[] = $permission->code;
        }

        return array_unique($permissions);
    }
}