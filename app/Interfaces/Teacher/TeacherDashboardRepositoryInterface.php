<?php

namespace App\Interfaces\Teacher;

use App\Models\User;
use Illuminate\Support\Collection;

interface TeacherDashboardRepositoryInterface
{
    public function getQuickStats(User $teacher): array;

    public function getTodayClasses(User $teacher): Collection;

    public function getTodayClassDetail(User $teacher, string $periodId): ?array;

    public function getWeeklySchedule(User $teacher): array;

    public function getFullSchedule(User $teacher): array;
}
