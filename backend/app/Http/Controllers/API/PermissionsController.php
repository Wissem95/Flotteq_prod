<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmployeeRole;
use App\Models\EmployeePermission;
use App\Models\EmployeeDepartment;
use App\Models\InternalEmployee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionsController extends Controller
{
    // === GESTION DES RÔLES ===

    public function getRoles(Request $request)
    {
        try {
            $query = EmployeeRole::with(['department', 'employees', 'permissions'])
                ->withCount('employees');

            if ($request->filled('active')) {
                if ($request->boolean('active')) {
                    $query->active();
                } else {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('department_id')) {
                $query->forDepartment($request->department_id);
            }

            if ($request->filled('level')) {
                $query->byLevel($request->level);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $roles = $query->orderBy('level')
                ->orderBy('name')
                ->paginate($request->get('per_page', 20));

            return response()->json($roles);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des rôles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeRole(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:employee_roles,code',
                'description' => 'nullable|string',
                'level' => 'required|integer|between:1,10',
                'department_specific' => 'boolean',
                'department_id' => 'nullable|required_if:department_specific,true|exists:employee_departments,id',
                'permissions' => 'nullable|array',
                'restrictions' => 'nullable|array',
                'can_manage_employees' => 'boolean',
                'can_access_finances' => 'boolean',
                'can_manage_tenants' => 'boolean',
                'can_manage_partners' => 'boolean',
                'can_view_analytics' => 'boolean',
                'can_export_data' => 'boolean',
                'max_subordinates' => 'nullable|integer|min:0',
                'approval_limit' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $role = EmployeeRole::create($validatedData);

            return response()->json([
                'message' => 'Rôle créé avec succès',
                'role' => $role->load(['department'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création du rôle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function showRole(EmployeeRole $role)
    {
        try {
            $role->load(['department', 'employees', 'permissions']);
            
            return response()->json($role);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du rôle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateRole(Request $request, EmployeeRole $role)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'code' => [
                    'string', 
                    'max:50',
                    Rule::unique('employee_roles')->ignore($role->id)
                ],
                'description' => 'nullable|string',
                'level' => 'integer|between:1,10',
                'department_specific' => 'boolean',
                'department_id' => 'nullable|required_if:department_specific,true|exists:employee_departments,id',
                'permissions' => 'nullable|array',
                'restrictions' => 'nullable|array',
                'can_manage_employees' => 'boolean',
                'can_access_finances' => 'boolean',
                'can_manage_tenants' => 'boolean',
                'can_manage_partners' => 'boolean',
                'can_view_analytics' => 'boolean',
                'can_export_data' => 'boolean',
                'max_subordinates' => 'nullable|integer|min:0',
                'approval_limit' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $role->update($validatedData);

            return response()->json([
                'message' => 'Rôle mis à jour avec succès',
                'role' => $role->fresh()->load(['department'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du rôle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyRole(EmployeeRole $role)
    {
        try {
            // Vérifier si le rôle est utilisé par des employés
            if ($role->employees()->exists()) {
                return response()->json([
                    'error' => 'Impossible de supprimer un rôle assigné à des employés'
                ], 422);
            }

            $role->delete();

            return response()->json([
                'message' => 'Rôle supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression du rôle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // === GESTION DES PERMISSIONS ===

    public function getPermissions(Request $request)
    {
        try {
            $query = EmployeePermission::with(['roles']);

            if ($request->filled('active')) {
                if ($request->boolean('active')) {
                    $query->active();
                } else {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('module')) {
                $query->byModule($request->module);
            }

            if ($request->filled('dangerous')) {
                if ($request->boolean('dangerous')) {
                    $query->dangerous();
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $permissions = $query->orderBy('category')
                ->orderBy('module')
                ->orderBy('name')
                ->paginate($request->get('per_page', 50));

            return response()->json($permissions);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storePermission(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:employee_permissions,code',
                'description' => 'nullable|string',
                'category' => 'required|string|max:100',
                'module' => 'required|string|max:100',
                'resource' => 'nullable|string|max:100',
                'action' => 'required|string|max:50',
                'scope' => 'in:global,tenant,department,personal',
                'conditions' => 'nullable|array',
                'dependencies' => 'nullable|array',
                'is_system' => 'boolean',
                'is_dangerous' => 'boolean',
                'requires_2fa' => 'boolean',
                'requires_approval' => 'boolean',
                'approval_level' => 'nullable|integer|between:1,5',
                'audit_log' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $permission = EmployeePermission::create($validatedData);

            return response()->json([
                'message' => 'Permission créée avec succès',
                'permission' => $permission
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePermission(Request $request, EmployeePermission $permission)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'code' => [
                    'string', 
                    'max:100',
                    Rule::unique('employee_permissions')->ignore($permission->id)
                ],
                'description' => 'nullable|string',
                'category' => 'string|max:100',
                'module' => 'string|max:100',
                'resource' => 'nullable|string|max:100',
                'action' => 'string|max:50',
                'scope' => 'in:global,tenant,department,personal',
                'conditions' => 'nullable|array',
                'dependencies' => 'nullable|array',
                'is_system' => 'boolean',
                'is_dangerous' => 'boolean',
                'requires_2fa' => 'boolean',
                'requires_approval' => 'boolean',
                'approval_level' => 'nullable|integer|between:1,5',
                'audit_log' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $permission->update($validatedData);

            return response()->json([
                'message' => 'Permission mise à jour avec succès',
                'permission' => $permission->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // === ASSIGNATION DES PERMISSIONS AUX RÔLES ===

    public function assignPermissionToRole(Request $request, EmployeeRole $role)
    {
        try {
            $request->validate([
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:employee_permissions,id'
            ]);

            $role->permissions()->attach($request->permission_ids, [
                'granted_at' => now(),
                'granted_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Permissions assignées au rôle avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'assignation des permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function removePermissionFromRole(EmployeeRole $role, EmployeePermission $permission)
    {
        try {
            $role->permissions()->detach($permission->id);

            return response()->json([
                'message' => 'Permission retirée du rôle avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la permission',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // === MATRICE DES PERMISSIONS ===

    public function getPermissionMatrix()
    {
        try {
            $roles = EmployeeRole::active()->with('permissions')->get();
            $permissions = EmployeePermission::active()->orderBy('category')->orderBy('name')->get();

            $matrix = [];
            foreach ($permissions as $permission) {
                $permissionData = [
                    'permission' => $permission,
                    'roles' => []
                ];

                foreach ($roles as $role) {
                    $hasPermission = $role->permissions->contains('id', $permission->id);
                    $permissionData['roles'][] = [
                        'role_id' => $role->id,
                        'role_name' => $role->name,
                        'has_permission' => $hasPermission
                    ];
                }

                $matrix[] = $permissionData;
            }

            return response()->json([
                'roles' => $roles,
                'permissions' => $permissions,
                'matrix' => $matrix
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la matrice des permissions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserPermissions(Request $request, InternalEmployee $employee)
    {
        try {
            $employee->load(['role.permissions', 'department']);

            $rolePermissions = $employee->role->permissions ?? collect();
            $individualPermissions = $employee->permissions ?? [];

            $allPermissions = $rolePermissions->pluck('code')
                ->concat($individualPermissions)
                ->unique()
                ->values();

            return response()->json([
                'employee' => $employee,
                'role_permissions' => $rolePermissions,
                'individual_permissions' => $individualPermissions,
                'all_permissions' => $allPermissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des permissions de l\'employé',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategories()
    {
        try {
            $categories = EmployeePermission::active()
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values();

            return response()->json(['categories' => $categories]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des catégories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getModules()
    {
        try {
            $modules = EmployeePermission::active()
                ->distinct()
                ->pluck('module')
                ->sort()
                ->values();

            return response()->json(['modules' => $modules]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des modules',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}