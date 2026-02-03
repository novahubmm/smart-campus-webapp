<?php

namespace App\Interfaces;

use App\DTOs\TimeTableAttendance\TimeTableSetupData;
use App\Models\Setting;

interface TimeTableAttendanceRepositoryInterface
{
    public function firstOrCreateSetting(): Setting;

    public function updateSetup(Setting $setting, TimeTableSetupData $data): Setting;
}
