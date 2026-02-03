<?php

namespace App\Services;

use App\DTOs\TimeTableAttendance\TimeTableSetupData;
use App\Interfaces\TimeTableAttendanceRepositoryInterface;
use App\Models\Setting;

class TimeTableAttendanceService
{
    public function __construct(
        private readonly TimeTableAttendanceRepositoryInterface $repository
    ) {}

    public function getSetup(): Setting
    {
        return $this->repository->firstOrCreateSetting();
    }

    public function saveSetup(TimeTableSetupData $data): Setting
    {
        $setting = $this->repository->firstOrCreateSetting();

        return $this->repository->updateSetup($setting, $data);
    }
}
