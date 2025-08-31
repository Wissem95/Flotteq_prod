<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TenantUsersController extends Controller
{
    /**
     * Obtenir TOUS les utilisateurs de TOUS les tenants avec statistiques
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Base query - seulement les utilisateurs non-internes (clients des tenants)
            $query = User::with(['tenant'])
                ->where('is_internal', false) // Exclure les employés FlotteQ
                ->whereNotNull('tenant_id'); // S'assurer qu'ils appartiennent à un tenant

            // Filtre par tenant
            if ($request->has('tenant_id') && $request->tenant_id !== 'all') {
                $query->where('tenant_id', $request->tenant_id);
            }

            // Recherche par nom ou email
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtre par statut
            if ($request->has('status') && $request->status !== 'all') {
                switch($request->status) {
                    case 'active':
                        $query->where('is_active', true);
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                }
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Sécuriser les colonnes de tri
            $allowedSortColumns = ['created_at', 'first_name', 'last_name', 'email', 'is_active'];
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 20), 100); // Limiter à 100 max
            $users = $query->paginate($perPage);

            // Statistiques globales
            $stats = [
                'total_users' => User::where('is_internal', false)->whereNotNull('tenant_id')->count(),
                'active_users' => User::where('is_internal', false)->whereNotNull('tenant_id')->where('is_active', true)->count(),
                'inactive_users' => User::where('is_internal', false)->whereNotNull('tenant_id')->where('is_active', false)->count(),
                'users_by_tenant' => User::select('tenant_id', DB::raw('count(*) as count'))
                    ->where('is_internal', false)
                    ->whereNotNull('tenant_id')
                    ->groupBy('tenant_id')
                    ->with('tenant:id,name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'tenant_id' => $item->tenant_id,
                            'count' => $item->count,
                            'tenant' => $item->tenant ? ['id' => $item->tenant->id, 'name' => $item->tenant->name] : null
                        ];
                    }),
                'recent_registrations' => User::where('is_internal', false)
                    ->whereNotNull('tenant_id')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'recent_logins' => User::where('is_internal', false)
                    ->whereNotNull('tenant_id')
                    ->where('updated_at', '>=', now()->subDays(1)) // Utiliser updated_at comme proxy
                    ->count()
            ];

            // Liste des tenants pour les filtres
            $tenants = Tenant::select('id', 'name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'users' => $users,
                'stats' => $stats,
                'tenants' => $tenants
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching tenant users: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des utilisateurs'], 500);
        }
    }

    /**
     * Obtenir les détails d'un utilisateur spécifique
     */
    public function show(string $userId): JsonResponse
    {
        try {
            $user = User::with(['tenant'])
                ->where('is_internal', false)
                ->whereNotNull('tenant_id')
                ->findOrFail($userId);

            // Ajouter des statistiques utilisateur
            $user->stats = [
                'days_since_registration' => $user->created_at->diffInDays(now()),
                'account_status' => $this->getUserStatus($user)
            ];

            return response()->json($user);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
    }

    /**
     * Mettre à jour un utilisateur (admin platform)
     */
    public function update(Request $request, string $userId): JsonResponse
    {
        try {
            $user = User::where('is_internal', false)
                ->whereNotNull('tenant_id')
                ->findOrFail($userId);

            $validatedData = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($userId)],
                'is_active' => 'sometimes|boolean',
                'tenant_id' => 'sometimes|exists:tenants,id',
                'password' => 'sometimes|string|min:8'
            ]);

            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            $user->update($validatedData);

            // Log l'action admin
            \Log::info("Admin updated tenant user {$userId}", [
                'admin_id' => auth()->id(),
                'changes' => array_keys($validatedData)
            ]);

            return response()->json([
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => $user->load('tenant')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating tenant user: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la mise à jour'], 500);
        }
    }

    /**
     * Suspendre/Réactiver un utilisateur
     */
    public function toggleStatus(string $userId): JsonResponse
    {
        try {
            $user = User::where('is_internal', false)
                ->whereNotNull('tenant_id')
                ->findOrFail($userId);
                
            $user->is_active = !$user->is_active;
            $user->save();

            \Log::info("Admin toggled tenant user {$userId} status to: " . ($user->is_active ? 'active' : 'inactive'), [
                'admin_id' => auth()->id(),
                'tenant_id' => $user->tenant_id
            ]);

            return response()->json([
                'message' => $user->is_active ? 'Utilisateur réactivé' : 'Utilisateur suspendu',
                'user' => $user->load('tenant')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling tenant user status: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du changement de statut'], 500);
        }
    }

    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function destroy(string $userId): JsonResponse
    {
        try {
            $user = User::where('is_internal', false)
                ->whereNotNull('tenant_id')
                ->findOrFail($userId);
                
            $tenantName = $user->tenant->name ?? 'Unknown';
            
            // Vérifier s'il n'est pas le dernier admin du tenant (si applicable)
            $adminUsersCount = User::where('tenant_id', $user->tenant_id)
                ->where('role', 'admin')
                ->where('is_internal', false)
                ->count();

            if ($adminUsersCount === 1 && $user->role === 'admin') {
                return response()->json([
                    'error' => 'Impossible de supprimer le dernier administrateur du tenant'
                ], 403);
            }

            $user->delete();

            \Log::info("Admin deleted tenant user {$userId} from tenant {$tenantName}", [
                'admin_id' => auth()->id(),
                'tenant_id' => $user->tenant_id
            ]);

            return response()->json([
                'message' => 'Utilisateur supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting tenant user: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la suppression'], 500);
        }
    }

    /**
     * Exporter la liste des utilisateurs en CSV
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $query = User::with(['tenant'])
                ->where('is_internal', false)
                ->whereNotNull('tenant_id');

            // Appliquer les mêmes filtres que pour l'index
            if ($request->has('tenant_id') && $request->tenant_id !== 'all') {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            $exportData = $users->map(function($user) {
                return [
                    'ID' => $user->id,
                    'Prénom' => $user->first_name,
                    'Nom' => $user->last_name,
                    'Email' => $user->email,
                    'Tenant' => $user->tenant->name ?? 'N/A',
                    'Rôle' => $user->role ?? 'user',
                    'Statut' => $user->is_active ? 'Actif' : 'Inactif',
                    'Créé le' => $user->created_at->format('d/m/Y'),
                    'Dernière activité' => $user->updated_at->format('d/m/Y H:i')
                ];
            });

            return response()->json([
                'data' => $exportData,
                'filename' => 'tenant_users_export_' . now()->format('Y_m_d') . '.csv'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting tenant users: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'export'], 500);
        }
    }

    /**
     * Obtenir le statut d'un utilisateur
     */
    private function getUserStatus($user): string
    {
        if (!$user->is_active) {
            return 'suspended';
        }
        return 'active';
    }
}