<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $setting = Setting::first();

        $allComplete = $setting
            && $setting->setup_completed_school_info
            && $setting->setup_completed_academic
            && $setting->setup_completed_event_and_announcements
            && $setting->setup_completed_time_table_and_attendance
            && $setting->setup_completed_finance;

        if ($allComplete) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
