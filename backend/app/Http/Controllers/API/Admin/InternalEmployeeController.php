<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InternalEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $employees = User::where('is_internal', true)
            ->select(['id', 'first_name', 'last_name', 'email', 'username', 'role_interne', 'avatar', 'is_active', 'created_at'])
            ->orderBy('first_name')
            ->paginate(10);
            
        return response()->json([
            'employees' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'last_page' => $employees->lastPage()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8'],
            'role_interne' => ['required', 'string'],
            'avatar' => ['nullable', 'string'],
        ]);

        $employee = User::create([
            ...$data,
            'is_internal' => true,
            'role' => 'admin', // ou autre selon logique
            'tenant_id' => null,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Employé ajouté', 'id' => $employee->id], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $employee = User::where('is_internal', true)->findOrFail($id);

        $data = $request->validate([
            'first_name' => ['sometimes', 'string'],
            'last_name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($employee->id)],
            'username' => ['sometimes', 'string', Rule::unique('users')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_interne' => ['sometimes', 'string'],
            'avatar' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $employee->update($data);
        return response()->json(['message' => 'Employé mis à jour']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $employee = User::where('is_internal', true)->findOrFail($id);
        $employee->delete();
        return response()->json(['message' => 'Employé supprimé']);
    }

    /**
     * Get employee statistics for dashboard
     */
    public function getStats(): JsonResponse
    {
        $totalEmployees = User::where('is_internal', true)->count();
        $activeEmployees = User::where('is_internal', true)->where('is_active', true)->count();
        $inactiveEmployees = $totalEmployees - $activeEmployees;

        // Employees by role
        $employeesByRole = User::where('is_internal', true)
            ->selectRaw('role_interne, COUNT(*) as count')
            ->groupBy('role_interne')
            ->pluck('count', 'role_interne')
            ->toArray();

        // Recent hires (last 30 days)
        $recentHires = User::where('is_internal', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Department distribution (mock data based on role)
        $departmentDistribution = [
            'Administration' => $employeesByRole['super_admin'] ?? 0 + $employeesByRole['admin'] ?? 0,
            'Support' => $employeesByRole['support'] ?? 0,
            'Partnerships' => $employeesByRole['partner_manager'] ?? 0,
            'Analytics' => $employeesByRole['analyst'] ?? 0,
            'Commercial' => $employeesByRole['commercial'] ?? 0
        ];

        return response()->json([
            'total' => $totalEmployees,
            'by_department' => $departmentDistribution,
            'by_role' => $employeesByRole,
            'by_status' => [
                'active' => $activeEmployees,
                'inactive' => $inactiveEmployees,
                'on_leave' => 0, // Mock data - no system for leave tracking yet
                'terminated' => 0 // Mock data - using soft deletes instead
            ],
            'by_work_location' => [
                'office' => (int)floor($activeEmployees * 0.4), // Mock: 40% office
                'remote' => (int)floor($activeEmployees * 0.3), // Mock: 30% remote  
                'hybrid' => (int)ceil($activeEmployees * 0.3)   // Mock: 30% hybrid
            ],
            'average_tenure_months' => 18, // Mock data
            'new_hires_this_month' => $recentHires,
            'departures_this_month' => 0 // Mock data
        ]);
    }
}
