<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianExamRepositoryInterface
{
    public function getExams(StudentProfile $student, ?string $subjectId = null): array;

    public function getExamDetail(string $examId): array;

    public function getExamResults(string $examId, StudentProfile $student): array;

    public function getSubjects(StudentProfile $student): array;

    public function getSubjectDetail(string $subjectId, StudentProfile $student): array;

    public function getSubjectPerformance(string $subjectId, StudentProfile $student): array;

    public function getSubjectSchedule(string $subjectId, StudentProfile $student): array;
}
