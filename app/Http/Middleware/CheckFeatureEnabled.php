<?php

namespace App\Http\Middleware;

use App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $feature
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // System admins can access everything
        if ($request->user() && $request->user()->hasRole('system_admin')) {
            return $next($request);
        }

        // Check if feature is enabled
        if (!$this->featureService->isEnabled($feature)) {
            abort(403, 'This feature is not available.');
        }

        return $next($request);
    }
}
