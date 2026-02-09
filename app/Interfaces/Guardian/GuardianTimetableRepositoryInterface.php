<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianTimetableRepositoryInterface
{
    public function getFullTimetable(StudentProfile $student): array;

    public function getDayTimetable(StudentProfile $student, string $day): array;

    public function getClassInfo(StudentProfile $student): array;

    // Enhanced methods for detailed class information
    public function getDetailedClassInfo(StudentProfile $student): array;

    public function getClassTeachers(StudentProfile $student): array;

    public function getClassStatistics(StudentProfile $student): array;
}
