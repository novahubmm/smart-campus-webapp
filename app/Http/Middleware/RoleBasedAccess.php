<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('Unauthenticated', 401);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return ApiResponse::error('Access denied. Required role: ' . implode(' or ', $roles), 403);
        }

        return $next($request);
    }
}