<?php

namespace App\Repositories;

use App\DTOs\TimeTableAttendance\TimeTableSetupData;
use App\Interfaces\TimeTableAttendanceRepositoryInterface;
use App\Models\Setting;

class TimeTableAttendanceRepository implements TimeTableAttendanceRepositoryInterface
{
    public function firstOrCreateSetting(): Setting
    {
        return Setting::firstOrCreate([]);
    }

    public function updateSetup(Setting $setting, TimeTableSetupData $data): Setting
    {
        $setting->fill([
            'number_of_periods_per_day' => $data->numberOfPeriodsPerDay,
            'minute_per_period' => $data->minutePerPeriod,
            'break_duration' => $data->breakDuration,
            'school_start_time' => $data->schoolStartTime,
            'school_end_time' => $data->schoolEndTime,
            'week_days' => $data->weekDays,
            'setup_completed_time_table_and_attendance' => true,
        ]);

        $setting->save();

        return $setting->fresh();
    }
}
