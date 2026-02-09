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

    public function getSubjectCurriculum(string $subjectId, StudentProfile $student): array;

    // Enhanced methods for trends and analysis
    public function getPerformanceTrends(StudentProfile $student, ?string $subjectId = null): array;

    public function getUpcomingExams(StudentProfile $student): array;

    public function getPastExams(StudentProfile $student, int $limit = 10): array;

    public function getExamComparison(StudentProfile $student, array $examIds): array;
}
