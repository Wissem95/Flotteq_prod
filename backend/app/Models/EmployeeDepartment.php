<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_department_id',
        'manager_id',
        'is_active',
        'max_employees',
        'budget_allocation',
        'permissions',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'budget_allocation' => 'array',
        'permissions' => 'array',
        'metadata' => 'array',
    ];

    public function parentDepartment()
    {
        return $this->belongsTo(EmployeeDepartment::class, 'parent_department_id');
    }

    public function childDepartments()
    {
        return $this->hasMany(EmployeeDepartment::class, 'parent_department_id');
    }

    public function manager()
    {
        return $this->belongsTo(InternalEmployee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(InternalEmployee::class, 'department_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_department_id');
    }

    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parentDepartment;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parentDepartment;
        }
        
        return implode(' > ', $path);
    }

    public function getEmployeeCountAttribute()
    {
        return $this->employees()->count();
    }

    public function hasAvailableSlots()
    {
        if (!$this->max_employees) {
            return true;
        }
        
        return $this->employee_count < $this->max_employees;
    }
}