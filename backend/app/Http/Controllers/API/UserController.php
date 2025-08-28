<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of users for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }

        // Récupérer les utilisateurs du même tenant
        $users = User::where('tenant_id', $tenantId)
            ->select(['id', 'first_name', 'last_name', 'email', 'role', 'is_active', 'created_at'])
            ->when($request->search, function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('first_name', 'ILIKE', "%{$request->search}%")
                      ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                      ->orWhere('email', 'ILIKE', "%{$request->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                }),
            ],
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:user,admin,manager',
        ]);

        $newUser = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
            'tenant_id' => $tenantId,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => [
                'id' => $newUser->id,
                'first_name' => $newUser->first_name,
                'last_name' => $newUser->last_name,
                'email' => $newUser->email,
                'role' => $newUser->role,
                'is_active' => $newUser->is_active,
                'created_at' => $newUser->created_at,
            ]
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        
        // Vérifier que l'utilisateur appartient au même tenant
        if ($user->tenant_id !== $currentUser->tenant_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        
        // Vérifier que l'utilisateur appartient au même tenant
        if ($user->tenant_id !== $currentUser->tenant_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)->where(function ($query) use ($user) {
                    return $query->where('tenant_id', $user->tenant_id);
                }),
            ],
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:user,admin,manager',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = $request->only(['first_name', 'last_name', 'email', 'role', 'is_active']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Utilisateur modifié avec succès',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        
        // Vérifier que l'utilisateur appartient au même tenant
        if ($user->tenant_id !== $currentUser->tenant_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Empêcher la suppression de soi-même
        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}