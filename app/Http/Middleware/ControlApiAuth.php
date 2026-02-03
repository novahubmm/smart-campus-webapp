<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ControlApiAuth
{
    /**
     * Handle an incoming request.
     *
     * Authenticate requests to the Control API using Bearer token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $expectedToken = config('app.control_api_token');

        if (!$token || !$expectedToken || $token !== $expectedToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing control API token'
            ], 401);
        }

        return $next($request);
    }
}