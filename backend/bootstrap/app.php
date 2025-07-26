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
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Custom middleware groups
        $middleware->group('tenant', [
            \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
            // \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class, // Désactivé pour les API
        ]);

        // Middleware aliases
        $middleware->alias([
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'is_super_admin_interne' => IsSuperAdminInterne::class,
            'ensure_permissions' => \App\Http\Middleware\EnsureUserHasPermissions::class,
            'check_incomplete_profile' => \App\Http\Middleware\CheckIncompleteProfile::class,
        ]);

        // Ajouter automatiquement le middleware de permissions après auth:sanctum
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\EnsureUserHasPermissions::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
