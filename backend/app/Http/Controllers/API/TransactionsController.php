<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionsController extends Controller
{
    /**
     * Get transactions overview
     */
    public function getOverview(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        // Valeur totale de la flotte
        $totalFleetValue = Vehicle::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->sum('purchase_price') ?? 0;
        
        // Investissement total (prix d'achat + dépenses)
        $totalExpenses = $this->getTotalExpenses($user->id, $tenant->id);
        $totalInvestment = $totalFleetValue + $totalExpenses;
        
        // Valeur marché estimée (approximation basée sur l'âge)
        $estimatedMarketValue = $this->getEstimatedFleetValue($user->id, $tenant->id);
        
        // Profit potentiel
        $potentialProfit = $estimatedMarketValue - $totalInvestment;
        
        // Marge moyenne
        $totalVehicles = Vehicle::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->count();
        
        $averageMargin = $totalVehicles > 0 ? $potentialProfit / $totalVehicles : 0;
        
        // Métriques de transactions
        $totalPurchases = Transaction::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('type', 'purchase')
            ->count();
        
        $totalSales = Transaction::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('type', 'sale')
            ->count();
        
        $totalPurchaseAmount = Transaction::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('type', 'purchase')
            ->sum('price') ?? 0;
        
        $totalSaleAmount = Transaction::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('type', 'sale')
            ->sum('price') ?? 0;
        
        return response()->json([
            'fleet_metrics' => [
                'total_fleet_value' => round($totalFleetValue, 2),
                'total_investment' => round($totalInvestment, 2),
                'estimated_market_value' => round($estimatedMarketValue, 2),
                'potential_profit' => round($potentialProfit, 2),
                'average_margin' => round($averageMargin, 2),
            ],
            'transaction_metrics' => [
                'total_purchases' => $totalPurchases,
                'total_sales' => $totalSales,
                'total_purchase_amount' => round($totalPurchaseAmount, 2),
                'total_sale_amount' => round($totalSaleAmount, 2),
                'net_transaction_result' => round($totalSaleAmount - $totalPurchaseAmount, 2),
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get vehicle analysis for buy/sell decisions
     */
    public function getVehicleAnalysis(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        $vehicles = Vehicle::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->withSum(['maintenances as maintenance_cost'], 'cost')
            ->withSum(['repairs as repair_cost'], 'total_cost')
            ->withSum(['invoices as invoice_cost'], 'amount')
            ->get()
            ->map(function ($vehicle) {
                $totalExpenses = ($vehicle->maintenance_cost ?? 0) + 
                               ($vehicle->repair_cost ?? 0) + 
                               ($vehicle->invoice_cost ?? 0);
                
                $purchasePrice = $vehicle->purchase_price ?? 0;
                $totalInvestment = $purchasePrice + $totalExpenses;
                
                // Estimation de la valeur marché basée sur l'âge et les dépenses
                $currentYear = date('Y');
                $vehicleAge = $currentYear - ($vehicle->annee ?? $currentYear);
                
                // Dépréciation approximative: 15% la première année, puis 10% par an
                $depreciationRate = $vehicleAge == 0 ? 0 : ($vehicleAge == 1 ? 0.15 : 0.15 + ($vehicleAge - 1) * 0.10);
                $depreciationRate = min($depreciationRate, 0.80); // Maximum 80% de dépréciation
                
                $estimatedMarketValue = $purchasePrice * (1 - $depreciationRate);
                
                // Ajustement en fonction des dépenses (si beaucoup de réparations, valeur diminuée)
                if ($totalExpenses > $purchasePrice * 0.3) {
                    $estimatedMarketValue *= 0.9; // Réduction de 10% si dépenses > 30% du prix d'achat
                }
                
                $estimatedSalePrice = max($estimatedMarketValue, $purchasePrice * 0.2); // Minimum 20% du prix d'achat
                $profitLoss = $estimatedSalePrice - $totalInvestment;
                
                return [
                    'id' => $vehicle->id,
                    'vehicle' => $vehicle->marque . ' ' . $vehicle->modele,
                    'plate' => $vehicle->immatriculation,
                    'purchase_price' => round($purchasePrice, 2),
                    'total_expenses' => round($totalExpenses, 2),
                    'total_investment' => round($totalInvestment, 2),
                    'estimated_market_value' => round($estimatedMarketValue, 2),
                    'estimated_sale_price' => round($estimatedSalePrice, 2),
                    'profit_loss' => round($profitLoss, 2),
                    'profit_loss_percentage' => $totalInvestment > 0 ? round(($profitLoss / $totalInvestment) * 100, 1) : 0,
                    'age_years' => $vehicleAge,
                    'recommendation' => $this->getRecommendation($profitLoss, $vehicleAge, $totalExpenses, $purchasePrice),
                ];
            })
            ->sortByDesc('profit_loss')
            ->values();
        
        return response()->json([
            'vehicle_analysis' => $vehicles,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get transaction history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'type' => ['nullable', 'string', 'in:purchase,sale'],
        ]);
        
        $query = Transaction::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->with('vehicle');
        
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        $transactions = $query->orderByDesc('date')
            ->paginate($request->per_page ?? 10)
            ->through(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type,
                    'vehicle' => $transaction->vehicle->marque . ' ' . $transaction->vehicle->modele,
                    'plate' => $transaction->vehicle->immatriculation,
                    'date' => $transaction->date->format('Y-m-d'),
                    'price' => round($transaction->price, 2),
                    'mileage' => $transaction->mileage,
                    'seller_buyer_name' => $transaction->seller_buyer_name,
                    'seller_buyer_contact' => $transaction->seller_buyer_contact,
                    'reason' => $transaction->reason,
                    'status' => $transaction->status,
                    'notes' => $transaction->notes,
                    'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        return response()->json([
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Store a new transaction
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        $request->validate([
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'type' => ['required', 'string', 'in:purchase,sale'],
            'date' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'seller_buyer_name' => ['required', 'string', 'max:255'],
            'seller_buyer_contact' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:pending,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
        
        // Vérifier que le véhicule appartient à l'utilisateur
        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();
        
        $transaction = Transaction::create([
            'vehicle_id' => $request->vehicle_id,
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'type' => $request->type,
            'date' => $request->date,
            'price' => $request->price,
            'mileage' => $request->mileage,
            'seller_buyer_name' => $request->seller_buyer_name,
            'seller_buyer_contact' => $request->seller_buyer_contact,
            'reason' => $request->reason,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
        ]);
        
        return response()->json([
            'transaction' => $transaction,
            'message' => 'Transaction créée avec succès',
        ], 201);
    }

    /**
     * Update a transaction
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = $request->user();
        
        // Vérifier que la transaction appartient à l'utilisateur
        if ($transaction->user_id !== $user->id || $transaction->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Transaction non trouvée'], 404);
        }
        
        $request->validate([
            'type' => ['sometimes', 'string', 'in:purchase,sale'],
            'date' => ['sometimes', 'date'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'seller_buyer_name' => ['sometimes', 'string', 'max:255'],
            'seller_buyer_contact' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:pending,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
        
        $transaction->update($request->only([
            'type', 'date', 'price', 'mileage', 'seller_buyer_name',
            'seller_buyer_contact', 'reason', 'status', 'notes'
        ]));
        
        return response()->json([
            'transaction' => $transaction,
            'message' => 'Transaction mise à jour avec succès',
        ]);
    }

    /**
     * Delete a transaction
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $tenant = app('currentTenant');
        $user = request()->user();
        
        // Vérifier que la transaction appartient à l'utilisateur
        if ($transaction->user_id !== $user->id || $transaction->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Transaction non trouvée'], 404);
        }
        
        $transaction->delete();
        
        return response()->json([
            'message' => 'Transaction supprimée avec succès',
        ]);
    }

    /**
     * Helper methods
     */
    private function getTotalExpenses(int $userId, int $tenantId): float
    {
        // Récupérer le total des dépenses comme dans FinancesController
        $maintenancesCost = DB::table('maintenances')
            ->join('vehicles', 'maintenances.vehicle_id', '=', 'vehicles.id')
            ->where('vehicles.user_id', $userId)
            ->where('vehicles.tenant_id', $tenantId)
            ->sum('maintenances.cost') ?? 0;
        
        $invoicesCost = DB::table('invoices')
            ->join('vehicles', 'invoices.vehicle_id', '=', 'vehicles.id')
            ->where('vehicles.user_id', $userId)
            ->where('vehicles.tenant_id', $tenantId)
            ->sum('invoices.amount') ?? 0;
        
        $repairsCost = DB::table('repairs')
            ->join('vehicles', 'repairs.vehicle_id', '=', 'vehicles.id')
            ->where('vehicles.user_id', $userId)
            ->where('vehicles.tenant_id', $tenantId)
            ->sum('repairs.total_cost') ?? 0;
        
        return $maintenancesCost + $invoicesCost + $repairsCost;
    }
    
    private function getEstimatedFleetValue(int $userId, int $tenantId): float
    {
        $vehicles = Vehicle::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->get();
        
        $totalEstimatedValue = 0;
        $currentYear = date('Y');
        
        foreach ($vehicles as $vehicle) {
            $purchasePrice = $vehicle->purchase_price ?? 0;
            $vehicleAge = $currentYear - ($vehicle->annee ?? $currentYear);
            
            // Dépréciation standard
            $depreciationRate = $vehicleAge == 0 ? 0 : ($vehicleAge == 1 ? 0.15 : 0.15 + ($vehicleAge - 1) * 0.10);
            $depreciationRate = min($depreciationRate, 0.80);
            
            $estimatedValue = $purchasePrice * (1 - $depreciationRate);
            $totalEstimatedValue += max($estimatedValue, $purchasePrice * 0.2);
        }
        
        return $totalEstimatedValue;
    }
    
    private function getRecommendation(float $profitLoss, int $age, float $expenses, float $purchasePrice): array
    {
        if ($profitLoss > 0) {
            if ($age > 10) {
                return [
                    'action' => 'sell',
                    'priority' => 'high',
                    'reason' => 'Véhicule ancien avec profit potentiel'
                ];
            } else {
                return [
                    'action' => 'hold',
                    'priority' => 'medium',
                    'reason' => 'Véhicule profitable, peut encore prendre de la valeur'
                ];
            }
        } else {
            if ($expenses > $purchasePrice * 0.5) {
                return [
                    'action' => 'sell',
                    'priority' => 'high',
                    'reason' => 'Dépenses élevées, risque de perte supplémentaire'
                ];
            } else {
                return [
                    'action' => 'hold',
                    'priority' => 'low',
                    'reason' => 'Perte limitée, peut-être temporaire'
                ];
            }
        }
    }
}