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
                return response()->json(['error' => 'No user found'], 403);
            }
            
            if (!$user->isInternal()) {
                return response()->json(['error' => 'User not internal'], 403);
            }
            
            if (!$user->isSuperAdmin()) {
                return response()->json(['error' => 'User not super admin'], 403);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Middleware error: ' . $e->getMessage()], 500);
        }
    }
}
