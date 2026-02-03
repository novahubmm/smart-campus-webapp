<?php

namespace App\Interfaces\Teacher;

use App\Models\User;

interface TeacherHomeworkRepositoryInterface
{
    public function getHomeworkList(User $teacher, ?string $classId, ?string $status): array;

    public function getHomeworkDetail(User $teacher, string $homeworkId): ?array;

    public function createHomework(User $teacher, array $data): array;

    public function collectHomework(User $teacher, string $homeworkId, string $studentId): ?array;
}
