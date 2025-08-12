<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdminInterne
{
    public function __construct()
    {
        file_put_contents('/tmp/middleware_debug.txt', __FILE__);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['debug' => 'No user found'], 403);
            }
            
            // Just check if user has the required fields, without calling methods
            if (!isset($user->is_internal) || !$user->is_internal) {
                return response()->json(['debug' => 'User not internal', 'user_id' => $user->id], 403);
            }
            
            if (!isset($user->role_interne) || !in_array($user->role_interne, ['super_admin', 'admin'])) {
                return response()->json(['debug' => 'User not admin', 'role_interne' => $user->role_interne], 403);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['debug' => 'Middleware error', 'error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }
}
