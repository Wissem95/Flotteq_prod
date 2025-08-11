<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',           // Exclure toutes les routes API
        '/api/*',          // Exclure toutes les routes API (avec slash)
        'sanctum/*',       // Exclure les routes Sanctum
        '/sanctum/*',      // Exclure les routes Sanctum (avec slash)
    ];

    /**
     * Determine if the HTTP request uses a 'read' verb.
     */
    protected function isReading($request): bool
    {
        // Considérer les requêtes API comme toujours "lecture" pour bypasser CSRF
        if ($request->is('api/*') || $request->is('/api/*')) {
            return true;
        }

        return parent::isReading($request);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     */
    protected function inExceptArray($request): bool
    {
        // Toujours bypasser CSRF pour les routes API
        if ($request->is('api/*') || $request->is('/api/*')) {
            return true;
        }

        return parent::inExceptArray($request);
    }
}