<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class ConditionalTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Si l'utilisateur est interne, on skip la vérification tenant
        if ($user && $user->isInternal()) {
            return $next($request);
        }
        
        // Pour les utilisateurs non-internes, on vérifie le tenant
        $tenantId = $request->header('X-Tenant-ID');
        
        if (!$tenantId) {
            return response()->json([
                'message' => 'X-Tenant-ID header is required for tenant users',
                'error' => 'missing_tenant_header'
            ], 400);
        }
        
        $tenant = Tenant::where('id', $tenantId)
            ->where('is_active', true)
            ->first();
            
        if (!$tenant) {
            return response()->json([
                'message' => 'Invalid or inactive tenant',
                'error' => 'invalid_tenant'
            ], 400);
        }
        
        // Vérifier que l'utilisateur appartient à ce tenant
        if ($user && $user->tenant_id !== (int)$tenantId) {
            return response()->json([
                'message' => 'User does not belong to this tenant',
                'error' => 'tenant_mismatch'
            ], 403);
        }
        
        // Stocker le tenant dans la requête pour les contrôleurs qui en ont besoin
        $request->attributes->set('tenant', $tenant);
        
        return $next($request);
    }
}