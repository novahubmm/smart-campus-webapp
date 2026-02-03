<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianAttendanceRepositoryInterface
{
    public function getAttendanceRecords(StudentProfile $student, int $month, int $year): array;

    public function getAttendanceSummary(StudentProfile $student, int $month, int $year): array;

    public function getAttendanceCalendar(StudentProfile $student, int $month, int $year): array;

    public function getAttendanceStats(StudentProfile $student): array;
}
