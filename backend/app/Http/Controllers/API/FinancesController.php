<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\Invoice;
use App\Models\Repair;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancesController extends Controller
{
    /**
     * Get financial overview
     */
    public function getOverview(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        // Coût mensuel actuel
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthCost = $this->getMonthlyExpenses($user->id, $tenantId, $currentMonth);
        
        // Coût du mois précédent pour calculer l'évolution
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthCost = $this->getMonthlyExpenses($user->id, $tenantId, $previousMonth);
        
        // Évolution en pourcentage
        $evolution = $previousMonthCost > 0 
            ? round((($currentMonthCost - $previousMonthCost) / $previousMonthCost) * 100, 1)
            : 0;
        
        // Factures en attente
        $pendingInvoicesCount = Invoice::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('status', 'pending')
            ->count();
        
        // Coût moyen d'entretien
        $averageMaintenanceCost = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->avg('cost') ?? 0;
        
        // Coût cumulé total
        $totalCumulatedCost = $this->getTotalExpenses($user->id, $tenantId);
        
        // Nombre d'entretiens effectués
        $totalMaintenances = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('status', 'completed')
            ->count();
        
        // Total réparations
        $totalRepairsCost = Repair::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->sum('total_cost') ?? 0;
        
        // Moyenne mensuelle (sur les 12 derniers mois)
        $monthlyAverage = $this->getMonthlyAverage($user->id, $tenantId);
        
        // Coût le plus élevé
        $highestCost = $this->getHighestExpense($user->id, $tenantId);
        
        return response()->json([
            'monthly_metrics' => [
                'current_month_cost' => round($currentMonthCost, 2),
                'pending_invoices' => $pendingInvoicesCount,
                'average_maintenance_cost' => round($averageMaintenanceCost, 2),
                'evolution_percentage' => $evolution,
            ],
            'cumulated_overview' => [
                'total_cost' => round($totalCumulatedCost, 2),
                'total_maintenances' => $totalMaintenances,
                'total_repairs_cost' => round($totalRepairsCost, 2),
                'monthly_average' => round($monthlyAverage, 2),
                'highest_cost' => $highestCost,
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get monthly expenses chart data
     */
    public function getMonthlyExpenses(int $userId, int $tenantId, ?Carbon $specificMonth = null): float
    {
        $month = $specificMonth ?? Carbon::now()->startOfMonth();
        $nextMonth = $month->copy()->addMonth();
        
        // Maintenances cost
        $maintenancesCost = Maintenance::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->whereBetween('maintenance_date', [$month, $nextMonth])
            ->sum('cost') ?? 0;
        
        // Invoices cost
        $invoicesCost = Invoice::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->whereBetween('invoice_date', [$month, $nextMonth])
            ->sum('amount') ?? 0;
        
        // Repairs cost
        $repairsCost = Repair::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->whereBetween('created_at', [$month, $nextMonth])
            ->sum('total_cost') ?? 0;
        
        return $maintenancesCost + $invoicesCost + $repairsCost;
    }

    /**
     * Get 12 months expenses chart
     */
    public function getMonthlyChart(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $monthsData = [];
        $currentDate = Carbon::now();
        
        for ($i = 11; $i >= 0; $i--) {
            $month = $currentDate->copy()->subMonths($i)->startOfMonth();
            $cost = $this->getMonthlyExpenses($user->id, $tenantId, $month);
            
            $monthsData[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->translatedFormat('F Y'),
                'cost' => round($cost, 2),
            ];
        }
        
        return response()->json([
            'monthly_data' => $monthsData,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get expense breakdown by type
     */
    public function getExpenseBreakdown(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        // Entretien courant
        $maintenanceCost = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->sum('cost') ?? 0;
        
        // Réparations
        $repairCost = Repair::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->sum('total_cost') ?? 0;
        
        // Factures par type
        $invoicesByType = Invoice::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get()
            ->pluck('total', 'expense_type');
        
        $breakdown = [
            [
                'type' => 'Entretien courant',
                'amount' => round($maintenanceCost, 2),
                'color' => '#3B82F6', // blue
            ],
            [
                'type' => 'Réparation',
                'amount' => round($repairCost, 2),
                'color' => '#EF4444', // red
            ],
            [
                'type' => 'CT',
                'amount' => round($invoicesByType['controle_technique'] ?? 0, 2),
                'color' => '#F59E0B', // amber
            ],
            [
                'type' => 'Assurance',
                'amount' => round($invoicesByType['assurance'] ?? 0, 2),
                'color' => '#10B981', // green
            ],
            [
                'type' => 'Autres',
                'amount' => round($invoicesByType['other'] ?? 0, 2),
                'color' => '#8B5CF6', // purple
            ],
        ];
        
        // Calculer les pourcentages
        $total = array_sum(array_column($breakdown, 'amount'));
        
        foreach ($breakdown as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['amount'] / $total) * 100, 1) : 0;
        }
        
        return response()->json([
            'breakdown' => $breakdown,
            'total_amount' => round($total, 2),
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get top 3 most expensive vehicles
     */
    public function getTopExpensiveVehicles(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $vehicles = Vehicle::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->withSum(['maintenances as maintenance_cost'], 'cost')
            ->withSum(['repairs as repair_cost'], 'total_cost')
            ->withSum(['invoices as invoice_cost'], 'amount')
            ->withCount(['maintenances as interventions_count'])
            ->get()
            ->map(function ($vehicle) {
                $totalCost = ($vehicle->maintenance_cost ?? 0) + 
                           ($vehicle->repair_cost ?? 0) + 
                           ($vehicle->invoice_cost ?? 0);
                
                return [
                    'id' => $vehicle->id,
                    'marque' => $vehicle->marque,
                    'modele' => $vehicle->modele,
                    'immatriculation' => $vehicle->immatriculation,
                    'interventions_count' => $vehicle->interventions_count,
                    'total_cost' => round($totalCost, 2),
                ];
            })
            ->sortByDesc('total_cost')
            ->take(3)
            ->values();
        
        return response()->json([
            'top_vehicles' => $vehicles,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get maintenance statistics
     */
    public function getMaintenanceStats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $currentMonth = Carbon::now()->startOfMonth();
        $currentYear = Carbon::now()->startOfYear();
        
        // Entretiens du mois
        $monthlyMaintenances = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('maintenance_date', '>=', $currentMonth)
            ->count();
        
        // Entretiens de l'année
        $yearlyMaintenances = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('maintenance_date', '>=', $currentYear)
            ->count();
        
        // Moyenne par véhicule
        $totalVehicles = Vehicle::where('user_id', $user->id)
            ->where('tenant_id', $tenantId)
            ->count();
        
        $averagePerVehicle = $totalVehicles > 0 ? round($yearlyMaintenances / $totalVehicles, 1) : 0;
        
        // Plus cher du mois
        $mostExpensiveThisMonth = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('maintenance_date', '>=', $currentMonth)
            ->max('cost') ?? 0;
        
        return response()->json([
            'monthly_maintenances' => $monthlyMaintenances,
            'yearly_maintenances' => $yearlyMaintenances,
            'average_per_vehicle' => $averagePerVehicle,
            'most_expensive_this_month' => round($mostExpensiveThisMonth, 2),
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get expense history with pagination
     */
    public function getExpenseHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        
        $perPage = $request->per_page ?? 10;
        
        // Créer une collection unifiée des dépenses
        $expenses = collect();
        
        // Maintenances
        $maintenances = Maintenance::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->with('vehicle')
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => 'maintenance_' . $maintenance->id,
                    'vehicle' => $maintenance->vehicle->marque . ' ' . $maintenance->vehicle->modele,
                    'plate' => $maintenance->vehicle->immatriculation,
                    'date' => $maintenance->maintenance_date,
                    'type' => 'Entretien - ' . ($maintenance->maintenance_type ?? 'Général'),
                    'amount' => $maintenance->cost ?? 0,
                    'invoice_number' => null,
                ];
            });
        
        // Factures
        $invoices = Invoice::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->with('vehicle')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => 'invoice_' . $invoice->id,
                    'vehicle' => $invoice->vehicle->marque . ' ' . $invoice->vehicle->modele,
                    'plate' => $invoice->vehicle->immatriculation,
                    'date' => $invoice->invoice_date,
                    'type' => ucfirst($invoice->expense_type ?? 'Autre'),
                    'amount' => $invoice->amount ?? 0,
                    'invoice_number' => $invoice->invoice_number,
                ];
            });
        
        // Réparations
        $repairs = Repair::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->with('vehicle')
            ->get()
            ->map(function ($repair) {
                return [
                    'id' => 'repair_' . $repair->id,
                    'vehicle' => $repair->vehicle->marque . ' ' . $repair->vehicle->modele,
                    'plate' => $repair->vehicle->immatriculation,
                    'date' => $repair->created_at->toDateString(),
                    'type' => 'Réparation - ' . ($repair->description ?? 'Non spécifié'),
                    'amount' => $repair->total_cost ?? 0,
                    'invoice_number' => null,
                ];
            });
        
        $expenses = $expenses->concat($maintenances)
                           ->concat($invoices)
                           ->concat($repairs)
                           ->sortByDesc('date')
                           ->values();
        
        // Pagination manuelle
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $perPage;
        $paginatedExpenses = $expenses->slice($offset, $perPage)->values();
        
        return response()->json([
            'expenses' => $paginatedExpenses,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $expenses->count(),
                'last_page' => ceil($expenses->count() / $perPage),
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get financial alerts
     */
    public function getFinancialAlerts(Request $request): JsonResponse
    {
        $user = $request->user();

        // Récupérer le tenant depuis l'utilisateur ou le header
        $tenantId = $user->tenant_id ?? $request->header('X-Tenant-ID');
        if (!$tenantId) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }
        
        $alerts = [];
        
        // Coût élevé ce mois
        $currentMonthCost = $this->getMonthlyExpenses($user->id, $tenantId);
        $monthlyAverage = $this->getMonthlyAverage($user->id, $tenantId);
        
        if ($currentMonthCost > $monthlyAverage * 1.5) {
            $alerts[] = [
                'type' => 'high_cost',
                'severity' => 'warning',
                'message' => 'Coût mensuel élevé détecté',
                'details' => "Le coût de ce mois ({$currentMonthCost}€) dépasse la moyenne de 50%",
            ];
        }
        
        // Factures en attente
        $pendingInvoices = Invoice::whereHas('vehicle', function($query) use ($user, $tenantId) {
                $query->where('user_id', $user->id)->where('tenant_id', $tenantId);
            })
            ->where('status', 'pending')
            ->count();
        
        if ($pendingInvoices > 5) {
            $alerts[] = [
                'type' => 'pending_invoices',
                'severity' => 'info',
                'message' => 'Nombreuses factures en attente',
                'details' => "{$pendingInvoices} factures en attente de traitement",
            ];
        }
        
        return response()->json([
            'alerts' => $alerts,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Helper methods
     */
    private function getTotalExpenses(int $userId, int $tenantId): float
    {
        $maintenancesCost = Maintenance::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->sum('cost') ?? 0;
        
        $invoicesCost = Invoice::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->sum('amount') ?? 0;
        
        $repairsCost = Repair::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->sum('total_cost') ?? 0;
        
        return $maintenancesCost + $invoicesCost + $repairsCost;
    }
    
    private function getMonthlyAverage(int $userId, int $tenantId): float
    {
        $total = 0;
        $months = 0;
        
        for ($i = 0; $i < 12; $i++) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $cost = $this->getMonthlyExpenses($userId, $tenantId, $month);
            $total += $cost;
            $months++;
        }
        
        return $months > 0 ? $total / $months : 0;
    }
    
    private function getHighestExpense(int $userId, int $tenantId): array
    {
        $highestMaintenance = Maintenance::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->orderByDesc('cost')
            ->first();
        
        $highestInvoice = Invoice::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->orderByDesc('amount')
            ->first();
        
        $highestRepair = Repair::whereHas('vehicle', function($query) use ($userId, $tenantId) {
                $query->where('user_id', $userId)->where('tenant_id', $tenantId);
            })
            ->orderByDesc('total_cost')
            ->first();
        
        $candidates = array_filter([
            $highestMaintenance ? ['cost' => (float)$highestMaintenance->cost, 'type' => 'Entretien'] : null,
            $highestInvoice ? ['cost' => (float)$highestInvoice->amount, 'type' => 'Facture'] : null,
            $highestRepair ? ['cost' => (float)$highestRepair->total_cost, 'type' => 'Réparation'] : null,
        ]);
        
        if (empty($candidates)) {
            return ['cost' => 0, 'type' => 'Aucun'];
        }
        
        $highest = collect($candidates)->sortByDesc('cost')->first();
        
        return [
            'cost' => round($highest['cost'], 2),
            'type' => $highest['type'],
        ];
    }
}