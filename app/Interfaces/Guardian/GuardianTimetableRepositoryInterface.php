<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianTimetableRepositoryInterface
{
    public function getFullTimetable(StudentProfile $student): array;

    public function getDayTimetable(StudentProfile $student, string $day): array;

    public function getClassInfo(StudentProfile $student): array;
}
