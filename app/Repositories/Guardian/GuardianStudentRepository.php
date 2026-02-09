<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianStudentRepositoryInterface;
use App\Models\ExamMark;
use App\Models\GuardianNote;
use App\Models\StudentAttendance;
use App\Models\StudentGoal;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GuardianStudentRepository implements GuardianStudentRepositoryInterface
{
    public function getStudentProfile(StudentProfile $student): array
    {
        $student->load(['user', 'grade', 'classModel']);

        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'N/A',
            'student_id' => $student->student_identifier ?? $student->student_id,
            'grade' => $student->grade?->name ?? 'N/A',
            'section' => $student->classModel?->section ?? 'N/A',
            'roll_number' => $student->roll_number ?? null,
            'profile_image' => $student->photo_path ? asset($student->photo_path) : null,
            'date_of_birth' => $student->dob?->format('Y-m-d'),
            'blood_group' => $student->blood_type,
            'gender' => $student->gender,
            'address' => $student->address,
            'father_name' => $student->father_name,
            'mother_name' => $student->mother_name,
            'emergency_contact' => $student->emergency_contact_phone_no,
        ];
    }

    public function getAcademicSummary(StudentProfile $student): array
    {
        // Get latest exam results
        $examMarks = ExamMark::where('student_id', $student->id)
            ->with(['exam', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate GPA (simplified)
        $totalMarks = $examMarks->sum('marks_obtained');
        $totalPossible = $examMarks->sum(fn($m) => $m->exam?->total_marks ?? 100);
        $gpa = $totalPossible > 0 ? round(($totalMarks / $totalPossible) * 4, 2) : 0;

        // Get attendance percentage
        $yearStart = Carbon::now()->startOfYear();
        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->where('date', '>=', $yearStart)
            ->get();
        
        $totalDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
        $attendancePercentage = $totalDays > 0 ? round($presentDays / $totalDays * 100, 1) : 0;

        // Get subject-wise performance
        $subjects = $examMarks->groupBy('subject_id')->map(function ($marks) {
            $subject = $marks->first()->subject;
            $totalObtained = $marks->sum('marks_obtained');
            $totalPossible = $marks->sum(fn($m) => $m->exam?->total_marks ?? 100);
            $percentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
            
            return [
                'id' => $subject?->id,
                'name' => $subject?->name ?? 'N/A',
                'current_marks' => $totalObtained,
                'total_marks' => $totalPossible,
                'grade' => $this->calculateGrade($percentage),
                'rank' => null, // TODO: Calculate rank
            ];
        })->values()->toArray();

        return [
            'current_gpa' => $gpa,
            'current_rank' => null, // TODO: Calculate rank
            'total_students' => $student->classModel?->students_count ?? 0,
            'attendance_percentage' => $attendancePercentage,
            'subjects' => $subjects,
        ];
    }

    public function getRankings(StudentProfile $student): array
    {
        // TODO: Implement proper ranking calculation
        return [
            'overall_rank' => null,
            'class_rank' => null,
            'grade_rank' => null,
            'rank_history' => [],
            'subject_ranks' => [],
        ];
    }

    public function getAchievements(StudentProfile $student): array
    {
        // TODO: Implement achievements/badges system
        return [
            'badges' => [],
            'total_badges' => 0,
            'recent_achievements' => [],
        ];
    }

    public function getGoals(StudentProfile $student): Collection
    {
        return StudentGoal::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createGoal(StudentProfile $student, array $data): StudentGoal
    {
        return StudentGoal::create([
            'student_id' => $student->id,
            'guardian_id' => $data['guardian_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'target_value' => $data['target_value'] ?? null,
            'current_value' => $data['current_value'] ?? 0,
            'target_date' => $data['target_date'] ?? null,
            'status' => 'in_progress',
        ]);
    }

    public function updateGoal(string $goalId, array $data): StudentGoal
    {
        $goal = StudentGoal::findOrFail($goalId);
        $goal->update($data);
        return $goal->fresh();
    }

    public function deleteGoal(string $goalId): bool
    {
        return StudentGoal::where('id', $goalId)->delete() > 0;
    }

    public function getNotes(StudentProfile $student): Collection
    {
        return GuardianNote::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createNote(StudentProfile $student, string $guardianId, array $data): GuardianNote
    {
        return GuardianNote::create([
            'student_id' => $student->id,
            'guardian_id' => $guardianId,
            'title' => $data['title'],
            'content' => $data['content'],
            'category' => $data['category'] ?? 'general',
        ]);
    }

    public function updateNote(string $noteId, array $data): GuardianNote
    {
        $note = GuardianNote::findOrFail($noteId);
        $note->update($data);
        return $note->fresh();
    }

    public function deleteNote(string $noteId): bool
    {
        return GuardianNote::where('id', $noteId)->delete() > 0;
    }

    public function getGPATrends(StudentProfile $student, int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months);
        
        // Get exam marks grouped by month
        $examMarks = ExamMark::where('student_id', $student->id)
            ->whereHas('exam', function ($q) use ($startDate) {
                $q->where('start_date', '>=', $startDate);
            })
            ->with(['exam', 'subject'])
            ->get();

        // Group by month and calculate GPA
        $monthlyData = $examMarks->groupBy(function ($mark) {
            return $mark->exam?->start_date?->format('Y-m') ?? Carbon::now()->format('Y-m');
        })->map(function ($marks, $month) {
            $totalObtained = $marks->sum('marks_obtained');
            $totalPossible = $marks->sum(fn($m) => $m->exam?->total_marks ?? 100);
            $percentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
            $gpa = round($percentage / 25, 2); // Convert to 4.0 scale

            return [
                'month' => $month,
                'gpa' => $gpa,
                'percentage' => $percentage,
                'exams_count' => $marks->count(),
            ];
        })->sortKeys()->values()->toArray();

        $currentGPA = count($monthlyData) > 0 ? end($monthlyData)['gpa'] : 0;
        $averageGPA = count($monthlyData) > 0 ? round(collect($monthlyData)->avg('gpa'), 2) : 0;

        return [
            'current_gpa' => $currentGPA,
            'average_gpa' => $averageGPA,
            'trend_data' => $monthlyData,
            'period_months' => $months,
        ];
    }

    public function getPerformanceAnalysis(StudentProfile $student): array
    {
        $examMarks = ExamMark::where('student_id', $student->id)
            ->with(['exam', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Calculate overall statistics
        $totalMarks = $examMarks->sum('marks_obtained');
        $totalPossible = $examMarks->sum(fn($m) => $m->exam?->total_marks ?? 100);
        $overallPercentage = $totalPossible > 0 ? round($totalMarks / $totalPossible * 100, 1) : 0;

        // Subject-wise analysis
        $subjectAnalysis = $examMarks->groupBy('subject_id')->map(function ($marks) {
            $subject = $marks->first()->subject;
            $totalObtained = $marks->sum('marks_obtained');
            $totalPossible = $marks->sum(fn($m) => $m->exam?->total_marks ?? 100);
            $percentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
            
            return [
                'subject_id' => $subject?->id,
                'subject_name' => $subject?->name ?? 'N/A',
                'average_percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'exams_taken' => $marks->count(),
                'trend' => $this->calculateTrend($marks),
            ];
        })->values()->toArray();

        // Sort subjects by performance
        usort($subjectAnalysis, fn($a, $b) => $b['average_percentage'] <=> $a['average_percentage']);

        // Get strengths and weaknesses
        $strengths = array_slice($subjectAnalysis, 0, 3);
        $weaknesses = array_slice(array_reverse($subjectAnalysis), 0, 3);

        return [
            'overall_percentage' => $overallPercentage,
            'overall_grade' => $this->calculateGrade($overallPercentage),
            'total_exams' => $examMarks->count(),
            'subject_analysis' => $subjectAnalysis,
            'strengths' => $strengths,
            'weaknesses' => array_reverse($weaknesses),
        ];
    }

    public function getSubjectStrengthsWeaknesses(StudentProfile $student): array
    {
        $examMarks = ExamMark::where('student_id', $student->id)
            ->with(['exam', 'subject'])
            ->get();

        $subjectPerformance = $examMarks->groupBy('subject_id')->map(function ($marks) {
            $subject = $marks->first()->subject;
            $totalObtained = $marks->sum('marks_obtained');
            $totalPossible = $marks->sum(fn($m) => $m->exam?->total_marks ?? 100);
            $percentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
            
            return [
                'subject_id' => $subject?->id,
                'subject_name' => $subject?->name ?? 'N/A',
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
            ];
        })->sortByDesc('percentage')->values();

        $strengths = $subjectPerformance->take(3)->toArray();
        $weaknesses = $subjectPerformance->reverse()->take(3)->reverse()->values()->toArray();

        return [
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'total_subjects' => $subjectPerformance->count(),
        ];
    }

    public function getAcademicBadges(StudentProfile $student): array
    {
        $badges = [];

        // Get attendance data
        $yearStart = Carbon::now()->startOfYear();
        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->where('date', '>=', $yearStart)
            ->get();
        
        $totalDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
        $attendancePercentage = $totalDays > 0 ? round($presentDays / $totalDays * 100, 1) : 0;

        // Attendance badges
        if ($attendancePercentage >= 100) {
            $badges[] = [
                'id' => 'perfect_attendance',
                'name' => 'Perfect Attendance',
                'description' => '100% attendance record',
                'icon' => '🏆',
                'category' => 'attendance',
                'earned_date' => Carbon::now()->format('Y-m-d'),
            ];
        } elseif ($attendancePercentage >= 95) {
            $badges[] = [
                'id' => 'excellent_attendance',
                'name' => 'Excellent Attendance',
                'description' => '95%+ attendance record',
                'icon' => '⭐',
                'category' => 'attendance',
                'earned_date' => Carbon::now()->format('Y-m-d'),
            ];
        }

        // Academic performance badges
        $examMarks = ExamMark::where('student_id', $student->id)
            ->with('exam')
            ->get();

        $totalMarks = $examMarks->sum('marks_obtained');
        $totalPossible = $examMarks->sum(fn($m) => $m->exam?->total_marks ?? 100);
        $overallPercentage = $totalPossible > 0 ? round($totalMarks / $totalPossible * 100, 1) : 0;

        if ($overallPercentage >= 90) {
            $badges[] = [
                'id' => 'honor_roll',
                'name' => 'Honor Roll',
                'description' => 'Maintained 90%+ average',
                'icon' => '🎓',
                'category' => 'academic',
                'earned_date' => Carbon::now()->format('Y-m-d'),
            ];
        } elseif ($overallPercentage >= 80) {
            $badges[] = [
                'id' => 'high_achiever',
                'name' => 'High Achiever',
                'description' => 'Maintained 80%+ average',
                'icon' => '📚',
                'category' => 'academic',
                'earned_date' => Carbon::now()->format('Y-m-d'),
            ];
        }

        // Consistency badge
        $recentExams = $examMarks->sortByDesc('created_at')->take(5);
        if ($recentExams->count() >= 5) {
            $allPassing = $recentExams->every(function ($mark) {
                $totalMarks = $mark->exam?->total_marks ?? 100;
                $percentage = $totalMarks > 0 ? ($mark->marks_obtained / $totalMarks) * 100 : 0;
                return $percentage >= 60;
            });

            if ($allPassing) {
                $badges[] = [
                    'id' => 'consistent_performer',
                    'name' => 'Consistent Performer',
                    'description' => 'Passed last 5 exams',
                    'icon' => '💪',
                    'category' => 'consistency',
                    'earned_date' => Carbon::now()->format('Y-m-d'),
                ];
            }
        }

        return [
            'badges' => $badges,
            'total_badges' => count($badges),
            'categories' => array_unique(array_column($badges, 'category')),
        ];
    }

    private function calculateTrend(Collection $marks): string
    {
        if ($marks->count() < 2) {
            return 'stable';
        }

        $recent = $marks->take(3);
        $previous = $marks->slice(3, 3);

        if ($previous->isEmpty()) {
            return 'stable';
        }

        $recentAvg = $recent->avg('marks_obtained');
        $previousAvg = $previous->avg('marks_obtained');

        if ($recentAvg > $previousAvg * 1.05) {
            return 'improving';
        } elseif ($recentAvg < $previousAvg * 0.95) {
            return 'declining';
        }

        return 'stable';
    }

    private function calculateGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }
}
