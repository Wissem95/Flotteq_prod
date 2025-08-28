<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get dashboard overview statistics
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        // Basic counts - filtrer par utilisateur
        $totalVehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->count();
        $totalUsers = 1; // L'utilisateur lui-même
        $activeVehicles = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // Recent activity (last 30 days)
        $recentPeriod = Carbon::now()->subDays(30);
        $newVehiclesThisMonth = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $recentPeriod)
            ->count();
        
        $newUsersThisMonth = 0; // L'utilisateur n'a pas créé d'autres utilisateurs

        // Vehicle status distribution
        $vehiclesByStatus = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Vehicle by fuel type
        $vehiclesByFuel = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->select('carburant', DB::raw('count(*) as count'))
            ->groupBy('carburant')
            ->get()
            ->pluck('count', 'carburant');

        // Average vehicle age
        $currentYear = date('Y');
        $averageAge = Vehicle::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->whereNotNull('annee')
            ->avg(DB::raw("$currentYear - annee"));

        return response()->json([
            'overview' => [
                'total_vehicles' => $totalVehicles,
                'total_users' => $totalUsers,
                'active_vehicles' => $activeVehicles,
                'inactive_vehicles' => $totalVehicles - $activeVehicles,
                'new_vehicles_this_month' => $newVehiclesThisMonth,
                'new_users_this_month' => $newUsersThisMonth,
                'average_vehicle_age' => round($averageAge ?? 0, 1),
            ],
            'distributions' => [
                'vehicles_by_status' => $vehiclesByStatus,
                'vehicles_by_fuel' => $vehiclesByFuel,
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get vehicle statistics with filtering
     */
    public function getVehicleStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $request->validate([
            'period' => ['nullable', 'string', 'in:7,30,90,365'],
            'group_by' => ['nullable', 'string', 'in:day,week,month,year'],
        ]);

        $period = (int) ($request->period ?? 30);
        $groupBy = $request->group_by ?? 'day';
        $startDate = Carbon::now()->subDays($period);

        // Vehicles created over time (database agnostic)
        $dateSelect = match($groupBy) {
            'day' => "DATE(created_at) as period",
            'week' => "strftime('%Y-%W', created_at) as period",
            'month' => "strftime('%Y-%m', created_at) as period", 
            'year' => "strftime('%Y', created_at) as period",
            default => "DATE(created_at) as period"
        };

        // For MySQL compatibility
        if (config('database.default') === 'mysql') {
            $dateSelect = match($groupBy) {
                'day' => "DATE(created_at) as period",
                'week' => "DATE_FORMAT(created_at, '%Y-%u') as period",
                'month' => "DATE_FORMAT(created_at, '%Y-%m') as period",
                'year' => "YEAR(created_at) as period",
                default => "DATE(created_at) as period"
            };
        }

        $vehicleCreationTrend = Vehicle::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw($dateSelect),
                DB::raw('count(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Top vehicle brands
        $topBrands = Vehicle::where('tenant_id', $tenantId)
            ->select('marque', DB::raw('count(*) as count'))
            ->groupBy('marque')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Vehicle age distribution
        $currentYear = date('Y');
        $ageDistribution = Vehicle::where('tenant_id', $tenantId)
            ->whereNotNull('annee')
            ->select(
                DB::raw("CASE 
                    WHEN $currentYear - annee <= 2 THEN '0-2 ans'
                    WHEN $currentYear - annee <= 5 THEN '3-5 ans'
                    WHEN $currentYear - annee <= 10 THEN '6-10 ans'
                    WHEN $currentYear - annee <= 15 THEN '11-15 ans'
                    ELSE '15+ ans'
                END as age_range"),
                DB::raw('count(*) as count')
            )
            ->groupBy('age_range')
            ->get();

        // Mileage statistics
        $mileageStats = Vehicle::where('tenant_id', $tenantId)
            ->whereNotNull('kilometrage')
            ->selectRaw('
                AVG(kilometrage) as average,
                MIN(kilometrage) as minimum,
                MAX(kilometrage) as maximum,
                COUNT(*) as total_with_mileage
            ')
            ->first();

        return response()->json([
            'trends' => [
                'vehicle_creation' => $vehicleCreationTrend,
            ],
            'rankings' => [
                'top_brands' => $topBrands,
            ],
            'distributions' => [
                'age_ranges' => $ageDistribution,
            ],
            'mileage_stats' => [
                'average' => round($mileageStats->average ?? 0),
                'minimum' => $mileageStats->minimum ?? 0,
                'maximum' => $mileageStats->maximum ?? 0,
                'total_vehicles_with_mileage' => $mileageStats->total_with_mileage ?? 0,
            ],
            'period_days' => $period,
            'group_by' => $groupBy,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get user activity statistics
     */
    public function getUserStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $request->validate([
            'period' => ['nullable', 'string', 'in:7,30,90,365'],
        ]);

        $period = (int) ($request->period ?? 30);
        $startDate = Carbon::now()->subDays($period);

        // User registration trend
        $userRegistrationTrend = User::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Active users (those who own vehicles)
        $activeUsers = User::where('tenant_id', $tenantId)
            ->whereHas('vehicles')
            ->count();

        // Users by role distribution
        $usersByRole = User::where('tenant_id', $tenantId)
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

        // Users with Google OAuth
        $usersWithGoogle = User::where('tenant_id', $tenantId)
            ->whereNotNull('google_id')
            ->count();

        // Vehicle ownership distribution
        $vehicleOwnership = User::where('tenant_id', $tenantId)
            ->withCount('vehicles')
            ->get()
            ->groupBy(function($user) {
                if ($user->vehicles_count == 0) return '0 véhicules';
                if ($user->vehicles_count == 1) return '1 véhicule';
                if ($user->vehicles_count <= 3) return '2-3 véhicules';
                if ($user->vehicles_count <= 5) return '4-5 véhicules';
                return '6+ véhicules';
            })
            ->map(function($group) {
                return $group->count();
            });

        return response()->json([
            'overview' => [
                'total_users' => User::where('tenant_id', $tenantId)->count(),
                'active_users' => $activeUsers,
                'users_with_google' => $usersWithGoogle,
                'google_adoption_rate' => round(($usersWithGoogle / max(1, User::where('tenant_id', $tenantId)->count())) * 100, 1),
            ],
            'trends' => [
                'user_registrations' => $userRegistrationTrend,
            ],
            'distributions' => [
                'users_by_role' => $usersByRole,
                'vehicle_ownership' => $vehicleOwnership,
            ],
            'period_days' => $period,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get system health metrics
     */
    public function getSystemHealth(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        // Database metrics
        $dbStats = [
            'total_vehicles' => Vehicle::where('tenant_id', $tenantId)->count(),
            'total_users' => User::where('tenant_id', $tenantId)->count(),
            'total_media_files' => DB::table('media')
                ->join('vehicles', 'media.model_id', '=', 'vehicles.id')
                ->where('media.model_type', Vehicle::class)
                ->where('vehicles.tenant_id', $tenantId)
                ->count(),
        ];

        // Storage usage (approximate)
        $storageUsage = DB::table('media')
            ->join('vehicles', 'media.model_id', '=', 'vehicles.id')
            ->where('media.model_type', Vehicle::class)
            ->where('vehicles.tenant_id', $tenantId)
            ->sum('media.size');

        // Recent activity
        $recentActivity = [
            'vehicles_created_today' => Vehicle::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->count(),
            'users_created_today' => User::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->count(),
        ];

        // Data quality metrics
        $dataQuality = [
            'vehicles_with_images' => Vehicle::where('tenant_id', $tenantId)
                ->whereHas('media', function($query) {
                    $query->where('collection_name', 'images');
                })
                ->count(),
            'vehicles_with_complete_info' => Vehicle::where('tenant_id', $tenantId)
                ->whereNotNull(['marque', 'modele', 'immatriculation', 'annee'])
                ->count(),
            'users_with_complete_profiles' => User::where('tenant_id', $tenantId)
                ->whereNotNull(['first_name', 'last_name'])
                ->count(),
        ];

        return response()->json([
            'database' => $dbStats,
            'storage' => [
                'total_size_bytes' => $storageUsage,
                'total_size_mb' => round($storageUsage / 1024 / 1024, 2),
                'files_count' => $dbStats['total_media_files'],
            ],
            'activity' => $recentActivity,
            'data_quality' => $dataQuality,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Export analytics data
     */
    public function exportAnalytics(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', 'in:dashboard,vehicles,users,system'],
            'format' => ['nullable', 'string', 'in:json,csv'],
        ]);

        $type = $request->type;
        $format = $request->format ?? 'json';

        $data = match($type) {
            'dashboard' => $this->getDashboardStats($request)->getData(),
            'vehicles' => $this->getVehicleStats($request)->getData(),
            'users' => $this->getUserStats($request)->getData(),
            'system' => $this->getSystemHealth($request)->getData(),
        };

        if ($format === 'csv') {
            // For CSV, we'll return a simplified flat structure
            $csvData = $this->flattenForCsv($data, $type);
            
            return response()->json([
                'export_data' => $csvData,
                'export_type' => $type,
                'export_format' => $format,
                'exported_at' => now()->toISOString(),
            ]);
        }

        return response()->json([
            'export_data' => $data,
            'export_type' => $type,
            'export_format' => $format,
            'exported_at' => now()->toISOString(),
        ]);
    }

    /**
     * Flatten data structure for CSV export
     */
    private function flattenForCsv($data, string $type): array
    {
        $result = [];
        
        switch ($type) {
            case 'dashboard':
                foreach ($data->overview as $key => $value) {
                    $result[] = ['metric' => $key, 'value' => $value];
                }
                break;
                
            case 'vehicles':
                if (isset($data->rankings->top_brands)) {
                    foreach ($data->rankings->top_brands as $brand) {
                        $result[] = [
                            'brand' => $brand->marque,
                            'count' => $brand->count
                        ];
                    }
                }
                break;
                
            case 'users':
                foreach ($data->overview as $key => $value) {
                    $result[] = ['metric' => $key, 'value' => $value];
                }
                break;
                
            case 'system':
                foreach ($data->database as $key => $value) {
                    $result[] = ['metric' => "db_$key", 'value' => $value];
                }
                break;
        }
        
        return $result;
    }
}
