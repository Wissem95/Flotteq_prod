<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\UserPermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserHasPermissions
{
    /**
     * Handle an incoming request.
     *
     * Ce middleware assigne automatiquement les permissions de base
     * à tout utilisateur authentifié qui n'en a pas.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Si l'utilisateur est authentifié et n'a aucune permission
        if ($user && $user->permissions()->count() === 0) {
            try {
                UserPermissionService::assignDefaultPermissions($user);
            } catch (\Exception $e) {
                // Log l'erreur mais continue - ne pas bloquer la requête
                Log::warning("Impossible d'assigner les permissions à l'utilisateur {$user->id}: " . $e->getMessage());
            }
        }

        return $next($request);
    }
}
