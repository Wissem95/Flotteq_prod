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
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Désactiver la vérification CSRF pour les routes API
        // Les routes API utilisent l'authentification Bearer token via Sanctum
        $request->session()->put('_token', $request->header('X-CSRF-TOKEN', 'disabled'));
        
        return $next($request);
    }
}