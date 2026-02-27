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
        $student->load(['user', 'grade.gradeCategory', 'classModel', 'guardians']);

        $class = $student->classModel;
        $gradeColor = $student->grade?->gradeCategory?->color ?? '#6B7280';

        // Check if student is a class leader
        $isClassLeader = false;
        if ($class) {
            $isClassLeader = $class->class_leader_id === $student->id 
                          || $class->male_class_leader_id === $student->id 
                          || $class->female_class_leader_id === $student->id;
        }

        // Get the primary guardian
        $primaryGuardian = $student->guardians()
            ->wherePivot('is_primary', true)
            ->with('user')
            ->first();

        // If no primary guardian, get the first guardian
        if (!$primaryGuardian) {
            $primaryGuardian = $student->guardians()->with('user')->first();
        }

        return [
            // Header Info
            'id' => $student->id,
            'name' => $student->user?->name ?? 'N/A',
            'roll_no' => $student->student_identifier ?? $student->student_id ?? '',
            'gender' => ucfirst($student->gender ?? 'N/A'),
            'avatar' => avatar_url($student->photo_path, 'student'),
            'class' => [
                'id' => $class?->id,
                'grade' => $student->grade?->level ?? '',
                'section' => $class?->name ?? 'N/A',
                'grade_color' => $gradeColor,
            ],
            'is_class_leader' => $isClassLeader ? 1 : 0,

            // Basic Information
            'student_id' => $student->student_identifier ?? $student->student_id ?? 'N/A',
            'date_of_joining' => $student->date_of_joining?->format('Y-m-d') ?? 'N/A',
            'roll_number' => $student->roll_number ?? null,

            // Personal Information
            'ethnicity' => $student->ethnicity ?? 'N/A',
            'religion' => $student->religious ?? 'N/A',
            'nrc' => $student->nrc ?? 'N/A',
            'date_of_birth' => $student->dob?->format('Y-m-d') ?? 'N/A',
            'blood_group' => $student->blood_type ?? 'N/A',

            // Academic Information
            'starting_grade' => $student->starting_grade_at_school ?? 'N/A',
            'current_grade' => $student->grade?->name ?? 'N/A',
            'current_class' => $class?->name ?? 'N/A',
            'previous_grade' => $student->previous_grade ?? 'N/A',
            'previous_class' => $student->previous_class ?? 'N/A',
            'previous_section' => 'N/A', // Not stored in current schema
            'guardian_teacher' => $student->guardian_teacher ?? 'N/A',
            'assistant_teacher' => $student->assistant_teacher ?? 'N/A',
            'previous_school' => $student->previous_school_name ?? 'N/A',
            'address' => $student->address ?? 'N/A',

            // Medical Information
            'weight' => $student->weight ?? 'N/A',
            'height' => $student->height ?? 'N/A',
            'blood_type' => $student->blood_type ?? 'N/A',
            'medicine_allergy' => $student->medicine_allergy ?? 'None',
            'food_allergy' => $student->food_allergy ?? 'None',
            'medical_history' => $student->medical_directory ?? 'None',

            // Guardian Information
            'guardian_name' => $primaryGuardian?->user?->name ?? 'N/A',
            'guardian_email' => $primaryGuardian?->user?->email ?? 'N/A',
            'guardian_phone' => $primaryGuardian?->user?->phone ?? 'N/A',
            'guardian_relationship' => $primaryGuardian?->pivot?->relationship ?? 'N/A',

            // Legacy parent fields (for backward compatibility)
            'father_name' => $student->father_name,
            'father_nrc' => $student->father_nrc,
            'father_religious' => $student->father_religious,
            'father_occupation' => $student->father_occupation,
            'father_address' => $student->father_address,
            'mother_name' => $student->mother_name,
            'mother_nrc' => $student->mother_nrc,
            'mother_religious' => $student->mother_religious,
            'mother_occupation' => $student->mother_occupation,
            'mother_address' => $student->mother_address,
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

    /**
     * Get Subject Performance for Profile Screen
     */
    public function getSubjectPerformance(StudentProfile $student): array
    {
        // Get all subjects for the student's class
        $subjects = \App\Models\Subject::whereHas('grades', function($q) use ($student) {
            $q->where('grade_id', $student->grade_id);
        })->get();

        $subjectData = [];
        
        foreach ($subjects as $subject) {
            // Get latest exam marks for this subject
            $latestMark = \App\Models\ExamMark::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->latest('created_at')
                ->first();

            if ($latestMark) {
                $percentage = ($latestMark->marks_obtained / $latestMark->total_marks) * 100;
                $grade = $this->calculateGrade($percentage);
                
                // Get rank in class for this subject
                $rank = \App\Models\ExamMark::where('subject_id', $subject->id)
                    ->where('exam_id', $latestMark->exam_id)
                    ->whereHas('student', function($q) use ($student) {
                        $q->where('class_id', $student->class_id);
                    })
                    ->selectRaw('student_id, (marks_obtained / total_marks * 100) as percentage')
                    ->orderByDesc('percentage')
                    ->get()
                    ->search(function($item) use ($student) {
                        return $item->student_id === $student->id;
                    }) + 1;

                $totalStudents = \App\Models\StudentProfile::where('class_id', $student->class_id)->count();

                $subjectData[] = [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'name_mm' => $subject->name, // Add Myanmar translation if available
                    'grade' => $grade,
                    'grade_color' => $this->getGradeColor($grade),
                    'percentage' => round($percentage, 1),
                    'rank' => $rank,
                    'total_students' => $totalStudents,
                ];
            }
        }

        return ['subjects' => $subjectData];
    }

    /**
     * Get Progress Tracking (GPA & Rank History)
     */
    public function getProgressTracking(StudentProfile $student, int $months = 6): array
    {
        $startDate = now()->subMonths($months);
        
        // Get exam history
        $exams = \App\Models\Exam::where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

        $gpaHistory = [];
        $rankHistory = [];
        $currentGpa = 0;
        $previousGpa = 0;
        $currentRank = 0;
        $previousRank = 0;

        foreach ($exams as $index => $exam) {
            $marks = \App\Models\ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->get();

            if ($marks->isNotEmpty()) {
                $totalPercentage = $marks->sum(function($mark) {
                    return ($mark->marks_obtained / $mark->total_marks) * 100;
                });
                $gpa = ($totalPercentage / $marks->count()) / 25; // Convert to 4.0 scale

                // Calculate rank
                $allStudentScores = \App\Models\ExamMark::where('exam_id', $exam->id)
                    ->whereHas('student', function($q) use ($student) {
                        $q->where('class_id', $student->class_id);
                    })
                    ->selectRaw('student_id, SUM(marks_obtained) as total_marks')
                    ->groupBy('student_id')
                    ->orderByDesc('total_marks')
                    ->get();

                $rank = $allStudentScores->search(function($item) use ($student) {
                    return $item->student_id === $student->id;
                }) + 1;

                $date = $exam->created_at->format('Y-m');
                $label = $exam->created_at->format('M Y');

                $gpaHistory[] = [
                    'date' => $date,
                    'value' => round($gpa, 2),
                    'label' => $label,
                ];

                $rankHistory[] = [
                    'date' => $date,
                    'value' => $rank,
                    'label' => $label,
                ];

                if ($index === count($exams) - 1) {
                    $currentGpa = round($gpa, 2);
                    $currentRank = $rank;
                } elseif ($index === count($exams) - 2) {
                    $previousGpa = round($gpa, 2);
                    $previousRank = $rank;
                }
            }
        }

        return [
            'gpa_history' => $gpaHistory,
            'rank_history' => $rankHistory,
            'current_gpa' => $currentGpa,
            'previous_gpa' => $previousGpa,
            'current_rank' => $currentRank,
            'previous_rank' => $previousRank,
        ];
    }

    /**
     * Get Comparison Data (Student vs Class Average)
     */
    public function getComparisonData(StudentProfile $student): array
    {
        // Get latest exam
        $latestExam = \App\Models\Exam::latest()->first();
        
        if (!$latestExam) {
            return [
                'gpa_comparison' => ['student_value' => 0, 'class_average' => 0, 'label' => 'GPA'],
                'avg_score_comparison' => ['student_value' => 0, 'class_average' => 0, 'label' => 'Average Score'],
                'subject_comparisons' => [],
            ];
        }

        // Get student marks
        $studentMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
            ->where('student_id', $student->id)
            ->get();

        // Calculate student GPA and average
        $studentTotalPercentage = $studentMarks->sum(function($mark) {
            return ($mark->marks_obtained / $mark->total_marks) * 100;
        });
        $studentGpa = $studentMarks->count() > 0 ? ($studentTotalPercentage / $studentMarks->count()) / 25 : 0;
        $studentAvgScore = $studentMarks->count() > 0 ? $studentTotalPercentage / $studentMarks->count() : 0;

        // Get class average
        $classMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
            ->whereHas('student', function($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->get();

        $classTotalPercentage = $classMarks->sum(function($mark) {
            return ($mark->marks_obtained / $mark->total_marks) * 100;
        });
        $classGpa = $classMarks->count() > 0 ? ($classTotalPercentage / $classMarks->count()) / 25 : 0;
        $classAvgScore = $classMarks->count() > 0 ? $classTotalPercentage / $classMarks->count() : 0;

        // Subject comparisons
        $subjectComparisons = [];
        foreach ($studentMarks as $mark) {
            $subject = $mark->subject;
            $studentScore = ($mark->marks_obtained / $mark->total_marks) * 100;

            // Get class average for this subject
            $subjectClassMarks = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                ->where('subject_id', $mark->subject_id)
                ->whereHas('student', function($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                })
                ->get();

            $subjectClassAvg = $subjectClassMarks->avg(function($m) {
                return ($m->marks_obtained / $m->total_marks) * 100;
            });

            $indicator = 'neutral';
            if ($studentScore > $subjectClassAvg + 2) {
                $indicator = 'positive';
            } elseif ($studentScore < $subjectClassAvg - 2) {
                $indicator = 'needs_improvement';
            }

            $subjectComparisons[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_name_mm' => $subject->name,
                'student_score' => round($studentScore, 1),
                'class_average' => round($subjectClassAvg, 1),
                'indicator' => $indicator,
            ];
        }

        return [
            'gpa_comparison' => [
                'student_value' => round($studentGpa, 2),
                'class_average' => round($classGpa, 2),
                'label' => 'GPA',
            ],
            'avg_score_comparison' => [
                'student_value' => round($studentAvgScore, 1),
                'class_average' => round($classAvgScore, 1),
                'label' => 'Average Score',
            ],
            'subject_comparisons' => $subjectComparisons,
        ];
    }

    /**
     * Get Attendance Summary for Profile Screen
     */
    public function getAttendanceSummary(StudentProfile $student, int $months = 3): array
    {
        $startDate = now()->subMonths($months)->startOfMonth();
        
        $attendanceRecords = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->where('date', '>=', $startDate)
            ->get();

        $totalDays = $attendanceRecords->count();
        $totalPresent = $attendanceRecords->where('status', 'present')->count();
        $overallPercentage = $totalDays > 0 ? ($totalPresent / $totalDays) * 100 : 0;

        // Monthly breakdown
        $monthlyBreakdown = [];
        for ($i = 0; $i < $months; $i++) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $monthRecords = $attendanceRecords->whereBetween('date', [$monthStart, $monthEnd]);
            $monthTotal = $monthRecords->count();
            $monthPresent = $monthRecords->where('status', 'present')->count();
            $monthPercentage = $monthTotal > 0 ? ($monthPresent / $monthTotal) * 100 : 0;

            $monthlyBreakdown[] = [
                'month' => $monthStart->format('M'),
                'month_mm' => $this->getMonthNameMM($monthStart->format('m')),
                'year' => $monthStart->year,
                'percentage' => round($monthPercentage, 1),
                'present' => $monthPresent,
                'total' => $monthTotal,
            ];
        }

        return [
            'overall_percentage' => round($overallPercentage, 1),
            'total_present' => $totalPresent,
            'total_days' => $totalDays,
            'monthly_breakdown' => array_reverse($monthlyBreakdown),
        ];
    }

    /**
     * Get Rankings & Exam History for Profile Screen
     */
    public function getRankingsData(StudentProfile $student): array
    {
        // Get current rankings
        $latestExam = \App\Models\Exam::latest()->first();
        
        $currentClassRank = 0;
        $currentGradeRank = 0;
        $totalStudentsInClass = \App\Models\StudentProfile::where('class_id', $student->class_id)->count();
        $totalStudentsInGrade = \App\Models\StudentProfile::where('grade_id', $student->grade_id)->count();

        if ($latestExam) {
            // Calculate class rank
            $classRankings = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                ->whereHas('student', function($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                })
                ->selectRaw('student_id, SUM(marks_obtained) as total_marks')
                ->groupBy('student_id')
                ->orderByDesc('total_marks')
                ->get();

            $currentClassRank = $classRankings->search(function($item) use ($student) {
                return $item->student_id === $student->id;
            }) + 1;

            // Calculate grade rank
            $gradeRankings = \App\Models\ExamMark::where('exam_id', $latestExam->id)
                ->whereHas('student', function($q) use ($student) {
                    $q->where('grade_id', $student->grade_id);
                })
                ->selectRaw('student_id, SUM(marks_obtained) as total_marks')
                ->groupBy('student_id')
                ->orderByDesc('total_marks')
                ->get();

            $currentGradeRank = $gradeRankings->search(function($item) use ($student) {
                return $item->student_id === $student->id;
            }) + 1;
        }

        // Get exam history
        $exams = \App\Models\Exam::latest()->take(5)->get();
        $examHistory = [];

        foreach ($exams as $exam) {
            $marks = \App\Models\ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->get();

            if ($marks->isNotEmpty()) {
                $totalScore = $marks->sum('marks_obtained');
                $maxScore = $marks->sum('total_marks');
                $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

                // Calculate ranks for this exam
                $classRankings = \App\Models\ExamMark::where('exam_id', $exam->id)
                    ->whereHas('student', function($q) use ($student) {
                        $q->where('class_id', $student->class_id);
                    })
                    ->selectRaw('student_id, SUM(marks_obtained) as total_marks')
                    ->groupBy('student_id')
                    ->orderByDesc('total_marks')
                    ->get();

                $classRank = $classRankings->search(function($item) use ($student) {
                    return $item->student_id === $student->id;
                }) + 1;

                $gradeRankings = \App\Models\ExamMark::where('exam_id', $exam->id)
                    ->whereHas('student', function($q) use ($student) {
                        $q->where('grade_id', $student->grade_id);
                    })
                    ->selectRaw('student_id, SUM(marks_obtained) as total_marks')
                    ->groupBy('student_id')
                    ->orderByDesc('total_marks')
                    ->get();

                $gradeRank = $gradeRankings->search(function($item) use ($student) {
                    return $item->student_id === $student->id;
                }) + 1;

                $examHistory[] = [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'name_mm' => $exam->name,
                    'date' => $exam->exam_date ? $exam->exam_date->format('Y-m-d') : $exam->created_at->format('Y-m-d'),
                    'total_score' => $totalScore,
                    'max_score' => $maxScore,
                    'percentage' => round($percentage, 1),
                    'class_rank' => $classRank,
                    'grade_rank' => $gradeRank,
                ];
            }
        }

        return [
            'current_class_rank' => $currentClassRank,
            'total_students_in_class' => $totalStudentsInClass,
            'current_grade_rank' => $currentGradeRank,
            'total_students_in_grade' => $totalStudentsInGrade,
            'exam_history' => $examHistory,
        ];
    }

    private function getGradeColor(string $grade): string
    {
        $colors = [
            'A+' => '#26A69A',
            'A' => '#26A69A',
            'B+' => '#7E57C2',
            'B' => '#7E57C2',
            'C' => '#FFA726',
            'D' => '#EF5350',
            'F' => '#EF5350',
        ];

        return $colors[$grade] ?? '#9E9E9E';
    }

    private function getMonthNameMM(string $month): string
    {
        $months = [
            '01' => 'ဇန်နဝါရီ',
            '02' => 'ဖေဖော်ဝါရီ',
            '03' => 'မတ်',
            '04' => 'ဧပြီ',
            '05' => 'မေ',
            '06' => 'ဇွန်',
            '07' => 'ဇူလိုင်',
            '08' => 'သြဂုတ်',
            '09' => 'စက်တင်ဘာ',
            '10' => 'အောက်တိုဘာ',
            '11' => 'နိုဝင်ဘာ',
            '12' => 'ဒီဇင်ဘာ',
        ];

        return $months[$month] ?? '';
    }

    public function getFullProfile(StudentProfile $student): array
    {
        // Load all necessary relationships
        $student->load(['user', 'grade', 'classModel', 'guardians']);

        // Check if student is a class leader
        $isClassLeader = false;
        if ($student->classModel) {
            $isClassLeader = $student->classModel->class_leader_id === $student->id 
                          || $student->classModel->male_class_leader_id === $student->id 
                          || $student->classModel->female_class_leader_id === $student->id;
        }

        // Get the primary guardian
        $primaryGuardian = $student->guardians()
            ->wherePivot('is_primary', true)
            ->with('user')
            ->first();

        // If no primary guardian, get the first guardian
        if (!$primaryGuardian) {
            $primaryGuardian = $student->guardians()->with('user')->first();
        }

        return [
            'basic_information' => [
                'name' => $student->user?->name ?? 'N/A',
                'student_id' => $student->student_identifier ?? $student->student_id ?? 'N/A',
                'date_of_joining' => $student->date_of_joining?->format('Y-m-d') ?? 'N/A',
                'is_class_leader' => $isClassLeader ? 1 : 0,
            ],
            'personal_information' => [
                'gender' => $student->gender ?? 'N/A',
                'ethnicity' => $student->ethnicity ?? 'N/A',
                'religion' => $student->religious ?? 'N/A',
                'nrc' => $student->nrc ?? 'N/A',
                'date_of_birth' => $student->dob?->format('Y-m-d') ?? 'N/A',
            ],
            'academic_information' => [
                'starting_grade' => $student->starting_grade_at_school ?? 'N/A',
                'current_grade' => $student->grade?->name ?? 'N/A',
                'current_class' => $student->classModel?->name ?? $student->classModel?->section ?? 'N/A',
                'previous_grade' => 'N/A', // Not stored in current schema
                'previous_class' => 'N/A', // Not stored in current schema
                'guardian_teacher' => $student->guardian_teacher ?? 'N/A',
                'assistant_teacher' => $student->assistant_teacher ?? 'N/A',
                'previous_school' => $student->previous_school_name ?? 'N/A',
                'address' => $student->address ?? 'N/A',
            ],
            'medical' => [
                'weight' => $student->weight ?? 'N/A',
                'height' => $student->height ?? 'N/A',
                'blood_type' => $student->blood_type ?? 'N/A',
                'medicine_allergy' => $student->medicine_allergy ?? 'None',
                'food_allergy' => $student->food_allergy ?? 'None',
                'medical_history' => $student->medical_directory ?? 'None',
            ],
            'guardian_information' => [
                'name' => $primaryGuardian?->user?->name ?? 'N/A',
                'email' => $primaryGuardian?->user?->email ?? 'N/A',
                'phone' => $primaryGuardian?->user?->phone ?? 'N/A',
                'relationship' => $primaryGuardian?->pivot?->relationship ?? 'N/A',
            ],
        ];
    }
}

