<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TestDebugMiddleware;
use App\Http\Middleware\IsSuperAdminInterne;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global API middleware
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            // Désactivé pour éviter les problèmes CSRF cross-origin - utilisation pure Bearer token
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Custom middleware groups
        $middleware->group('tenant', [
            \App\Http\Middleware\ConditionalTenant::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'is_super_admin_interne' => IsSuperAdminInterne::class,
            'ensure_permissions' => \App\Http\Middleware\EnsureUserHasPermissions::class,
            'check_incomplete_profile' => \App\Http\Middleware\CheckIncompleteProfile::class,
        ]);

        // Temporairement désactivé - cause des erreurs 500 avec Spatie Permission
        // $middleware->appendToGroup('api', [
        //     \App\Http\Middleware\EnsureUserHasPermissions::class,
        // ]);

        // Configure auth redirect for API requests
        $middleware->redirectUsersTo(function ($request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // Don't redirect API requests
            }
            return '/login'; // Regular web redirect (unused for this API-only app)
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gérer l'authentification échouée pour les API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié.',
                    'error' => 'Unauthenticated'
                ], 401);
            }
        });
    })->create();
