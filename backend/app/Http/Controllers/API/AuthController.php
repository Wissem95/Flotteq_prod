<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Services\UserPermissionService;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{


    /**
     * Register a new admin user with their tenant.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'min:3', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
            'domain' => [
                'nullable',
                'string',
                'unique:tenants,domain',
                'regex:/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/',
                'min:3',
                'max:63'
            ],
        ]);

        // Create tenant first - generate domain if not provided
        $domain = $validated['domain'] ?? strtolower(str_replace(' ', '', $validated['company_name'])) . '.flotteq.local';
        $tenant = Tenant::create([
            'name' => $validated['company_name'],
            'domain' => $domain,
            'database' => 'flotteq_' . str_replace(['.', '-'], '_', $domain),
            'is_active' => true,
        ]);

        // Create admin user
        $user = User::create([
            'email' => $validated['email'],
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        // Assigner les permissions par défaut au nouvel utilisateur
        try {
            UserPermissionService::assignDefaultPermissions($user);
        } catch (\Exception $e) {
            // Log l'erreur mais continue - ne pas bloquer l'enregistrement
            Log::warning("Impossible d'assigner les permissions lors de l'enregistrement pour l'utilisateur {$user->id}: " . $e->getMessage());
        }

        // Make tenant current
        $tenant->makeCurrent();

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Préparer les données utilisateur avec le statut du profil
        $userProfileData = $user->only(['id', 'email', 'username', 'first_name', 'last_name', 'role']);
        // Temporairement désactivé pour diagnostic erreurs 500
        try {
            $userProfileData['profile_incomplete'] = $user->hasIncompleteProfile();
            $userProfileData['missing_fields'] = $user->getMissingProfileFields();
        } catch (\Exception $e) {
            // Valeurs par défaut si erreur
            Log::warning("Erreur lors de la vérification du profil pour l'utilisateur {$user->id}: " . $e->getMessage());
            $userProfileData['profile_incomplete'] = true;
            $userProfileData['missing_fields'] = [];
        }

        return response()->json([
            'message' => 'Registration successful',
            'user' => $userProfileData,
            'tenant' => $tenant->only(['id', 'name', 'domain']),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(Request $request): JsonResponse
    {
        // Clean the input data
        $login = trim($request->input('login', ''));
        $password = trim($request->input('password', ''));
        // $domain = trim($request->input('domain'));

        // First, validate the domain and get the tenant
        // $tenant = Tenant::where('domain', $domain)
        //     ->where('is_active', true)
        //     ->first();

        // if (!$tenant) {
        //     throw ValidationException::withMessages([
        //         'domain' => ['Invalid or inactive tenant domain.'],
        //     ]);
        // }

        // Make tenant current
        // $tenant->makeCurrent();

        // Now validate user credentials
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Find user by email or username
        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => ['User not found.'],
            ]);
        }

        // Verify the user belongs to the correct tenant
        // if ($user->tenant_id !== $tenant->id) {
        //     throw ValidationException::withMessages([
        //         'login' => ['User does not belong to this tenant.'],
        //     ]);
        // }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials.'],
            ]);
        }

        // Assigner les permissions par défaut si l'utilisateur n'en a pas
        // Temporairement désactivé pour éviter les erreurs 500 si les permissions n'existent pas
        try {
            if ($user->permissions()->count() === 0) {
                UserPermissionService::assignDefaultPermissions($user);
            }
        } catch (\Exception $e) {
            // Log l'erreur mais continue - ne pas bloquer la connexion
            Log::warning("Impossible d'assigner les permissions lors du login pour l'utilisateur {$user->id}: " . $e->getMessage());
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Préparer les données utilisateur avec le statut du profil
        $userProfileData = $user->only(['id', 'email', 'username', 'first_name', 'last_name', 'role']);
        // Temporairement désactivé pour diagnostic erreurs 500
        try {
            $userProfileData['profile_incomplete'] = $user->hasIncompleteProfile();
            $userProfileData['missing_fields'] = $user->getMissingProfileFields();
        } catch (\Exception $e) {
            // Valeurs par défaut si erreur
            Log::warning("Erreur lors de la vérification du profil pour l'utilisateur {$user->id}: " . $e->getMessage());
            $userProfileData['profile_incomplete'] = true;
            $userProfileData['missing_fields'] = [];
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $userProfileData,
            // 'tenant' => $tenant->only(['id', 'name', 'domain']),
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => ['User not authenticated.'],
            ]);
        }

        // Get the tenant from the user's tenant_id
        $tenant = Tenant::where('id', $user->tenant_id)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            throw ValidationException::withMessages([
                'tenant' => ['Tenant not found or inactive.'],
            ]);
        }

        // Make tenant current to avoid middleware issues
        $tenant->makeCurrent();

        // Préparer les données utilisateur avec le statut du profil
        $userProfileData = $user->only([
            'id', 'email', 'username', 'first_name', 'last_name', 'role',
            'phone', 'birthdate', 'gender', 'address', 'postalCode', 'city', 'country',
            'company', 'fleet_role', 'license_number'
        ]);
        $userProfileData['profile_incomplete'] = $user->hasIncompleteProfile();
        $userProfileData['missing_fields'] = $user->getMissingProfileFields();

        return response()->json([
            'user' => $userProfileData,
            'tenant' => $tenant->only(['id', 'name', 'domain']),
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            throw ValidationException::withMessages([
                'auth' => ['User not authenticated.'],
            ]);
        }

        Log::info('Profile update request', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);

        // Validation des champs
        $validated = $request->validate([
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'fleet_role' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ]);

        // Sauvegarder les données avant modification pour comparaison
        $oldData = $user->only(array_keys($validated));
        Log::info('User data before update', ['old_data' => $oldData]);

        // Mettre à jour les champs
        $user->fill($validated);
        $updated = $user->save();

        Log::info('Profile update result', [
            'user_id' => $user->id,
            'updated' => $updated,
            'new_data' => $user->only(array_keys($validated))
        ]);

        // Rafraîchir les données utilisateur depuis la base
        $user->refresh();

        // Préparer les données utilisateur avec le statut du profil
        $userProfileData = $user->only([
            'id', 'email', 'username', 'first_name', 'last_name', 'role',
            'phone', 'birthdate', 'gender', 'address', 'postalCode', 'city', 'country',
            'company', 'fleet_role', 'license_number'
        ]);
        
        // Temporairement désactivé pour diagnostic erreurs 500
        try {
            $userProfileData['profile_incomplete'] = $user->hasIncompleteProfile();
            $userProfileData['missing_fields'] = $user->getMissingProfileFields();
        } catch (\Exception $e) {
            // Valeurs par défaut si erreur
            Log::warning("Erreur lors de la vérification du profil pour l'utilisateur {$user->id}: " . $e->getMessage());
            $userProfileData['profile_incomplete'] = true;
            $userProfileData['missing_fields'] = [];
        }

        return response()->json([
            'user' => $userProfileData,
            'message' => 'Profil mis à jour avec succès',
        ]);
    }
}
