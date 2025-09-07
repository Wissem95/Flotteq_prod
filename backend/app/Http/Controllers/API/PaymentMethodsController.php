<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = PaymentMethod::query();

            // Filtres
            if ($request->filled('active')) {
                if ($request->boolean('active')) {
                    $query->active();
                } else {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            if ($request->filled('provider')) {
                $query->byProvider($request->provider);
            }

            if ($request->filled('supports_recurring')) {
                $query->supportsRecurring();
            }

            if ($request->filled('supports_refund')) {
                $query->supportsRefund();
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('provider', 'like', "%{$search}%");
                });
            }

            $paymentMethods = $query->orderBy('position')
                ->orderBy('name')
                ->paginate($request->get('per_page', 20));

            return response()->json($paymentMethods);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des méthodes de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:payment_methods,code',
                'type' => 'required|in:card,bank_transfer,wallet,crypto,other',
                'provider' => 'required|string|max:100',
                'gateway' => 'required|string|max:100',
                'gateway_config' => 'nullable|array',
                'api_keys' => 'nullable|array',
                'webhook_url' => 'nullable|url',
                'webhook_secret' => 'nullable|string',
                'supported_currencies' => 'nullable|array',
                'supported_countries' => 'nullable|array',
                'min_amount' => 'nullable|numeric|min:0',
                'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
                'transaction_fee_fixed' => 'nullable|numeric|min:0',
                'transaction_fee_percentage' => 'nullable|numeric|between:0,100',
                'settlement_delay_days' => 'nullable|integer|min:0',
                'instant_payment' => 'boolean',
                'recurring_payment' => 'boolean',
                'refund_supported' => 'boolean',
                'partial_refund_supported' => 'boolean',
                'auto_capture' => 'boolean',
                'requires_3ds' => 'boolean',
                'requires_verification' => 'boolean',
                'verification_fields' => 'nullable|array',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'icon_url' => 'nullable|url',
                'position' => 'nullable|integer|min:0',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'is_test_mode' => 'boolean',
                'test_credentials' => 'nullable|array',
                'sandbox_url' => 'nullable|url',
                'production_url' => 'nullable|url',
                'success_url' => 'nullable|url',
                'cancel_url' => 'nullable|url',
                'notification_url' => 'nullable|url',
                'available_for_tenants' => 'nullable|array',
                'available_for_plans' => 'nullable|array',
                'excluded_countries' => 'nullable|array',
                'risk_level' => 'in:low,medium,high',
                'fraud_detection' => 'boolean',
                'compliance_requirements' => 'nullable|array',
                'documentation_url' => 'nullable|url',
                'support_email' => 'nullable|email',
                'integration_status' => 'in:pending,in_progress,completed,failed',
            ]);

            // Si c'est défini comme méthode par défaut, désactiver les autres
            if ($validatedData['is_default'] ?? false) {
                PaymentMethod::where('is_default', true)->update(['is_default' => false]);
            }

            $paymentMethod = PaymentMethod::create($validatedData);

            return response()->json([
                'message' => 'Méthode de paiement créée avec succès',
                'payment_method' => $paymentMethod
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la méthode de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PaymentMethod $paymentMethod)
    {
        try {
            return response()->json($paymentMethod);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la méthode de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'code' => [
                    'string', 
                    'max:50',
                    Rule::unique('payment_methods')->ignore($paymentMethod->id)
                ],
                'type' => 'in:card,bank_transfer,wallet,crypto,other',
                'provider' => 'string|max:100',
                'gateway' => 'string|max:100',
                'gateway_config' => 'nullable|array',
                'api_keys' => 'nullable|array',
                'webhook_url' => 'nullable|url',
                'webhook_secret' => 'nullable|string',
                'supported_currencies' => 'nullable|array',
                'supported_countries' => 'nullable|array',
                'min_amount' => 'nullable|numeric|min:0',
                'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
                'transaction_fee_fixed' => 'nullable|numeric|min:0',
                'transaction_fee_percentage' => 'nullable|numeric|between:0,100',
                'settlement_delay_days' => 'nullable|integer|min:0',
                'instant_payment' => 'boolean',
                'recurring_payment' => 'boolean',
                'refund_supported' => 'boolean',
                'partial_refund_supported' => 'boolean',
                'auto_capture' => 'boolean',
                'requires_3ds' => 'boolean',
                'requires_verification' => 'boolean',
                'verification_fields' => 'nullable|array',
                'display_name' => 'string|max:255',
                'description' => 'nullable|string',
                'icon_url' => 'nullable|url',
                'position' => 'nullable|integer|min:0',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
                'is_test_mode' => 'boolean',
                'test_credentials' => 'nullable|array',
                'sandbox_url' => 'nullable|url',
                'production_url' => 'nullable|url',
                'success_url' => 'nullable|url',
                'cancel_url' => 'nullable|url',
                'notification_url' => 'nullable|url',
                'available_for_tenants' => 'nullable|array',
                'available_for_plans' => 'nullable|array',
                'excluded_countries' => 'nullable|array',
                'risk_level' => 'in:low,medium,high',
                'fraud_detection' => 'boolean',
                'compliance_requirements' => 'nullable|array',
                'documentation_url' => 'nullable|url',
                'support_email' => 'nullable|email',
                'integration_status' => 'in:pending,in_progress,completed,failed',
            ]);

            // Si c'est défini comme méthode par défaut, désactiver les autres
            if (($validatedData['is_default'] ?? false) && !$paymentMethod->is_default) {
                PaymentMethod::where('is_default', true)->update(['is_default' => false]);
            }

            $paymentMethod->update($validatedData);

            return response()->json([
                'message' => 'Méthode de paiement mise à jour avec succès',
                'payment_method' => $paymentMethod->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la méthode de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        try {
            // Vérifier si la méthode a des transactions en cours
            $hasActiveTransactions = $paymentMethod->transactions()
                ->whereIn('status', ['pending', 'processing'])
                ->exists();

            if ($hasActiveTransactions) {
                return response()->json([
                    'error' => 'Impossible de supprimer une méthode de paiement avec des transactions en cours'
                ], 422);
            }

            // Si c'était la méthode par défaut, définir une autre comme défaut
            if ($paymentMethod->is_default) {
                $newDefault = PaymentMethod::active()
                    ->where('id', '!=', $paymentMethod->id)
                    ->first();
                
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            $paymentMethod->delete();

            return response()->json([
                'message' => 'Méthode de paiement supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la méthode de paiement',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function testConnection(PaymentMethod $paymentMethod)
    {
        try {
            $success = $paymentMethod->testConnection();
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Connexion réussie' : 'Échec de la connexion',
                'last_tested_at' => $paymentMethod->fresh()->last_tested_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du test de connexion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        try {
            $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);
            
            return response()->json([
                'message' => $paymentMethod->is_active ? 'Méthode activée' : 'Méthode désactivée',
                'payment_method' => $paymentMethod
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du changement de statut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function setDefault(PaymentMethod $paymentMethod)
    {
        try {
            // Désactiver toutes les autres méthodes par défaut
            PaymentMethod::where('is_default', true)->update(['is_default' => false]);
            
            // Définir cette méthode comme défaut
            $paymentMethod->update(['is_default' => true]);
            
            return response()->json([
                'message' => 'Méthode définie comme défaut avec succès',
                'payment_method' => $paymentMethod
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la définition par défaut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $totalMethods = PaymentMethod::count();
            $activeMethods = PaymentMethod::active()->count();
            
            // Statistiques d'usage des méthodes
            $methodUsage = PaymentMethod::withCount([
                'transactions' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            ])
            ->with(['transactions' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                      ->selectRaw('payment_method_id, SUM(amount) as total_amount')
                      ->groupBy('payment_method_id');
            }])
            ->get()
            ->map(function($method) {
                return [
                    'name' => $method->display_name,
                    'code' => $method->code,
                    'transactions_count' => $method->transactions_count,
                    'total_amount' => $method->transactions->sum('total_amount') ?? 0
                ];
            });

            return response()->json([
                'total_methods' => $totalMethods,
                'active_methods' => $activeMethods,
                'method_usage' => $methodUsage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function available(Request $request)
    {
        try {
            $tenantId = $request->get('tenant_id');
            $planId = $request->get('plan_id');
            $amount = $request->get('amount');
            $currency = $request->get('currency', 'EUR');
            $country = $request->get('country');

            $methods = PaymentMethod::getAvailableMethods($tenantId, $planId);

            // Filtrer par montant et devise si spécifiés
            if ($amount) {
                $methods = $methods->filter(function($method) use ($amount, $currency) {
                    return $method->isAvailableForAmount($amount, $currency);
                });
            }

            // Filtrer par pays si spécifié
            if ($country) {
                $methods = $methods->filter(function($method) use ($country) {
                    return $method->isAvailableForCountry($country);
                });
            }

            return response()->json([
                'methods' => $methods->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des méthodes disponibles',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}