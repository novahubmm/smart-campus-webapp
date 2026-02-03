<?php

namespace App\Http\Controllers;

use App\DTOs\TimeTableAttendance\TimeTableSetupData;
use App\Http\Requests\TimeTableAttendanceSetupRequest;
use App\Models\Setting;
use App\Services\TimeTableAttendanceService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TimeTableAttendanceSetupController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly TimeTableAttendanceService $service
    ) {}

    public function index(): View
    {
        $setting = $this->service->getSetup();

        $defaults = [
            'number_of_periods_per_day' => $setting->number_of_periods_per_day ?? 7,
            'minute_per_period' => $setting->minute_per_period ?? 45,
            'break_duration' => $setting->break_duration ?? 30,
            'school_start_time' => $setting->school_start_time ?? '08:00',
            'school_end_time' => $setting->school_end_time ?? '15:30',
            'week_days' => $setting->week_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ];

        return view('attendance.setup', [
            'setting' => $setting,
            'defaults' => $defaults,
        ]);
    }

    public function store(TimeTableAttendanceSetupRequest $request): RedirectResponse
    {
        $data = TimeTableSetupData::from($request->validated());

        $this->service->saveSetup($data);

        $this->logActivity('setup_complete', 'TimeTableAttendanceSetup', null, 'Completed timetable & attendance setup');

        // Check if all setup steps are completed
        $setting = Setting::first();
        $allSetupCompleted = $setting
            && $setting->setup_completed_school_info
            && $setting->setup_completed_academic
            && $setting->setup_completed_event_and_announcements
            && $setting->setup_completed_time_table_and_attendance
            && $setting->setup_completed_finance;

        if ($allSetupCompleted) {
            return redirect()
                ->route('dashboard')
                ->with('success', __('Time-table & attendance setup completed successfully!'));
        }

        return redirect()
            ->route('setup.overview')
            ->with('success', __('Time-table & attendance setup completed successfully! Please complete the remaining setup steps.'));
    }
}
