<?php

namespace App\Interfaces;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface TeacherAttendanceRepositoryInterface
{
    public function getDailyRegister(Carbon $date): Collection;

    public function getMonthlySummary(Carbon $month): Collection;

    public function getRangeSummary(Carbon $start, Carbon $end): Collection;

    public function getTeacherDetail(string $teacherId, Carbon $start, Carbon $end): array;
}
