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
            ->get();
        return response()->json($employees);
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
}
