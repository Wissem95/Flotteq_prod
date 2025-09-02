<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromotionsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Promotion::with(['createdBy', 'approvedBy'])
                ->orderBy('created_at', 'desc');

            // Filtres
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('featured')) {
                $query->where('is_featured', $request->boolean('featured'));
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $promotions = $query->paginate($request->get('per_page', 15));

            return response()->json($promotions);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des promotions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:promotions,code',
                'description' => 'nullable|string',
                'type' => 'required|in:discount,trial,bonus',
                'discount_type' => 'required_if:type,discount|in:fixed,percentage',
                'discount_value' => 'required_if:discount_type,fixed|nullable|numeric|min:0',
                'discount_percentage' => 'required_if:discount_type,percentage|nullable|numeric|between:0,100',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'min_purchase_amount' => 'nullable|numeric|min:0',
                'start_date' => 'required|date|after:now',
                'end_date' => 'nullable|date|after:start_date',
                'usage_limit_total' => 'nullable|integer|min:1',
                'usage_limit_per_user' => 'nullable|integer|min:1',
                'usage_limit_per_tenant' => 'nullable|integer|min:1',
                'requires_code' => 'boolean',
                'auto_apply' => 'boolean',
                'stackable' => 'boolean',
                'new_users_only' => 'boolean',
                'existing_users_only' => 'boolean',
                'first_purchase_only' => 'boolean',
                'display_banner' => 'boolean',
                'banner_text' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'applicable_plans' => 'nullable|array',
                'applicable_tenants' => 'nullable|array',
                'target_audience' => 'nullable|array',
            ]);

            $promotion = Promotion::create(array_merge($validatedData, [
                'created_by' => auth()->id()
            ]));

            return response()->json([
                'message' => 'Promotion créée avec succès',
                'promotion' => $promotion->load(['createdBy'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Promotion $promotion)
    {
        try {
            $promotion->load(['createdBy', 'approvedBy', 'rules', 'usages']);
            
            return response()->json($promotion);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Promotion $promotion)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'code' => [
                    'string', 
                    'max:50',
                    Rule::unique('promotions')->ignore($promotion->id)
                ],
                'description' => 'nullable|string',
                'type' => 'in:discount,trial,bonus',
                'discount_type' => 'required_if:type,discount|in:fixed,percentage',
                'discount_value' => 'required_if:discount_type,fixed|nullable|numeric|min:0',
                'discount_percentage' => 'required_if:discount_type,percentage|nullable|numeric|between:0,100',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'min_purchase_amount' => 'nullable|numeric|min:0',
                'start_date' => 'date',
                'end_date' => 'nullable|date|after:start_date',
                'usage_limit_total' => 'nullable|integer|min:1',
                'usage_limit_per_user' => 'nullable|integer|min:1',
                'usage_limit_per_tenant' => 'nullable|integer|min:1',
                'requires_code' => 'boolean',
                'auto_apply' => 'boolean',
                'stackable' => 'boolean',
                'new_users_only' => 'boolean',
                'existing_users_only' => 'boolean',
                'first_purchase_only' => 'boolean',
                'display_banner' => 'boolean',
                'banner_text' => 'nullable|string',
                'terms_and_conditions' => 'nullable|string',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'applicable_plans' => 'nullable|array',
                'applicable_tenants' => 'nullable|array',
                'target_audience' => 'nullable|array',
            ]);

            $promotion->update($validatedData);

            return response()->json([
                'message' => 'Promotion mise à jour avec succès',
                'promotion' => $promotion->fresh()->load(['createdBy', 'approvedBy'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Promotion $promotion)
    {
        try {
            // Vérifier si la promotion a été utilisée
            if ($promotion->current_usage_count > 0) {
                return response()->json([
                    'error' => 'Impossible de supprimer une promotion déjà utilisée'
                ], 422);
            }

            $promotion->delete();

            return response()->json([
                'message' => 'Promotion supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function activate(Promotion $promotion)
    {
        try {
            $promotion->update(['is_active' => true]);
            
            return response()->json([
                'message' => 'Promotion activée avec succès',
                'promotion' => $promotion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'activation de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deactivate(Promotion $promotion)
    {
        try {
            $promotion->update(['is_active' => false]);
            
            return response()->json([
                'message' => 'Promotion désactivée avec succès',
                'promotion' => $promotion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la désactivation de la promotion',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $totalPromotions = Promotion::count();
            $activePromotions = Promotion::active()->count();
            $totalUsage = Promotion::sum('current_usage_count');
            $totalRevenue = Promotion::sum('total_revenue_generated');
            $totalDiscount = Promotion::sum('total_discount_given');

            $topPromotions = Promotion::orderBy('current_usage_count', 'desc')
                ->limit(5)
                ->get(['name', 'code', 'current_usage_count', 'total_revenue_generated']);

            return response()->json([
                'total_promotions' => $totalPromotions,
                'active_promotions' => $activePromotions,
                'total_usage' => $totalUsage,
                'total_revenue' => $totalRevenue,
                'total_discount' => $totalDiscount,
                'top_promotions' => $topPromotions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function validateCode(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'user_id' => 'required|integer',
                'tenant_id' => 'nullable|integer',
                'amount' => 'required|numeric|min:0'
            ]);

            $promotion = Promotion::where('code', $request->code)->first();

            if (!$promotion) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Code promo invalide'
                ]);
            }

            if (!$promotion->canBeUsedBy($request->user_id, $request->tenant_id)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Code promo non applicable'
                ]);
            }

            $discount = $promotion->calculateDiscount($request->amount);

            return response()->json([
                'valid' => true,
                'promotion' => $promotion,
                'discount' => $discount,
                'final_amount' => $request->amount - $discount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la validation du code promo',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}