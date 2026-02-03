<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetupController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            // If there's an error accessing settings, create a default setting
            $setting = null;
        }

        $steps = [
            [
                'key' => 'school',
                'title' => 'School Info',
                'description' => 'Add school name, contact details, and branding assets.',
                'complete' => $setting ? (bool) $setting->setup_completed_school_info : false,
                'action' => url('/settings/school-info'),
                'action_label' => 'Update School Info',
            ],
            [
                'key' => 'academic',
                'title' => 'Academic Setup',
                'description' => 'Configure batches, grades, classes, rooms, and subjects.',
                'complete' => $setting ? (bool) $setting->setup_completed_academic : false,
                'action' => url('/academic-setup'),
                'action_label' => 'Open Academic Setup',
            ],
            [
                'key' => 'events',
                'title' => 'Events & Announcements',
                'description' => 'Prepare event categories and announcement templates.',
                'complete' => $setting ? (bool) $setting->setup_completed_event_and_announcements : false,
                'action' => url('/event-announcement-setup'),
                'action_label' => 'Open Events Setup',
            ],
            [
                'key' => 'attendance',
                'title' => 'Time-table & Attendance',
                'description' => 'Configure periods, timetables, and attendance rules.',
                'complete' => $setting ? (bool) $setting->setup_completed_time_table_and_attendance : false,
                'action' => url('/time-table-attendance-setup'),
                'action_label' => 'Open Attendance Setup',
            ],
            [
                'key' => 'finance',
                'title' => 'Finance',
                'description' => 'Set up finance parameters, fees, and payroll defaults.',
                'complete' => $setting ? (bool) $setting->setup_completed_finance : false,
                'action' => url('/finance-setup'),
                'action_label' => 'Open Finance Setup',
            ],
        ];

        $missing = $request->session()->get('missing_setup');
       
        try {
            return view('setup.overview', [
                'steps' => $steps,
                'missing' => $missing,
            ]);
        } catch (\Exception $e) {
            // Log the error and show a fallback view
            \Log::error('Setup overview error: ' . $e->getMessage(), [
                'steps' => $steps,
                'missing' => $missing,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple error view or redirect to dashboard
            return redirect()->route('dashboard')->with('error', 'Setup page is temporarily unavailable. Please try again later.');
        }
    }
}
