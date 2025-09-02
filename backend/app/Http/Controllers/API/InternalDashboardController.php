<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InternalDashboardController extends Controller
{
    /**
     * Helper pour déterminer le scope et récupérer le tenant
     */
    private function getScope(Request $request)
    {
        $tenantId = $request->query('tenant_id');
        
        if ($tenantId && $tenantId !== 'all') {
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                abort(404, 'Tenant non trouvé');
            }
            return [
                'scope' => 'tenant',
                'tenant_id' => $tenantId,
                'tenant' => $tenant,
                'tenant_info' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domain' => $tenant->domain ?? null
                ]
            ];
        }
        
        return [
            'scope' => 'global',
            'tenant_id' => null,
            'tenant' => null,
            'tenant_info' => null
        ];
    }

    /**
     * Get dashboard statistics for internal admin panel (global or tenant-specific)
     */
    public function getGlobalStats(Request $request): JsonResponse
    {
        $scopeData = $this->getScope($request);
        $tenantId = $scopeData['tenant_id'];
        
        // Base queries
        $tenantQuery = Tenant::query();
        $userQuery = User::query();
        $vehicleQuery = Vehicle::query();
        $maintenanceQuery = Maintenance::query();
        
        // Apply tenant filter if specific tenant selected
        if ($tenantId) {
            $userQuery->where('tenant_id', $tenantId);
            $vehicleQuery->where('tenant_id', $tenantId);
            $maintenanceQuery->whereHas('vehicle', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        
        // Calculate statistics
        $totalTenants = $tenantId ? 1 : $tenantQuery->count();
        $activeTenants = $tenantId 
            ? ($scopeData['tenant']->is_active ? 1 : 0)
            : $tenantQuery->where('is_active', true)->count();
        
        $totalVehicles = $vehicleQuery->count();
        $activeVehicles = clone $vehicleQuery;
        $activeVehicles = $activeVehicles->where('status', 'active')->count();
        
        $totalUsers = $userQuery->count();
        $activeUsers = clone $userQuery;
        $activeUsers = $activeUsers->where('is_active', true)->count();
        
        $pendingMaintenances = clone $maintenanceQuery;
        $pendingMaintenances = $pendingMaintenances->where('status', 'scheduled')->count();
        
        // Technical controls upcoming (next 30 days)
        $upcomingTechnicalControls = clone $vehicleQuery;
        $upcomingTechnicalControls = $upcomingTechnicalControls->where('next_ct_date', '<=', Carbon::now()->addDays(30))
            ->where('next_ct_date', '>=', Carbon::now())
            ->count();
        
        // Critical alerts (vehicles with expired CT or insurance)
        $criticalAlerts = clone $vehicleQuery;
        $criticalAlerts = $criticalAlerts->where(function($query) {
            $query->where('next_ct_date', '<', Carbon::now())
                  ->orWhere('insurance_expiry_date', '<', Carbon::now());
        })->count();
        
        // Total alerts (include warnings - CT/insurance expiring in 30 days)
        $totalAlerts = clone $vehicleQuery;
        $totalAlerts = $totalAlerts->where(function($query) {
            $query->where('next_ct_date', '<=', Carbon::now()->addDays(30))
                  ->orWhere('insurance_expiry_date', '<=', Carbon::now()->addDays(30));
        })->count();

        return response()->json([
            'scope' => $scopeData['scope'],
            'tenant_info' => $scopeData['tenant_info'],
            'stats' => [
                'tenants' => [
                    'total' => $totalTenants,
                    'active' => $activeTenants
                ],
                'vehicles' => [
                    'total' => $totalVehicles,
                    'active' => $activeVehicles,
                    'in_maintenance' => $totalVehicles - $activeVehicles
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active' => $activeUsers,
                    'inactive' => $totalUsers - $activeUsers
                ],
                'maintenances' => [
                    'pending' => $pendingMaintenances,
                    'upcoming_30_days' => $upcomingTechnicalControls
                ],
                'alerts' => [
                    'critical' => $criticalAlerts,
                    'total' => $totalAlerts
                ]
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get upcoming maintenances (global or tenant-specific)
     */
    public function getUpcomingMaintenances(Request $request): JsonResponse
    {
        $scopeData = $this->getScope($request);
        $tenantId = $scopeData['tenant_id'];
        $limit = $request->query('limit', 5);
        
        $query = Maintenance::with(['vehicle.tenant']);
        
        // Apply tenant filter if specific tenant selected
        if ($tenantId) {
            $query->whereHas('vehicle', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }
        
        $maintenances = $query
            ->where('status', 'scheduled')
            ->where('maintenance_date', '>=', Carbon::now())
            ->orderBy('maintenance_date')
            ->limit($limit)
            ->get()
            ->map(function($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'vehicle_name' => ($maintenance->vehicle->marque ?? '') . ' ' . ($maintenance->vehicle->modele ?? ''),
                    'license_plate' => $maintenance->vehicle->immatriculation ?? '',
                    'maintenance_type' => $maintenance->maintenance_type ?? 'Entretien général',
                    'scheduled_date' => $maintenance->maintenance_date,
                    'tenant_name' => $maintenance->vehicle->tenant->name ?? 'Tenant inconnu',
                    'status' => $maintenance->status,
                ];
            });

        return response()->json([
            'scope' => $scopeData['scope'],
            'tenant_info' => $scopeData['tenant_info'],
            'data' => $maintenances,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get system alerts (global or tenant-specific)
     */
    public function getSystemAlerts(Request $request): JsonResponse
    {
        $scopeData = $this->getScope($request);
        $tenantId = $scopeData['tenant_id'];
        $limit = $request->query('limit', 5);
        $alerts = collect();

        // Base query for vehicles with tenant filter if specified
        $vehicleQuery = Vehicle::with('tenant');
        if ($tenantId) {
            $vehicleQuery->where('tenant_id', $tenantId);
        }

        // Critical: Expired CT
        $expiredCT = clone $vehicleQuery;
        $expiredCT = $expiredCT->where('next_ct_date', '<', Carbon::now())
            ->limit($limit)
            ->get();

        foreach ($expiredCT as $vehicle) {
            $alerts->push([
                'id' => 'ct_expired_' . $vehicle->id,
                'title' => 'Contrôle technique expiré',
                'description' => "Véhicule {$vehicle->immatriculation} - CT expiré",
                'severity' => 'critical',
                'category' => 'compliance',
                'created_at' => $vehicle->next_ct_date,
                'tenant_name' => $vehicle->tenant->name ?? 'Tenant inconnu',
            ]);
        }

        // Critical: Expired insurance
        $expiredInsurance = clone $vehicleQuery;
        $expiredInsurance = $expiredInsurance->whereNotNull('insurance_expiry_date')
            ->where('insurance_expiry_date', '<', Carbon::now())
            ->limit($limit)
            ->get();

        foreach ($expiredInsurance as $vehicle) {
            $alerts->push([
                'id' => 'insurance_expired_' . $vehicle->id,
                'title' => 'Assurance expirée',
                'description' => "Véhicule {$vehicle->immatriculation} - Assurance expirée le " . Carbon::parse($vehicle->insurance_expiry_date)->format('d/m/Y'),
                'severity' => 'critical',
                'category' => 'compliance',
                'created_at' => $vehicle->insurance_expiry_date,
                'tenant_name' => $vehicle->tenant->name ?? 'Tenant inconnu',
            ]);
        }

        // Warning: CT expiring soon
        $ctExpiringSoon = clone $vehicleQuery;
        $ctExpiringSoon = $ctExpiringSoon->where('next_ct_date', '>', Carbon::now())
            ->where('next_ct_date', '<=', Carbon::now()->addDays(30))
            ->limit($limit)
            ->get();

        foreach ($ctExpiringSoon as $vehicle) {
            $alerts->push([
                'id' => 'ct_expiring_' . $vehicle->id,
                'title' => 'Contrôle technique bientôt expiré',
                'description' => "Véhicule {$vehicle->immatriculation} - CT expire le " . Carbon::parse($vehicle->next_ct_date)->format('d/m/Y'),
                'severity' => 'medium',
                'category' => 'compliance',
                'created_at' => $vehicle->next_ct_date,
                'tenant_name' => $vehicle->tenant->name ?? 'Tenant inconnu',
            ]);
        }

        // Warning: Insurance expiring soon
        $insuranceExpiringSoon = clone $vehicleQuery;
        $insuranceExpiringSoon = $insuranceExpiringSoon->whereNotNull('insurance_expiry_date')
            ->where('insurance_expiry_date', '>', Carbon::now())
            ->where('insurance_expiry_date', '<=', Carbon::now()->addDays(30))
            ->limit($limit)
            ->get();

        foreach ($insuranceExpiringSoon as $vehicle) {
            $alerts->push([
                'id' => 'insurance_expiring_' . $vehicle->id,
                'title' => 'Assurance bientôt expirée',
                'description' => "Véhicule {$vehicle->immatriculation} - Assurance expire le " . Carbon::parse($vehicle->insurance_expiry_date)->format('d/m/Y'),
                'severity' => 'medium',
                'category' => 'compliance',
                'created_at' => $vehicle->insurance_expiry_date,
                'tenant_name' => $vehicle->tenant->name ?? 'Tenant inconnu',
            ]);
        }

        // Sort by severity and date
        $sortedAlerts = $alerts->sortBy([
            ['severity', 'desc'],
            ['created_at', 'asc']
        ])->take($limit)->values();

        return response()->json([
            'scope' => $scopeData['scope'],
            'tenant_info' => $scopeData['tenant_info'],
            'data' => $sortedAlerts,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get global revenue statistics
     */
    public function getGlobalRevenue(Request $request): JsonResponse
    {
        // This would need to be implemented based on your subscription/billing system
        // For now, return calculated estimates based on active tenants
        
        $activeTenants = Tenant::where('is_active', true)->count();
        
        // Assuming different plan prices - adjust based on your actual plans
        $basicPlanPrice = 29.99;
        $proPlanPrice = 59.99;
        $enterprisePlanPrice = 99.99;
        
        // Simple estimation - in reality you'd query your subscriptions table
        $estimatedMonthlyRevenue = $activeTenants * $basicPlanPrice; // Conservative estimate
        
        $monthlyGrowth = 0; // Calculate from historical data
        
        // Get monthly trend (last 12 months) - simplified
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $tenantsAtMonth = Tenant::where('created_at', '<=', $month->endOfMonth())->count();
            
            $monthlyTrends[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->translatedFormat('F Y'),
                'revenue' => $tenantsAtMonth * $basicPlanPrice,
                'tenants' => $tenantsAtMonth,
            ];
        }

        return response()->json([
            'monthly_revenue' => round($estimatedMonthlyRevenue, 2),
            'annual_revenue' => round($estimatedMonthlyRevenue * 12, 2),
            'growth_percentage' => $monthlyGrowth,
            'monthly_trends' => $monthlyTrends,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get partner distribution statistics
     */
    public function getPartnerDistribution(Request $request): JsonResponse
    {
        // This would need to be implemented based on your partners table
        // For now, return empty data structure
        
        return response()->json([
            'partners_distribution' => [
                // ['name' => 'Garages', 'value' => 0, 'color' => '#3b82f6'],
                // ['name' => 'Centres CT', 'value' => 0, 'color' => '#10b981'],
                // ['name' => 'Assurances', 'value' => 0, 'color' => '#f59e0b'],
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get system health metrics
     */
    public function getSystemHealth(Request $request): JsonResponse
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            $dbStatus = 'healthy';
            
            // Simple response time test
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;
            
            // Calculate uptime percentage (simplified - you'd want real monitoring)
            $uptime = 99.9; // Default good value
            
            return response()->json([
                'database_status' => $dbStatus,
                'response_time_ms' => round($responseTime, 2),
                'uptime_percentage' => $uptime,
                'status' => 'healthy',
                'generated_at' => now()->toISOString(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'database_status' => 'error',
                'response_time_ms' => 0,
                'uptime_percentage' => 0,
                'status' => 'critical',
                'error' => $e->getMessage(),
                'generated_at' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get tenants list for scope selector
     */
    public function getTenantsList(Request $request): JsonResponse
    {
        $tenants = Tenant::withCount(['users', 'vehicles'])
            ->orderBy('name')
            ->get()
            ->map(function($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'domain' => $tenant->domain,
                    'is_active' => $tenant->is_active ?? true,
                    'created_at' => $tenant->created_at,
                    'users_count' => $tenant->users_count ?? 0,
                    'vehicles_count' => $tenant->vehicles_count ?? 0,
                    'status_label' => ($tenant->is_active ?? true) ? 'Actif' : 'Inactif',
                    'display_label' => sprintf(
                        "%s (%d véhicules, %d utilisateurs)",
                        $tenant->name,
                        $tenant->vehicles_count ?? 0,
                        $tenant->users_count ?? 0
                    )
                ];
            });
        
        return response()->json([
            'tenants' => $tenants,
            'summary' => [
                'total' => $tenants->count(),
                'active' => $tenants->where('is_active', true)->count(),
                'inactive' => $tenants->where('is_active', false)->count()
            ]
        ]);
    }
}