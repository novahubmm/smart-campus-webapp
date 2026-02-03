<?php

namespace App\Interfaces;

use App\DTOs\Attendance\StudentAttendanceData;
use App\DTOs\Attendance\StudentAttendanceFilterData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface StudentAttendanceRepositoryInterface
{
    public function getClassDailySummary(Carbon $date, ?string $classId = null, ?string $gradeId = null): Collection;

    public function getStudentsWithMonthlyStat(StudentAttendanceFilterData $filter): Collection;

    public function getRegisterData(string $classId, Carbon $date): array;

    public function getClassDetailData(string $classId, Carbon $date): array;

    public function getStudentDetailData(string $studentId, Carbon $startDate, Carbon $endDate): array;

    public function saveRegister(Carbon $date, string $classId, array $rows, ?string $markedBy): void;
}
