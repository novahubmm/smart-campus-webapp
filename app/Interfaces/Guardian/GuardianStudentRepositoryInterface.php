<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianStudentRepositoryInterface
{
    public function getStudentProfile(StudentProfile $student): array;

    public function getAcademicSummary(StudentProfile $student): array;

    public function getRankings(StudentProfile $student): array;

    public function getAchievements(StudentProfile $student): array;

    public function getGoals(StudentProfile $student): \Illuminate\Database\Eloquent\Collection;

    public function createGoal(StudentProfile $student, array $data): \App\Models\StudentGoal;

    public function updateGoal(string $goalId, array $data): \App\Models\StudentGoal;

    public function deleteGoal(string $goalId): bool;

    public function getNotes(StudentProfile $student): \Illuminate\Database\Eloquent\Collection;

    public function createNote(StudentProfile $student, string $guardianId, array $data): \App\Models\GuardianNote;

    public function updateNote(string $noteId, array $data): \App\Models\GuardianNote;

    public function deleteNote(string $noteId): bool;

    // Enhanced methods for academic performance
    public function getGPATrends(StudentProfile $student, int $months = 12): array;

    public function getPerformanceAnalysis(StudentProfile $student): array;

    public function getSubjectStrengthsWeaknesses(StudentProfile $student): array;

    public function getAcademicBadges(StudentProfile $student): array;

    // Profile Screen specific methods
    public function getSubjectPerformance(StudentProfile $student): array;

    public function getProgressTracking(StudentProfile $student, int $months = 6): array;

    public function getComparisonData(StudentProfile $student): array;

    public function getAttendanceSummary(StudentProfile $student, int $months = 3): array;

    public function getRankingsData(StudentProfile $student): array;
}
