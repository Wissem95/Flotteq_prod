<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSubscription;
use App\Models\Vehicle;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimits
{
    /**
     * Handle an incoming request to check plan limits
     */
    public function handle(Request $request, Closure $next, string $resource = null): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return $next($request);
        }

        // Obtenir l'abonnement actuel du tenant
        $subscription = $this->getCurrentSubscription($user->tenant_id);
        
        if (!$subscription) {
            // Pas d'abonnement = restrictions maximales (mode gratuit limité)
            return $this->handleNoSubscription($request, $next, $resource, $user->tenant_id);
        }

        // Vérifier les limites selon le type de ressource
        if ($resource) {
            $limitCheck = $this->checkResourceLimit($subscription, $resource, $user->tenant_id, $request);
            
            if (!$limitCheck['allowed']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Limite du plan atteinte',
                    'message' => $limitCheck['message'],
                    'current_plan' => $subscription->subscription->name,
                    'limit_details' => $limitCheck['details'],
                    'upgrade_required' => true,
                ], 422);
            }
        }

        // Ajouter les informations de plan à la requête pour usage ultérieur
        $request->attributes->set('current_subscription', $subscription);
        $request->attributes->set('plan_limits', [
            'vehicles_limit' => $subscription->subscription->max_vehicles ?? 1,
            'users_limit' => $subscription->subscription->max_users ?? 1,
            'vehicles_used' => Vehicle::where('tenant_id', $user->tenant_id)->count(),
            'users_used' => User::where('tenant_id', $user->tenant_id)->count(),
        ]);

        return $next($request);
    }

    /**
     * Obtenir l'abonnement actuel d'un tenant
     */
    private function getCurrentSubscription($tenantId)
    {
        // Récupérer l'abonnement actif du tenant via UserSubscription
        $primaryUser = User::where('tenant_id', $tenantId)
            ->orderBy('created_at')
            ->first();

        if (!$primaryUser) {
            return null;
        }

        return UserSubscription::with('subscription')
            ->where('user_id', $primaryUser->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    /**
     * Gérer les tenants sans abonnement (mode gratuit très limité)
     */
    private function handleNoSubscription(Request $request, Closure $next, string $resource = null, int $tenantId): Response
    {
        if ($resource === 'vehicles') {
            $vehicleCount = Vehicle::where('tenant_id', $tenantId)->count();
            if ($vehicleCount >= 1) { // Limite gratuite: 1 véhicule seulement
                return response()->json([
                    'success' => false,
                    'error' => 'Limite gratuite atteinte',
                    'message' => 'Vous avez atteint la limite de véhicules pour un compte gratuit. Souscrivez à un plan pour ajouter plus de véhicules.',
                    'current_plan' => 'Gratuit',
                    'limit_details' => [
                        'vehicles_used' => $vehicleCount,
                        'vehicles_limit' => 1,
                        'plan_required' => 'Starter (5 véhicules) ou supérieur'
                    ],
                    'upgrade_required' => true,
                ], 422);
            }
        }

        if ($resource === 'users') {
            $userCount = User::where('tenant_id', $tenantId)->count();
            if ($userCount >= 1) { // Limite gratuite: 1 utilisateur seulement
                return response()->json([
                    'success' => false,
                    'error' => 'Limite gratuite atteinte',
                    'message' => 'Vous avez atteint la limite d\'utilisateurs pour un compte gratuit. Souscrivez à un plan pour ajouter plus d\'utilisateurs.',
                    'current_plan' => 'Gratuit',
                    'limit_details' => [
                        'users_used' => $userCount,
                        'users_limit' => 1,
                        'plan_required' => 'Starter (3 utilisateurs) ou supérieur'
                    ],
                    'upgrade_required' => true,
                ], 422);
            }
        }

        return $next($request);
    }

    /**
     * Vérifier les limites pour une ressource spécifique
     */
    private function checkResourceLimit($subscription, string $resource, int $tenantId, Request $request): array
    {
        $plan = $subscription->subscription;
        
        switch ($resource) {
            case 'vehicles':
                return $this->checkVehicleLimit($plan, $tenantId);
                
            case 'users':
                return $this->checkUserLimit($plan, $tenantId);
                
            case 'maintenance':
                return $this->checkMaintenanceLimit($plan, $tenantId);
                
            default:
                return ['allowed' => true];
        }
    }

    /**
     * Vérifier la limite de véhicules
     */
    private function checkVehicleLimit($plan, int $tenantId): array
    {
        $currentCount = Vehicle::where('tenant_id', $tenantId)->count();
        $limit = $plan->max_vehicles ?? 5; // Default à 5 si pas défini
        
        if ($currentCount >= $limit) {
            return [
                'allowed' => false,
                'message' => "Vous avez atteint la limite de véhicules de votre plan {$plan->name} ({$limit} véhicules maximum).",
                'details' => [
                    'vehicles_used' => $currentCount,
                    'vehicles_limit' => $limit,
                    'plan_name' => $plan->name,
                    'suggested_action' => $limit < 20 ? 'Passez au plan Professional (20 véhicules)' : 'Passez au plan Enterprise (100 véhicules)'
                ]
            ];
        }

        // Avertissement si proche de la limite
        if ($currentCount >= ($limit * 0.8)) {
            return [
                'allowed' => true,
                'warning' => true,
                'message' => "Attention: vous approchez de la limite de véhicules ({$currentCount}/{$limit})",
                'details' => [
                    'vehicles_used' => $currentCount,
                    'vehicles_limit' => $limit,
                    'remaining' => $limit - $currentCount
                ]
            ];
        }

        return [
            'allowed' => true,
            'details' => [
                'vehicles_used' => $currentCount,
                'vehicles_limit' => $limit,
                'remaining' => $limit - $currentCount
            ]
        ];
    }

    /**
     * Vérifier la limite d'utilisateurs
     */
    private function checkUserLimit($plan, int $tenantId): array
    {
        $currentCount = User::where('tenant_id', $tenantId)->count();
        $limit = $plan->max_users ?? 3; // Default à 3 si pas défini
        
        if ($currentCount >= $limit) {
            return [
                'allowed' => false,
                'message' => "Vous avez atteint la limite d'utilisateurs de votre plan {$plan->name} ({$limit} utilisateurs maximum).",
                'details' => [
                    'users_used' => $currentCount,
                    'users_limit' => $limit,
                    'plan_name' => $plan->name,
                    'suggested_action' => $limit < 10 ? 'Passez au plan Professional (10 utilisateurs)' : 'Passez au plan Enterprise (50 utilisateurs)'
                ]
            ];
        }

        return [
            'allowed' => true,
            'details' => [
                'users_used' => $currentCount,
                'users_limit' => $limit,
                'remaining' => $limit - $currentCount
            ]
        ];
    }

    /**
     * Vérifier les limites de maintenance (si applicable selon le plan)
     */
    private function checkMaintenanceLimit($plan, int $tenantId): array
    {
        // Les fonctions de maintenance avancées peuvent être limitées selon le plan
        $features = $plan->features ?? [];
        
        if (is_string($features)) {
            $features = json_decode($features, true) ?? [];
        }

        // Plan Starter: maintenance de base seulement
        if ($plan->name === 'Starter' && !($features['advanced_maintenance'] ?? false)) {
            return [
                'allowed' => true,
                'message' => 'Fonctionnalités de maintenance de base disponibles',
                'details' => [
                    'available_features' => ['Planification basique', 'Historique', 'Alertes simples'],
                    'restricted_features' => ['Analytics maintenance', 'Rapports avancés', 'Intégrations externes']
                ]
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Méthode statique pour vérifier les limites depuis un contrôleur
     */
    public static function checkTenantLimits(int $tenantId, string $resource): array
    {
        $middleware = new self();
        
        $primaryUser = User::where('tenant_id', $tenantId)->first();
        if (!$primaryUser) {
            return ['allowed' => false, 'message' => 'Tenant invalide'];
        }

        $subscription = $middleware->getCurrentSubscription($tenantId);
        
        if (!$subscription) {
            return $middleware->handleNoSubscriptionCheck($tenantId, $resource);
        }

        return $middleware->checkResourceLimit($subscription, $resource, $tenantId, null);
    }

    /**
     * Vérification sans abonnement pour usage statique
     */
    private function handleNoSubscriptionCheck(int $tenantId, string $resource): array
    {
        if ($resource === 'vehicles') {
            $count = Vehicle::where('tenant_id', $tenantId)->count();
            return [
                'allowed' => $count < 1,
                'message' => $count >= 1 ? 'Limite gratuite atteinte (1 véhicule maximum)' : 'OK',
                'details' => ['used' => $count, 'limit' => 1]
            ];
        }

        if ($resource === 'users') {
            $count = User::where('tenant_id', $tenantId)->count();
            return [
                'allowed' => $count < 1,
                'message' => $count >= 1 ? 'Limite gratuite atteinte (1 utilisateur maximum)' : 'OK',
                'details' => ['used' => $count, 'limit' => 1]
            ];
        }

        return ['allowed' => true];
    }
}