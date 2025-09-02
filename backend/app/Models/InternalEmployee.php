<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class InternalEmployee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'department_id',
        'role_id',
        'manager_id',
        'job_title',
        'employment_type',
        'hire_date',
        'termination_date',
        'status',
        'salary',
        'salary_currency',
        'salary_frequency',
        'benefits',
        'email_verified_at',
        'password',
        'two_factor_enabled',
        'two_factor_recovery_codes',
        'password_changed_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'permissions',
        'tenant_access',
        'can_access_all_tenants',
        'bio',
        'avatar',
        'emergency_contact',
        'work_schedule',
        'metadata'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'two_factor_enabled' => 'boolean',
        'can_access_all_tenants' => 'boolean',
        'salary' => 'decimal:2',
        'benefits' => 'array',
        'permissions' => 'array',
        'tenant_access' => 'array',
        'emergency_contact' => 'array',
        'work_schedule' => 'array',
        'metadata' => 'array',
        'two_factor_recovery_codes' => 'array',
    ];

    public function department()
    {
        return $this->belongsTo(EmployeeDepartment::class, 'department_id');
    }

    public function role()
    {
        return $this->belongsTo(EmployeeRole::class, 'role_id');
    }

    public function manager()
    {
        return $this->belongsTo(InternalEmployee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(InternalEmployee::class, 'manager_id');
    }

    public function managedDepartments()
    {
        return $this->hasMany(EmployeeDepartment::class, 'manager_id');
    }

    public function hasPermission($permission)
    {
        // Vérifier les permissions individuelles
        if ($this->permissions && in_array($permission, $this->permissions)) {
            return true;
        }

        // Vérifier les permissions du rôle
        if ($this->role && $this->role->hasPermission($permission)) {
            return true;
        }

        return false;
    }

    public function canAccessTenant($tenantId)
    {
        if ($this->can_access_all_tenants) {
            return true;
        }

        if ($this->tenant_access && in_array($tenantId, $this->tenant_access)) {
            return true;
        }

        return false;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }
}