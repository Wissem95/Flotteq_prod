<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InternalAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class InternalAuthController extends Controller
{
    /**
     * Login for Internal Admin users only
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Find internal admin by email
        $user = InternalAdmin::where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Ces identifiants ne correspondent à aucun compte administrateur.'],
            ]);
        }

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('internal-admin-token')->plainTextToken;

        // Update last login
        $user->update(['last_login' => now()]);

        Log::info('Internal admin login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return response()->json([
            'message' => 'Connexion administrative réussie',
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => ['*'], // Internal users have all permissions
                'is_internal' => true,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated internal user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user('internal');

        if (!$user || !($user instanceof InternalAdmin)) {
            throw ValidationException::withMessages([
                'auth' => ['Utilisateur non autorisé pour l\'interface d\'administration.'],
            ]);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => ['*'], // Internal users have all permissions
                'is_internal' => true,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Logout internal user
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user('internal')->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Déconnexion administrative réussie',
        ]);
    }

    /**
     * Update internal user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user('internal');

        if (!$user || !($user instanceof InternalAdmin)) {
            throw ValidationException::withMessages([
                'auth' => ['Utilisateur non autorisé.'],
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:internal_admins,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => ['*'],
                'is_internal' => true,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    /**
     * Check database connection health
     */
    public function healthDatabase(): JsonResponse
    {
        try {
            // Simple database check
            $userCount = InternalAdmin::count();
            
            return response()->json([
                'status' => 'ok',
                'database' => 'connected',
                'internal_users_count' => $userCount,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'database' => 'disconnected',
                'error' => $e->getMessage(),
                'timestamp' => now(),
            ], 500);
        }
    }
}