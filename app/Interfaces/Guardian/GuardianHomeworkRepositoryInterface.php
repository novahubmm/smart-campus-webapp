<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianHomeworkRepositoryInterface
{
    public function getHomework(StudentProfile $student, ?string $status = null, ?string $subjectId = null): array;

    public function getHomeworkDetail(string $homeworkId, StudentProfile $student): array;

    public function getHomeworkStats(StudentProfile $student): array;

    public function updateHomeworkStatus(string $homeworkId, StudentProfile $student, string $status): bool;

    public function submitHomework(string $homeworkId, StudentProfile $student, ?string $notes, array $photos): array;
}
