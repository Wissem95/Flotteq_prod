<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    /**
     * Handle an incoming request to check tenant subscription limits
     */
    public function handle(Request $request, Closure $next, ?string $resource = null): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['error' => 'Tenant requis'], 400);
        }

        $tenant = Tenant::find($user->tenant_id);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant invalide'], 404);
        }

        // If no resource specified, just continue (read-only operations)
        if (!$resource) {
            return $next($request);
        }

        // Check limits for resource creation
        if ($request->isMethod('POST')) {
            $check = $this->checkResourceLimit($tenant, $resource);
            
            if (!$check['allowed']) {
                return response()->json([
                    'error' => 'Limite du plan atteinte',
                    'message' => $check['message'],
                    'current_plan' => $check['plan_name'] ?? 'Aucun',
                    'limit_details' => $check['details'],
                    'upgrade_required' => true,
                    'suggestion' => $check['suggestion'] ?? 'Passez à un plan supérieur'
                ], 422);
            }
        }

        // Add tenant info to request for use in controllers
        $request->merge([
            'tenant' => $tenant,
            'tenant_limits' => $tenant->getSubscriptionLimits()
        ]);

        return $next($request);
    }

    /**
     * Check if tenant can add the specified resource
     */
    private function checkResourceLimit(Tenant $tenant, string $resource): array
    {
        $limits = $tenant->getSubscriptionLimits();
        
        switch ($resource) {
            case 'vehicles':
                if (!$tenant->canAddVehicles()) {
                    return [
                        'allowed' => false,
                        'message' => "Vous avez atteint la limite de véhicules de votre plan {$limits['plan_name']} ({$limits['vehicles_limit']} véhicules maximum).",
                        'plan_name' => $limits['plan_name'],
                        'details' => [
                            'vehicles_used' => $limits['vehicles_used'],
                            'vehicles_limit' => $limits['vehicles_limit'],
                            'vehicles_available' => $limits['vehicles_available']
                        ],
                        'suggestion' => $this->getUpgradeSuggestion('vehicles', $limits['vehicles_limit'])
                    ];
                }
                break;

            case 'users':
                if (!$tenant->canAddUsers()) {
                    return [
                        'allowed' => false,
                        'message' => "Vous avez atteint la limite d'utilisateurs de votre plan {$limits['plan_name']} ({$limits['users_limit']} utilisateurs maximum).",
                        'plan_name' => $limits['plan_name'],
                        'details' => [
                            'users_used' => $limits['users_used'],
                            'users_limit' => $limits['users_limit'],
                            'users_available' => $limits['users_available']
                        ],
                        'suggestion' => $this->getUpgradeSuggestion('users', $limits['users_limit'])
                    ];
                }
                break;

            default:
                // Unknown resource, allow by default
                break;
        }

        return [
            'allowed' => true,
            'plan_name' => $limits['plan_name'],
            'details' => $limits
        ];
    }

    /**
     * Get upgrade suggestion based on current limits
     */
    private function getUpgradeSuggestion(string $resource, int $currentLimit): string
    {
        $suggestions = [
            'vehicles' => [
                1 => 'Passez au plan Starter (5 véhicules)',
                5 => 'Passez au plan Professional (20 véhicules)',
                20 => 'Passez au plan Enterprise (100 véhicules)',
                100 => 'Contactez-nous pour un plan personnalisé'
            ],
            'users' => [
                1 => 'Passez au plan Starter (3 utilisateurs)',
                3 => 'Passez au plan Professional (10 utilisateurs)', 
                10 => 'Passez au plan Enterprise (50 utilisateurs)',
                50 => 'Contactez-nous pour un plan personnalisé'
            ]
        ];

        $resourceSuggestions = $suggestions[$resource] ?? [];
        
        foreach ($resourceSuggestions as $limit => $suggestion) {
            if ($currentLimit <= $limit) {
                return $suggestion;
            }
        }

        return 'Contactez-nous pour un plan personnalisé';
    }

    /**
     * Static method to check tenant limits from controllers
     */
    public static function checkTenantLimits(int $tenantId, string $resource): array
    {
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return ['allowed' => false, 'message' => 'Tenant invalide'];
        }

        $middleware = new self();
        return $middleware->checkResourceLimit($tenant, $resource);
    }
}