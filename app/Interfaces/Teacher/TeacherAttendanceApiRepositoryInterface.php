<?php

namespace App\Interfaces\Teacher;

use App\Models\User;

interface TeacherAttendanceApiRepositoryInterface
{
    public function getStudentsForAttendance(User $teacher, string $classId, ?string $periodId, string $date): ?array;

    public function saveAttendance(User $teacher, array $data): array;

    public function bulkUpdateAttendance(User $teacher, array $data): array;

    public function getAttendanceHistory(User $teacher, ?string $filter, ?string $dateFilter = null): array;

    public function getAttendanceDetail(User $teacher, string $recordId): ?array;
}
