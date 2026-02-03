<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianDashboardRepositoryInterface
{
    public function getDashboardData(StudentProfile $student): array;

    public function getTodaySchedule(StudentProfile $student): array;

    public function getUpcomingHomework(StudentProfile $student, int $limit = 5): array;

    public function getRecentAnnouncements(StudentProfile $student, int $limit = 5): array;

    public function getFeeReminder(StudentProfile $student): array;

    public function getCurrentClass(StudentProfile $student): ?array;

    public function getNextClass(StudentProfile $student): ?array;
}
