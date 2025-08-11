<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableCsrfForApi
{
    /**
     * Handle an incoming request.
     * Désactive complètement la vérification CSRF pour les routes API
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Marquer la requête comme exempte de vérification CSRF
        $request->session()->put('_token', 'api-disabled');
        $request->session()->regenerateToken();
        
        // Ajouter un header pour identifier que CSRF est désactivé
        $response = $next($request);
        $response->headers->set('X-CSRF-Protection', 'disabled-for-api');
        
        return $response;
    }
}