<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants (Internal only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tenant::with(['users', 'vehicles'])
                ->withCount(['users', 'vehicles']);

            // Search functionality
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $tenants = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tenants->items(),
                'pagination' => [
                    'current_page' => $tenants->currentPage(),
                    'per_page' => $tenants->perPage(),
                    'total' => $tenants->total(),
                    'last_page' => $tenants->lastPage(),
                ],
                'message' => 'Tenants récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tenants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('status', 'active')->count(),
                'inactive_tenants' => Tenant::where('status', 'inactive')->count(),
                'pending_tenants' => Tenant::where('status', 'pending')->count(),
                'total_users' => User::where('is_internal', false)->count(),
                'total_vehicles' => Vehicle::count(),
                'recent_tenants' => Tenant::orderBy('created_at', 'desc')->limit(5)->get(),
                'monthly_growth' => $this->getMonthlyGrowth(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques des tenants récupérées'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific tenant
     */
    public function show(Tenant $tenant): JsonResponse
    {
        try {
            $tenant->load(['users', 'vehicles.maintenances', 'partnerRelations.partner']);

            $tenantData = $tenant->toArray();
            $tenantData['statistics'] = [
                'users_count' => $tenant->users->count(),
                'vehicles_count' => $tenant->vehicles->count(),
                'maintenance_count' => $tenant->vehicles->sum(fn($vehicle) => $vehicle->maintenances->count()),
                'partners_count' => $tenant->partnerRelations->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $tenantData,
                'message' => 'Tenant récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new tenant
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:tenants,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'required|in:active,inactive,pending',
            ]);

            $tenant = Tenant::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $tenant,
                'message' => 'Tenant créé avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a tenant
     */
    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:tenants,email,' . $tenant->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'sometimes|in:active,inactive,pending',
            ]);

            $tenant->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $tenant,
                'message' => 'Tenant mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a tenant
     */
    public function destroy(Tenant $tenant): JsonResponse
    {
        try {
            // Check if tenant has users or vehicles
            if ($tenant->users()->count() > 0 || $tenant->vehicles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un tenant avec des utilisateurs ou véhicules'
                ], 400);
            }

            $tenant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tenant supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly growth statistics
     */
    private function getMonthlyGrowth(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Tenant::whereYear('created_at', $date->year)
                           ->whereMonth('created_at', $date->month)
                           ->count();
            
            $months[] = [
                'month' => $date->format('Y-m'),
                'count' => $count
            ];
        }
        
        return $months;
    }
}