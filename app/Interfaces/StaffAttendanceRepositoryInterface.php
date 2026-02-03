<?php

namespace App\Interfaces;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface StaffAttendanceRepositoryInterface
{
    public function getDailyRegister(Carbon $date): Collection;

    public function getMonthlySummary(Carbon $month): Collection;

    public function getRangeSummary(Carbon $start, Carbon $end): Collection;

    public function getStaffDetail(string $staffId, Carbon $start, Carbon $end): array;
}
