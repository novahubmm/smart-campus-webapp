<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupCompleted
{
    public function handle(Request $request, Closure $next, string $domain): Response|RedirectResponse
    {
        $setting = Setting::first();

        $rules = [
            'school' => [
                'flag' => 'setup_completed_school_info',
                'redirect' => route('settings.school-info'),
                'message' => __('Complete school info setup before continuing.'),
            ],
            'academic' => [
                'flag' => 'setup_completed_academic',
                'redirect' => route('academic-setup.index'),
                'message' => __('Complete academic setup before using this module.'),
            ],
            'events' => [
                'flag' => 'setup_completed_event_and_announcements',
                'redirect' => route('event-announcement-setup.index'),
                'message' => __('Complete events & announcements setup before using this module.'),
            ],
            'attendance' => [
                'flag' => 'setup_completed_time_table_and_attendance',
                'redirect' => route('time-table-attendance-setup.index'),
                'message' => __('Complete timetable & attendance setup before using this module.'),
            ],
            'finance' => [
                'flag' => 'setup_completed_finance',
                'redirect' => route('finance-setup.index'),
                'message' => __('Complete finance setup before using this module.'),
            ],
        ];

        if (!isset($rules[$domain])) {
            return $next($request);
        }

        $rule = $rules[$domain];
        $isComplete = (bool) optional($setting)->{$rule['flag']};

        if (!$isComplete) {
            return redirect()
                ->route('setup.overview')
                ->with('error', $rule['message'])
                ->with('missing_setup', $domain);
        }

        return $next($request);
    }
}
