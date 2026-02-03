<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * Check if maintenance mode is enabled and allow admin users to bypass it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance check for certain routes
        if ($this->shouldSkipMaintenanceCheck($request)) {
            return $next($request);
        }

        // Check if maintenance mode is enabled
        if (Setting::isMaintenanceMode()) {
            // Allow authenticated admin users to bypass maintenance mode
            if (auth()->check() && auth()->user()->hasRole('admin')) {
                return $next($request);
            }

            // Allow login routes so admins can authenticate
            if ($this->isAuthRoute($request)) {
                return $next($request);
            }

            // Show maintenance page for all other users
            return response()->view('errors.503', [
                'message' => Setting::getMaintenanceMessage()
            ], 503);
        }

        return $next($request);
    }

    /**
     * Check if the request should skip maintenance mode check
     */
    private function shouldSkipMaintenanceCheck(Request $request): bool
    {
        $skipRoutes = [
            'api/control/*', // Control Panel API routes
            'up', // Health check
        ];

        foreach ($skipRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is for authentication routes
     */
    private function isAuthRoute(Request $request): bool
    {
        $authRoutes = [
            'login',
            'logout',
            'password/*',
            'forgot-password',
            'reset-password',
        ];

        foreach ($authRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }
}
