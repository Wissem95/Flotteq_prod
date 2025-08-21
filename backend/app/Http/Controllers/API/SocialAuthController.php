<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Services\UserPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use GuzzleHttp\Client as HttpClient;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

/**
 * Social authentication controller for handling Google OAuth
 *
 * @method \Laravel\Socialite\Two\GoogleProvider stateless()
 */
class SocialAuthController extends Controller
{
    /**
     * Handle Firebase Auth (nouvelle méthode qui réutilise toute la logique existante)
     */
    public function handleFirebaseAuth(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'user_data' => 'sometimes|array',
            'tenant_domain' => 'sometimes|string'
        ]);

        try {
            // 1. Pour l'instant, on fait confiance au frontend (on améliorera plus tard)
            // En production, il faudrait vérifier le token Firebase côté serveur
            $userData = $request->user_data;
            
            if (!$userData || !isset($userData['email'])) {
                throw new \Exception('User data is required');
            }
            
            $uid = $userData['google_id'] ?? uniqid('firebase_');
            $email = $userData['email'];
            $name = $userData['name'] ?? '';
            $avatar = $userData['avatar'] ?? null;

            Log::info('Firebase Auth: Token verified successfully', [
                'uid' => $uid,
                'email' => $email,
                'name' => $name
            ]);

            // 2. Utiliser la même logique que l'OAuth existant
            $tenant = $this->getTenantForAuth($request->tenant_domain);
            $tenant->makeCurrent();

            // 3. Préparer les données utilisateur avec toutes les données disponibles
            $googleUserData = [
                'id' => $uid,
                'email' => $email,
                'name' => $name,
                'avatar' => $avatar,
                'first_name' => $this->extractFirstName($name),
                'last_name' => $this->extractLastName($name),
                // Nouvelles données étendues
                'phone' => $userData['phone'] ?? null,
                'birthdate' => $userData['birthday'] ?? null,
                'gender' => $userData['gender'] ?? null,
                'address' => $userData['address'] ?? null,
                'locale' => $userData['locale'] ?? null,
                'phone_verified' => $userData['phone_verified'] ?? false,
                'email_verified' => $userData['email_verified'] ?? false,
            ];

            // 4. RÉUTILISER EXACTEMENT la même logique de création/mise à jour
            $user = $this->findOrCreateUser($googleUserData, $tenant);

            // 5. Créer le token (même logique)
            $token = $user->createToken('firebase-auth')->plainTextToken;

            // 6. MÊME format de retour que l'OAuth existant
            return response()->json([
                'message' => 'Authentication successful',
                'user' => $user->only(['id', 'email', 'username', 'first_name', 'last_name', 'avatar']),
                'token' => $token,
                'tenant' => $tenant->only(['id', 'name', 'domain']),
            ]);

        } catch (\Exception $e) {
            Log::error('Firebase Auth: Authentication failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'error' => 'Authentication failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Méthode helper pour réutiliser la logique de tenant
     */
    private function getTenantForAuth(?string $tenantDomain): Tenant
    {
        if ($tenantDomain) {
            return Tenant::where('domain', $tenantDomain)->firstOrFail();
        }
        
        $tenant = Tenant::first();
        if (!$tenant) {
            throw new \Exception('No tenant available');
        }
        
        return $tenant;
    }

    /**
     * Méthode helper pour réutiliser la logique de création/mise à jour utilisateur
     */
    private function findOrCreateUser(array $googleUserData, Tenant $tenant): User
    {
        // Chercher l'utilisateur existant (MÊME LOGIQUE)
        $user = User::where('email', $googleUserData['email'])
            ->where('tenant_id', $tenant->id)
            ->first();

        if (!$user) {
            // Vérifier si l'utilisateur existe dans un autre tenant
            $existingUser = User::where('email', $googleUserData['email'])->first();
            if ($existingUser) {
                throw new \Exception('Cet email est déjà associé à un autre domaine.');
            }

            // Créer nouvel utilisateur avec toutes les données disponibles
            $user = User::create([
                'email' => $googleUserData['email'],
                'username' => $this->generateUsername($googleUserData['name']),
                'first_name' => $googleUserData['first_name'],
                'last_name' => $googleUserData['last_name'],
                'password' => Hash::make(Str::random(32)),
                'google_id' => $googleUserData['id'],
                'avatar' => $googleUserData['avatar'],
                'email_verified_at' => now(),
                'tenant_id' => $tenant->id,
                // Nouvelles données étendues
                'phone' => $googleUserData['phone'],
                'birthdate' => $googleUserData['birthdate'],
                'gender' => $googleUserData['gender'],
                'address' => $googleUserData['address'],
                'locale' => $googleUserData['locale'],
                'phone_verified' => $googleUserData['phone_verified'],
                'email_verified_status' => $googleUserData['email_verified'],
            ]);

            // Assigner permissions (MÊME LOGIQUE)
            UserPermissionService::assignDefaultPermissions($user);
        } else {
            // Mettre à jour utilisateur existant avec nouvelles données si vides
            $updateData = [
                'google_id' => $googleUserData['id'],
                'avatar' => $googleUserData['avatar'],
                'first_name' => $googleUserData['first_name'],
                'last_name' => $googleUserData['last_name'],
            ];
            
            // Ajouter les nouvelles données seulement si elles ne sont pas déjà renseignées
            if (empty($user->phone) && !empty($googleUserData['phone'])) {
                $updateData['phone'] = $googleUserData['phone'];
            }
            if (empty($user->birthdate) && !empty($googleUserData['birthdate'])) {
                $updateData['birthdate'] = $googleUserData['birthdate'];
            }
            if (empty($user->gender) && !empty($googleUserData['gender'])) {
                $updateData['gender'] = $googleUserData['gender'];
            }
            if (empty($user->address) && !empty($googleUserData['address'])) {
                $updateData['address'] = $googleUserData['address'];
            }
            if (empty($user->locale) && !empty($googleUserData['locale'])) {
                $updateData['locale'] = $googleUserData['locale'];
            }
            if (!$user->phone_verified && $googleUserData['phone_verified']) {
                $updateData['phone_verified'] = $googleUserData['phone_verified'];
            }
            if (!$user->email_verified_status && $googleUserData['email_verified']) {
                $updateData['email_verified_status'] = $googleUserData['email_verified'];
            }
            
            $user->update($updateData);
        }

        return $user;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_domain' => ['nullable', 'string', 'exists:tenants,domain']
        ]);

        // Use provided tenant or default to first available tenant
        if ($request->tenant_domain) {
        $tenant = Tenant::where('domain', $request->tenant_domain)->firstOrFail();
        } else {
            $tenant = Tenant::first();
            if (!$tenant) {
                abort(500, 'No tenant available');
            }
        }

        // Store tenant info in state parameter
        $state = base64_encode(json_encode([
            'tenant_id' => $tenant->id,
            'tenant_domain' => $tenant->domain,
            'csrf_token' => Str::random(40)
        ]));

        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver('google');
        
        // CORRECTION: S'assurer que le redirect_uri est bien configuré
        $redirectUri = config('services.google.redirect');
        if (!$redirectUri) {
            abort(500, 'Google OAuth redirect URI not configured');
        }
        
        return $provider->stateless()
            ->redirectUrl($redirectUri)
            ->scopes(['openid', 'profile', 'email'])
            ->with(['state' => $state])
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request): JsonResponse|RedirectResponse
    {
        // Debug: Log all request data
        Log::info('Google OAuth Callback Debug', [
            'all_params' => $request->all(),
            'state' => $request->get('state'),
            'code' => $request->get('code'),
        ]);

        try {
            // Decode state to get tenant info
            $state = $request->get('state');
            if (!$state) {
                Log::error('Google OAuth: No state parameter');
                return response()->json(['error' => 'Invalid state parameter'], 400);
            }

            $stateData = json_decode(base64_decode($state), true);
            if (!$stateData || !isset($stateData['tenant_id'])) {
                Log::error('Google OAuth: Invalid state data', ['state' => $state, 'decoded' => $stateData]);
                return response()->json(['error' => 'Invalid state data'], 400);
            }

            Log::info('Google OAuth: State decoded successfully', ['tenant_id' => $stateData['tenant_id']]);

            $tenant = Tenant::findOrFail($stateData['tenant_id']);
            $tenant->makeCurrent();

            Log::info('Google OAuth: Tenant set', ['tenant_id' => $tenant->id]);

            // Get user from Google
            /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
            $provider = Socialite::driver('google');
            $googleUser = $provider->stateless()
                ->redirectUrl(config('services.google.redirect'))
                ->user();

            // Extraire plus d'informations utilisateur depuis Google
            $googleUserData = $this->extractGoogleUserData($googleUser);

            Log::info('Google OAuth: Google user retrieved', [
                'email' => $googleUserData['email'],
                'name' => $googleUserData['name'],
                'id' => $googleUserData['id'],
                'extracted_data' => $googleUserData
            ]);

            // Find or create user in current tenant
            $user = User::where('email', $googleUserData['email'])
                ->where('tenant_id', $tenant->id)
                ->first();

            // If user doesn't exist in current tenant, check if they exist in another tenant
            if (!$user) {
                $existingUser = User::where('email', $googleUserData['email'])->first();
                if ($existingUser) {
                    Log::warning('Google OAuth: User exists in different tenant', [
                        'email' => $googleUserData['email'],
                        'existing_tenant' => $existingUser->tenant_id,
                        'requested_tenant' => $tenant->id
                    ]);
                    return response()->json([
                        'error' => 'Cet email est déjà associé à un autre domaine. Veuillez utiliser le bon domaine ou contacter l\'administrateur.'
                    ], 409);
                }
            }

            if (!$user) {
                Log::info('Google OAuth: Creating new user');
                try {
                    $user = User::create([
                        'email' => $googleUserData['email'],
                        'username' => $this->generateUsername($googleUserData['name']),
                        'first_name' => $googleUserData['first_name'],
                        'last_name' => $googleUserData['last_name'],
                        'password' => Hash::make(Str::random(32)), // Random password
                        'google_id' => $googleUserData['id'],
                        'avatar' => $googleUserData['avatar'],
                        'email_verified_at' => now(),
                        'tenant_id' => $tenant->id,
                        // Ajouter les données supplémentaires si disponibles
                        'birthdate' => $googleUserData['birthdate'] ?? null,
                        'gender' => $googleUserData['gender'] ?? null,
                        'city' => $googleUserData['city'] ?? null,
                        'country' => $googleUserData['country'] ?? null,
                    ]);
                    Log::info('Google OAuth: User created successfully', ['user_id' => $user->id]);

                    // Assigner les permissions par défaut
                    UserPermissionService::assignDefaultPermissions($user);
                    Log::info('Google OAuth: Permissions assigned successfully');
                } catch (\Exception $e) {
                    Log::error('Google OAuth: Failed to create user', ['error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                Log::info('Google OAuth: Updating existing user', ['user_id' => $user->id]);
                try {
                    // Log des données avant mise à jour
                    Log::info('Google OAuth: User data before update', [
                        'current_user' => $user->only(['first_name', 'last_name', 'birthdate', 'gender', 'city', 'country']),
                        'google_data' => $googleUserData
                    ]);

                    // Toujours mettre à jour ces champs essentiels
                    $updateData = [
                        'google_id' => $googleUserData['id'],
                        'avatar' => $googleUserData['avatar'],
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ];

                    // Forcer la mise à jour du prénom et nom depuis Google (données plus fraîches)
                    if (!empty($googleUserData['first_name'])) {
                        $updateData['first_name'] = $googleUserData['first_name'];
                        Log::info('Google OAuth: Updating first_name', ['from' => $user->first_name, 'to' => $googleUserData['first_name']]);
                    }

                    if (!empty($googleUserData['last_name'])) {
                        $updateData['last_name'] = $googleUserData['last_name'];
                        Log::info('Google OAuth: Updating last_name', ['from' => $user->last_name, 'to' => $googleUserData['last_name']]);
                    }

                    // Ajouter les données supplémentaires seulement si elles ne sont pas déjà renseignées
                    if (empty($user->birthdate) && !empty($googleUserData['birthdate'])) {
                        $updateData['birthdate'] = $googleUserData['birthdate'];
                        Log::info('Google OAuth: Adding birthdate', ['value' => $googleUserData['birthdate']]);
                    }
                    if (empty($user->gender) && !empty($googleUserData['gender'])) {
                        $updateData['gender'] = $googleUserData['gender'];
                        Log::info('Google OAuth: Adding gender', ['value' => $googleUserData['gender']]);
                    }
                    if (empty($user->city) && !empty($googleUserData['city'])) {
                        $updateData['city'] = $googleUserData['city'];
                        Log::info('Google OAuth: Adding city', ['value' => $googleUserData['city']]);
                    }
                    if (empty($user->country) && !empty($googleUserData['country'])) {
                        $updateData['country'] = $googleUserData['country'];
                        Log::info('Google OAuth: Adding country', ['value' => $googleUserData['country']]);
                    }

                    Log::info('Google OAuth: Final update data', ['update_data' => $updateData]);

                    // Forcer la mise à jour avec différentes méthodes
                    try {
                        // Méthode 1: Update direct
                        $updated = $user->update($updateData);
                        Log::info('Google OAuth: Update method 1 result', ['updated' => $updated]);

                        // Méthode 2: Fill et save si update ne fonctionne pas
                        if (!$updated) {
                            $user->fill($updateData);
                            $saved = $user->save();
                            Log::info('Google OAuth: Fill and save result', ['saved' => $saved]);
                        }

                        // Méthode 3: Force touch pour updated_at
                        $user->touch();
                        Log::info('Google OAuth: Forced touch executed');

                    } catch (\Exception $updateError) {
                        Log::error('Google OAuth: Update failed', ['error' => $updateError->getMessage()]);

                        // Méthode alternative: mise à jour directe en base
                        try {
                            $result = DB::table('users')
                                ->where('id', $user->id)
                                ->update(array_merge($updateData, ['updated_at' => now()]));
                            Log::info('Google OAuth: Direct DB update result', ['result' => $result]);
                        } catch (\Exception $dbError) {
                            Log::error('Google OAuth: Direct DB update failed', ['error' => $dbError->getMessage()]);
                        }
                    }

                    Log::info('Google OAuth: User updated successfully', ['updated' => $updated ?? false]);

                    // Vérifier les données après mise à jour
                    $user->refresh();
                    Log::info('Google OAuth: User data after update', [
                        'updated_user' => $user->only(['first_name', 'last_name', 'birthdate', 'gender', 'city', 'country', 'updated_at'])
                    ]);
                } catch (\Exception $e) {
                    Log::error('Google OAuth: Failed to update user', ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            // Create authentication token
            Log::info('Google OAuth: Creating token for user', ['user_id' => $user->id]);
            $token = $user->createToken('google-auth')->plainTextToken;
            Log::info('Google OAuth: Token created successfully');

            // Redirection vers le frontend

            // Construire l'URL du frontend
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

            // Préparer les données utilisateur avec le statut du profil
            $userProfileData = $user->only(['id', 'email', 'username', 'first_name', 'last_name', 'avatar']);
            $userProfileData['profile_incomplete'] = $user->hasIncompleteProfile();
            $userProfileData['missing_fields'] = $user->getMissingProfileFields();

            // Encoder les données pour les passer en URL
            $userData = base64_encode(json_encode([
                'message' => 'Authentication successful',
                'user' => $userProfileData,
                'token' => $token,
                'tenant' => $tenant->only(['id', 'name', 'domain']),
            ]));

            // Redirection vers le frontend avec les données
            return redirect()->to("{$frontendUrl}/google-callback?data={$userData}");
        } catch (InvalidStateException $e) {
            Log::error('Google OAuth: Invalid state exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid OAuth state'], 400);
        } catch (\Exception $e) {
            Log::error('Google OAuth: General exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Link Google account to existing user
     */
    public function linkGoogleAccount(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = app('currentTenant');

        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
            $provider = Socialite::driver('google');
            $googleUser = $provider->stateless()
                ->redirectUrl(config('services.google.redirect'))
                ->user();

            // Check if Google account is already linked to another user
            $existingUser = User::where('google_id', $googleUser->getId())
                ->where('tenant_id', $tenant->id)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'error' => 'This Google account is already linked to another user'
                ], 409);
            }

            // Link Google account
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            return response()->json([
                'message' => 'Google account linked successfully',
                'user' => $user->only(['id', 'email', 'username', 'first_name', 'last_name', 'avatar', 'google_id'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to link Google account'], 500);
        }
    }

    /**
     * Unlink Google account
     */
    public function unlinkGoogleAccount(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update([
            'google_id' => null,
        ]);

        return response()->json([
            'message' => 'Google account unlinked successfully'
        ]);
    }

    /**
     * Generate unique username from name
     */
    private function generateUsername(string $name): string
    {
        $tenant = app('currentTenant');
        $baseUsername = Str::slug(Str::lower($name), '');
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->where('tenant_id', $tenant->id)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Extract first name from full name
     */
    private function extractFirstName(string $fullName): string
    {
        return trim(explode(' ', $fullName)[0] ?? '');
    }

    /**
     * Extract last name from full name
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', $fullName);
        if (count($parts) > 1) {
            array_shift($parts); // Remove first name
            return trim(implode(' ', $parts));
        }
        return '';
    }

    /**
     * Extract additional user data from Google user object.
     */
    private function extractGoogleUserData($googleUser): array
    {
        $userData = [
            'id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'avatar' => $googleUser->getAvatar(),
            'first_name' => $this->extractFirstName($googleUser->getName()),
            'last_name' => $this->extractLastName($googleUser->getName()),
        ];

        // Essayer de récupérer plus d'informations via Google People API
        try {
            $accessToken = $googleUser->token;
            Log::info('Google OAuth: Access token available', ['token_length' => strlen($accessToken)]);

            // Appel à Google People API pour récupérer les données complètes
            $peopleData = $this->fetchGooglePeopleData($accessToken);

            if ($peopleData) {
                Log::info('Google OAuth: People API data retrieved', ['people_data' => $peopleData]);

                // Intégrer les données People API
                if (isset($peopleData['names'][0])) {
                    $userData['first_name'] = $peopleData['names'][0]['givenName'] ?? $userData['first_name'];
                    $userData['last_name'] = $peopleData['names'][0]['familyName'] ?? $userData['last_name'];
                }

                if (isset($peopleData['birthdays'][0]['date'])) {
                    $birthday = $peopleData['birthdays'][0]['date'];
                    if (isset($birthday['year'], $birthday['month'], $birthday['day'])) {
                        $userData['birthdate'] = sprintf('%04d-%02d-%02d',
                            $birthday['year'], $birthday['month'], $birthday['day']);
                    }
                }

                if (isset($peopleData['genders'][0]['value'])) {
                    $gender = strtolower($peopleData['genders'][0]['value']);
                    $userData['gender'] = ($gender === 'male') ? 'male' : (($gender === 'female') ? 'female' : 'other');
                }

                if (isset($peopleData['phoneNumbers'][0]['value'])) {
                    $userData['phone'] = $peopleData['phoneNumbers'][0]['value'];
                }

                if (isset($peopleData['addresses'][0])) {
                    $address = $peopleData['addresses'][0];
                    if (isset($address['formattedValue'])) {
                        $userData['address'] = $address['formattedValue'];
                    }
                    if (isset($address['city'])) {
                        $userData['city'] = $address['city'];
                    }
                    if (isset($address['countryCode'])) {
                        $userData['country'] = $address['countryCode'];
                    }
                    if (isset($address['postalCode'])) {
                        $userData['postalCode'] = $address['postalCode'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Google OAuth: Failed to fetch People API data', ['error' => $e->getMessage()]);
        }

        // Essayer d'extraire plus d'informations si disponibles depuis l'objet de base
        $rawUser = $googleUser->user ?? [];

        // Informations supplémentaires de Google (fallback)
        if (isset($rawUser['given_name']) && empty($userData['first_name'])) {
            $userData['first_name'] = $rawUser['given_name'];
        }
        if (isset($rawUser['family_name']) && empty($userData['last_name'])) {
            $userData['last_name'] = $rawUser['family_name'];
        }
        if (isset($rawUser['locale'])) {
            $locale = $rawUser['locale'];
            // Essayer d'extraire le pays depuis la locale (ex: en_US -> US)
            if (strpos($locale, '_') !== false && empty($userData['country'])) {
                $parts = explode('_', $locale);
                $userData['country'] = end($parts);
            }
        }

        return $userData;
    }

    /**
     * Fetch user data from Google People API
     */
    private function fetchGooglePeopleData(string $accessToken): ?array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->get('https://people.googleapis.com/v1/people/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'personFields' => 'names,birthdays,genders,phoneNumbers,addresses,emailAddresses'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }
        } catch (\Exception $e) {
            Log::error('Google People API call failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
