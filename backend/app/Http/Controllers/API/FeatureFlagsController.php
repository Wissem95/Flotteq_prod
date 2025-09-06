<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeatureFlagsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = FeatureFlag::with(['parentFlag', 'childFlags', 'enabledBy', 'disabledBy']);

            // Filtres
            if ($request->filled('status')) {
                if ($request->status === 'enabled') {
                    $query->enabled();
                } elseif ($request->status === 'disabled') {
                    $query->disabled();
                }
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('risk_level')) {
                $query->where('risk_level', $request->risk_level);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('key', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $flags = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json($flags);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des feature flags',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'key' => 'required|string|max:100|unique:feature_flags,key',
                'description' => 'nullable|string',
                'type' => 'required|in:boolean,variant,experiment',
                'rollout_percentage' => 'integer|between:0,100',
                'target_users' => 'nullable|array',
                'target_tenants' => 'nullable|array',
                'target_plans' => 'nullable|array',
                'target_regions' => 'nullable|array',
                'exclude_users' => 'nullable|array',
                'exclude_tenants' => 'nullable|array',
                'conditions' => 'nullable|array',
                'variants' => 'nullable|array',
                'default_variant' => 'nullable|string',
                'start_date' => 'nullable|date|after:now',
                'end_date' => 'nullable|date|after:start_date',
                'dependencies' => 'nullable|array',
                'parent_flag_id' => 'nullable|exists:feature_flags,id',
                'category' => 'required|string|max:100',
                'tags' => 'nullable|array',
                'risk_level' => 'required|in:low,medium,high',
                'impact_areas' => 'nullable|array',
                'rollback_plan' => 'nullable|array',
                'monitoring_metrics' => 'nullable|array',
                'success_criteria' => 'nullable|array',
                'failure_threshold' => 'nullable|integer|min:1',
                'auto_disable_on_error' => 'boolean',
                'documentation_url' => 'nullable|url',
                'jira_ticket' => 'nullable|string',
                'notes' => 'nullable|string',
                'analytics_enabled' => 'boolean',
                'track_usage' => 'boolean',
            ]);

            $flag = FeatureFlag::create(array_merge($validatedData, [
                'status' => 'disabled' // Nouveau flag toujours désactivé par défaut
            ]));

            return response()->json([
                'message' => 'Feature flag créé avec succès',
                'flag' => $flag
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(FeatureFlag $featureFlag)
    {
        try {
            $featureFlag->load([
                'parentFlag', 
                'childFlags', 
                'enabledBy', 
                'disabledBy', 
                'testedBy', 
                'approvedBy'
            ]);
            
            return response()->json($featureFlag);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, FeatureFlag $featureFlag)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'string|max:255',
                'key' => [
                    'string', 
                    'max:100',
                    Rule::unique('feature_flags')->ignore($featureFlag->id)
                ],
                'description' => 'nullable|string',
                'type' => 'in:boolean,variant,experiment',
                'rollout_percentage' => 'integer|between:0,100',
                'target_users' => 'nullable|array',
                'target_tenants' => 'nullable|array',
                'target_plans' => 'nullable|array',
                'target_regions' => 'nullable|array',
                'exclude_users' => 'nullable|array',
                'exclude_tenants' => 'nullable|array',
                'conditions' => 'nullable|array',
                'variants' => 'nullable|array',
                'default_variant' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'dependencies' => 'nullable|array',
                'parent_flag_id' => 'nullable|exists:feature_flags,id',
                'category' => 'string|max:100',
                'tags' => 'nullable|array',
                'risk_level' => 'in:low,medium,high',
                'impact_areas' => 'nullable|array',
                'rollback_plan' => 'nullable|array',
                'monitoring_metrics' => 'nullable|array',
                'success_criteria' => 'nullable|array',
                'failure_threshold' => 'nullable|integer|min:1',
                'auto_disable_on_error' => 'boolean',
                'documentation_url' => 'nullable|url',
                'jira_ticket' => 'nullable|string',
                'notes' => 'nullable|string',
                'analytics_enabled' => 'boolean',
                'track_usage' => 'boolean',
            ]);

            $featureFlag->update($validatedData);

            return response()->json([
                'message' => 'Feature flag mis à jour avec succès',
                'flag' => $featureFlag->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(FeatureFlag $featureFlag)
    {
        try {
            // Vérifier s'il y a des flags enfants
            if ($featureFlag->childFlags()->exists()) {
                return response()->json([
                    'error' => 'Impossible de supprimer un feature flag avec des dépendances'
                ], 422);
            }

            $featureFlag->delete();

            return response()->json([
                'message' => 'Feature flag supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function enable(FeatureFlag $featureFlag)
    {
        try {
            $featureFlag->enable(auth()->id());
            
            return response()->json([
                'message' => 'Feature flag activé avec succès',
                'flag' => $featureFlag->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'activation du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function disable(Request $request, FeatureFlag $featureFlag)
    {
        try {
            $reason = $request->get('reason');
            $featureFlag->disable(auth()->id(), $reason);
            
            return response()->json([
                'message' => 'Feature flag désactivé avec succès',
                'flag' => $featureFlag->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la désactivation du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkFlag(Request $request)
    {
        try {
            $request->validate([
                'key' => 'required|string',
                'user_id' => 'nullable|integer',
                'tenant_id' => 'nullable|integer'
            ]);

            $isEnabled = FeatureFlag::checkFeature(
                $request->key, 
                $request->user_id, 
                $request->tenant_id
            );

            $flag = FeatureFlag::where('key', $request->key)->first();
            $variant = null;

            if ($isEnabled && $flag && $request->user_id) {
                $variant = $flag->getVariantFor($request->user_id);
                $flag->recordUsage($request->user_id);
            }

            return response()->json([
                'enabled' => $isEnabled,
                'variant' => $variant,
                'key' => $request->key
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkCheck(Request $request)
    {
        try {
            $request->validate([
                'keys' => 'required|array',
                'keys.*' => 'string',
                'user_id' => 'nullable|integer',
                'tenant_id' => 'nullable|integer'
            ]);

            $results = [];

            foreach ($request->keys as $key) {
                $isEnabled = FeatureFlag::checkFeature(
                    $key, 
                    $request->user_id, 
                    $request->tenant_id
                );

                $flag = FeatureFlag::where('key', $key)->first();
                $variant = null;

                if ($isEnabled && $flag && $request->user_id) {
                    $variant = $flag->getVariantFor($request->user_id);
                    $flag->recordUsage($request->user_id);
                }

                $results[$key] = [
                    'enabled' => $isEnabled,
                    'variant' => $variant
                ];
            }

            return response()->json(['flags' => $results]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification des feature flags',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $totalFlags = FeatureFlag::count();
            $enabledFlags = FeatureFlag::enabled()->count();
            $highRiskFlags = FeatureFlag::highRisk()->count();
            
            $flagsByCategory = FeatureFlag::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category');

            $flagsByRisk = FeatureFlag::selectRaw('risk_level, COUNT(*) as count')
                ->groupBy('risk_level')
                ->pluck('count', 'risk_level');

            $recentlyModified = FeatureFlag::where('updated_at', '>=', now()->subDays(7))
                ->count();

            return response()->json([
                'total_flags' => $totalFlags,
                'enabled_flags' => $enabledFlags,
                'high_risk_flags' => $highRiskFlags,
                'flags_by_category' => $flagsByCategory,
                'flags_by_risk' => $flagsByRisk,
                'recently_modified' => $recentlyModified
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCategories()
    {
        try {
            $categories = FeatureFlag::distinct()
                ->pluck('category')
                ->filter()
                ->sort()
                ->values();

            return response()->json(['categories' => $categories]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des catégories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clone(FeatureFlag $featureFlag)
    {
        try {
            $clonedData = $featureFlag->toArray();
            unset($clonedData['id']);
            unset($clonedData['created_at']);
            unset($clonedData['updated_at']);
            
            $clonedData['name'] = $featureFlag->name . ' (Copy)';
            $clonedData['key'] = $featureFlag->key . '_copy_' . time();
            $clonedData['status'] = 'disabled';

            $clonedFlag = FeatureFlag::create($clonedData);

            return response()->json([
                'message' => 'Feature flag cloné avec succès',
                'flag' => $clonedFlag
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du clonage du feature flag',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $totalFlags = FeatureFlag::count();
            $enabledFlags = FeatureFlag::enabled()->count();
            $disabledFlags = FeatureFlag::disabled()->count();
            
            $flagsByCategory = FeatureFlag::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();
                
            $flagsByRiskLevel = FeatureFlag::selectRaw('risk_level, COUNT(*) as count')
                ->groupBy('risk_level')
                ->pluck('count', 'risk_level')
                ->toArray();

            return response()->json([
                'total_flags' => $totalFlags,
                'enabled_flags' => $enabledFlags,
                'disabled_flags' => $disabledFlags,
                'by_category' => $flagsByCategory,
                'by_risk_level' => $flagsByRiskLevel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}