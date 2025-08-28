<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLoueurPermissions
{
    /**
     * Handle an incoming request for loueur (rental) users.
     * 
     * Loueurs have limited permissions:
     * - Can access: etat-des-lieux, vehicles (read-only), profile
     * - Cannot access: finances, user management, analytics
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        
        // Si ce n'est pas un loueur, passer au middleware suivant
        if ($user->role !== 'loueur') {
            return $next($request);
        }
        
        // Définir les routes autorisées pour les loueurs
        $allowedPaths = [
            // États des lieux (accès complet)
            'etat-des-lieux',
            'etat-des-lieux/*',
            
            // Véhicules (lecture seule)
            'vehicles',
            
            // Profil utilisateur
            'profile/*',
            
            // Upload de médias (pour les photos d'inspection)
            'media/*',
            'vehicles/*/media',
        ];
        
        $currentPath = $request->path();
        $currentPath = str_replace('api/', '', $currentPath);
        
        // Vérifier si la route est autorisée
        foreach ($allowedPaths as $allowedPath) {
            if (fnmatch($allowedPath, $currentPath)) {
                // Pour les véhicules, seules les lectures sont autorisées
                if (str_contains($currentPath, 'vehicles') && 
                    !in_array($request->method(), ['GET', 'HEAD'])) {
                    return response()->json([
                        'message' => 'Accès refusé : les loueurs ne peuvent que consulter les véhicules',
                        'error' => 'loueur_readonly_vehicles'
                    ], 403);
                }
                
                return $next($request);
            }
        }
        
        // Routes interdites pour les loueurs
        $forbiddenRoutes = [
            'finances/*',
            'analytics/*',
            'users*',
            'transactions/*',
            'internal/*',
        ];
        
        foreach ($forbiddenRoutes as $forbiddenRoute) {
            if (fnmatch($forbiddenRoute, $currentPath)) {
                return response()->json([
                    'message' => 'Accès refusé : cette fonctionnalité n\'est pas disponible pour les loueurs',
                    'error' => 'loueur_access_denied',
                    'allowed_features' => [
                        'États des lieux (création/modification)',
                        'Consultation des véhicules',
                        'Gestion du profil',
                        'Upload de photos d\'inspection'
                    ]
                ], 403);
            }
        }
        
        // Par défaut, autoriser les autres routes (notifications, etc.)
        return $next($request);
    }
}