<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register Spatie Permission gates
        // This allows using Gate::allows() with permission names directly
        Gate::before(function ($user, $ability) {
            try {
                // Check if user has the permission via Spatie
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($ability) ? true : null;
                }
            } catch (\Exception $e) {
                // If permission doesn't exist, return null to pass to next check
                return null;
            }

            return null;
        });
    }
}
