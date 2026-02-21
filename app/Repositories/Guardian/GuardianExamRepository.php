<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianExamRepositoryInterface;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\GradeSubject;
use App\Models\Homework;
use App\Models\StudentProfile;
use App\Models\Subject;
use Carbon\Carbon;

class GuardianExamRepository implements GuardianExamRepositoryInterface
{
    public function getExams(StudentProfile $student, ?string $subjectId = null): array
    {
        $query = Exam::whereHas('examSchedules', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->with(['examType', 'examSchedules' => function ($q) use ($student) {
                $q->where('class_id', $student->class_id)->with('subject');
            }]);

        if ($subjectId) {
            $query->whereHas('examSchedules', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId);
            });
        }

        $exams = $query->orderBy('start_date', 'desc')->get();

        return $exams->map(function ($exam) use ($student) {
            $schedule = $exam->examSchedules->first();
            $result = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $schedule?->subject?->name ?? 'Multiple Subjects',
                'date' => $exam->start_date?->format('Y-m-d'),
                'start_time' => $schedule?->start_time,
                'end_time' => $schedule?->end_time,
                'total_marks' => $exam->total_marks ?? 100,
                'room' => $schedule?->room ?? 'TBA',
                'status' => $this->getExamStatus($exam),
                'has_result' => $result !== null,
            ];
        })->toArray();
    }

    public function getExamDetail(string $examId): array
    {
        $exam = Exam::with(['examType', 'examSchedules.subject', 'examSchedules.room'])
            ->findOrFail($examId);

        $schedule = $exam->examSchedules->first();

        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'subject' => $schedule?->subject ? [
                'id' => $schedule->subject->id,
                'name' => $schedule->subject->name,
                'icon' => $schedule->subject->icon ?? 'book',
            ] : null,
            'date' => $exam->start_date?->format('Y-m-d'),
            'start_time' => $schedule?->start_time,
            'end_time' => $schedule?->end_time,
            'total_marks' => $exam->total_marks ?? 100,
            'pass_marks' => $exam->pass_marks ?? 40,
            'room' => $schedule?->room?->name ?? 'TBA',
            'instructions' => $exam->instructions,
            'status' => $this->getExamStatus($exam),
        ];
    }

    public function getExamResults(string $examId, StudentProfile $student): array
    {
        $exam = Exam::findOrFail($examId);
        $result = ExamMark::where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->with('subject')
            ->first();

        if (!$result) {
            return [
                'exam_id' => $examId,
                'student_id' => $student->id,
                'status' => 'pending',
                'message' => 'Results not yet published',
            ];
        }

        // Calculate class statistics
        $classResults = ExamMark::where('exam_id', $examId)
            ->whereHas('student', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->get();

        $classAverage = $classResults->avg('marks_obtained');
        $highestMarks = $classResults->max('marks_obtained');
        $rank = $classResults->where('marks_obtained', '>', $result->marks_obtained)->count() + 1;

        $totalMarks = $exam->total_marks ?? 100;
        $percentage = $totalMarks > 0 ? round(($result->marks_obtained / $totalMarks) * 100, 1) : 0;

        return [
            'exam_id' => $examId,
            'student_id' => $student->id,
            'marks_obtained' => $result->marks_obtained,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'grade' => $this->calculateGrade($percentage),
            'rank' => $rank,
            'class_rank' => $rank,
            'class_average' => round($classAverage, 1),
            'highest_marks' => $highestMarks,
            'remarks' => $result->remarks,
        ];
    }

    public function getSubjects(StudentProfile $student): array
    {
        $gradeSubjects = GradeSubject::where('grade_id', $student->grade_id)
            ->with(['subject.subjectType', 'teacher.user'])
            ->get();

        $subjects = $gradeSubjects->map(function ($gs) use ($student) {
            $subject = $gs->subject;
            
            if (!$subject) {
                return null;
            }

            // Get curriculum progress
            $chapters = \App\Models\CurriculumChapter::where('subject_id', $subject->id)
                ->where(function ($query) use ($student) {
                    $query->where('grade_id', $student->grade_id)
                          ->orWhereNull('grade_id');
                })
                ->with(['topics.progress' => function ($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                }])
                ->get();

            $totalChapters = $chapters->count();
            $completedChapters = 0;

            foreach ($chapters as $chapter) {
                $chapterTotalTopics = $chapter->topics->count();
                $chapterCompletedTopics = $chapter->topics->filter(function ($topic) {
                    return $topic->progress->where('status', 'completed')->isNotEmpty();
                })->count();

                // Chapter is completed if all topics are completed
                if ($chapterTotalTopics > 0 && $chapterCompletedTopics === $chapterTotalTopics) {
                    $completedChapters++;
                }
            }

            $progressPercentage = $totalChapters > 0 
                ? round(($completedChapters / $totalChapters) * 100, 2)
                : 0;

            // Get weekly hours from timetable
            $weeklyHours = \App\Models\Timetable::where('class_id', $student->class_id)
                ->where('subject_id', $subject->id)
                ->count();

            // Determine image and colors based on subject type
            $subjectTypeName = $subject->subjectType?->name ?? 'Core';
            $isCore = strtolower($subjectTypeName) === 'core';
            
            $image = $isCore ? 'core_subject.svg' : 'elective_subject.svg';
            $iconColor = $isCore ? '#2196F3' : '#4CAF50'; // Blue for Core, Green for Elective
            $progressColor = $isCore ? '#2196F3' : '#4CAF50';

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'name_mm' => $subject->name_mm ?? $subject->name,
                'code' => $subject->code ?? strtoupper(substr($subject->name, 0, 3)) . '-' . ($student->grade?->name ?? '10'),
                'category' => $subjectTypeName,
                'teacher_id' => $gs->teacher?->id,
                'teacher_name' => $gs->teacher?->user?->name ?? 'N/A',
                'teacher_name_mm' => $gs->teacher?->user?->name_mm ?? $gs->teacher?->user?->name ?? 'N/A',
                'teacher_phone' => $gs->teacher?->user?->phone,
                'teacher_email' => $gs->teacher?->user?->email,
                'image' => url('images/subject_images/' . $image),
                'icon_color' => $iconColor,
                'progress_color' => $progressColor,
                'weekly_hours' => $weeklyHours,
                'total_chapters' => $totalChapters,
                'completed_chapters' => $completedChapters,
                'progress_percentage' => $progressPercentage,
            ];
        })->filter()->values();

        $totalWeeklyHours = $subjects->sum('weekly_hours');

        return [
            'subjects' => $subjects->toArray(),
            'total_subjects' => $subjects->count(),
            'total_weekly_hours' => $totalWeeklyHours,
        ];
    }

    public function getSubjectDetail(string $subjectId, StudentProfile $student): array
    {
        $subject = Subject::findOrFail($subjectId);
        
        $gradeSubject = GradeSubject::where('grade_id', $student->grade_id)
            ->where('subject_id', $subjectId)
            ->with('teacher.user')
            ->first();

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'icon' => $subject->icon ?? 'book',
            'description' => $subject->description,
            'teacher' => $gradeSubject?->teacher ? [
                'id' => $gradeSubject->teacher->id,
                'name' => $gradeSubject->teacher->user?->name ?? 'N/A',
                'phone' => $gradeSubject->teacher->user?->phone,
                'email' => $gradeSubject->teacher->user?->email,
            ] : null,
        ];
    }

    public function getSubjectPerformance(string $subjectId, StudentProfile $student): array
    {
        $marks = ExamMark::where('student_id', $student->id)
            ->where('subject_id', $subjectId)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        $history = $marks->map(function ($mark) {
            $totalMarks = $mark->exam?->total_marks ?? 100;
            $percentage = $totalMarks > 0 ? round(($mark->marks_obtained / $totalMarks) * 100, 1) : 0;

            return [
                'exam_id' => $mark->exam_id,
                'exam_name' => $mark->exam?->name ?? 'N/A',
                'marks_obtained' => $mark->marks_obtained,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'date' => $mark->created_at->format('Y-m-d'),
            ];
        })->toArray();

        $avgPercentage = count($history) > 0 
            ? round(collect($history)->avg('percentage'), 1) 
            : 0;

        return [
            'subject_id' => $subjectId,
            'average_percentage' => $avgPercentage,
            'average_grade' => $this->calculateGrade($avgPercentage),
            'total_exams' => count($history),
            'history' => $history,
        ];
    }

    public function getSubjectSchedule(string $subjectId, StudentProfile $student): array
    {
        $subject = Subject::findOrFail($subjectId);
        
        $gradeSubject = GradeSubject::where('grade_id', $student->grade_id)
            ->where('subject_id', $subjectId)
            ->with('teacher.user')
            ->first();

        // Get timetable periods for this subject
        $periods = \App\Models\Period::where('class_id', $student->class_id)
            ->where('subject_id', $subjectId)
            ->with(['room', 'teacher.user'])
            ->orderBy('starts_at')
            ->get();

        // Sort by day of week manually
        $dayOrder = ['monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7];
        $periods = $periods->sortBy(function ($period) use ($dayOrder) {
            return $dayOrder[strtolower($period->day_of_week)] ?? 8;
        });

        $schedule = $periods->map(function ($period) {
            return [
                'day' => ucfirst($period->day_of_week),
                'time' => substr($period->starts_at, 0, 5) . ' - ' . substr($period->ends_at, 0, 5),
                'room' => $period->room?->name ?? 'TBA',
                'teacher' => $period->teacher?->user?->name ?? 'N/A',
            ];
        })->values()->toArray();

        // Get upcoming classes (next 7 days)
        $upcomingClasses = [];
        $today = Carbon::now();
        $dayMap = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];

        foreach ($periods as $period) {
            $periodDayNum = $dayMap[strtolower($period->day_of_week)] ?? 0;
            $currentDayNum = $today->dayOfWeek;
            
            $daysUntil = $periodDayNum - $currentDayNum;
            if ($daysUntil <= 0) {
                $daysUntil += 7;
            }
            
            $classDate = $today->copy()->addDays($daysUntil);
            
            $upcomingClasses[] = [
                'date' => $classDate->format('Y-m-d'),
                'day' => ucfirst($period->day_of_week),
                'time' => substr($period->starts_at, 0, 5) . ' - ' . substr($period->ends_at, 0, 5),
                'room' => $period->room?->name ?? 'TBA',
            ];
        }

        // Sort by date
        usort($upcomingClasses, fn($a, $b) => strcmp($a['date'], $b['date']));

        return [
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'teacher' => $gradeSubject?->teacher?->user?->name ?? 'N/A',
            ],
            'schedule' => $schedule,
            'upcoming_classes' => array_slice($upcomingClasses, 0, 5),
        ];
    }

    public function getSubjectCurriculum(string $subjectId, StudentProfile $student): array
    {
        $subject = Subject::findOrFail($subjectId);
        
        $gradeSubject = GradeSubject::where('grade_id', $student->grade_id)
            ->where('subject_id', $subjectId)
            ->with('teacher.user')
            ->first();
        
        // Get curriculum chapters for this subject
        $chapters = \App\Models\CurriculumChapter::where('subject_id', $subjectId)
            ->where(function ($query) use ($student) {
                $query->where('grade_id', $student->grade_id)
                      ->orWhereNull('grade_id');
            })
            ->with(['topics' => function ($query) use ($student) {
                $query->with(['progress' => function ($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                }])->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        $totalTopics = 0;
        $completedTopics = 0;
        $completedChapters = 0;

        $curriculumData = $chapters->map(function ($chapter, $index) use ($student, &$totalTopics, &$completedTopics, &$completedChapters) {
            $topics = $chapter->topics->map(function ($topic) {
                $progress = $topic->progress->first();
                $status = $progress?->status ?? 'not_started';
                
                // Map status to match mobile app expectations
                if ($status === 'not_started' || !$progress) {
                    $status = 'upcoming';
                }
                
                return [
                    'id' => (string) $topic->id,
                    'name' => $topic->title,
                    'order' => $topic->order,
                    'status' => $status,
                    'duration' => '3 hours', // Default duration, can be added to database
                ];
            });

            $chapterTotalTopics = $topics->count();
            $chapterCompletedTopics = $topics->where('status', 'completed')->count();
            $chapterInProgressTopics = $topics->where('status', 'in_progress')->count();
            $chapterCurrentTopic = $topics->where('status', 'current')->first();
            
            $totalTopics += $chapterTotalTopics;
            $completedTopics += $chapterCompletedTopics;
            
            // Determine chapter status
            $chapterStatus = 'not_started';
            $currentTopicName = null;
            
            if ($chapterCompletedTopics === $chapterTotalTopics && $chapterTotalTopics > 0) {
                $chapterStatus = 'completed';
                $completedChapters++;
            } elseif ($chapterCompletedTopics > 0 || $chapterInProgressTopics > 0 || $chapterCurrentTopic) {
                $chapterStatus = 'in_progress';
                $currentTopicName = $chapterCurrentTopic['name'] ?? null;
            }
            
            $progressPercentage = $chapterTotalTopics > 0 
                ? round(($chapterCompletedTopics / $chapterTotalTopics) * 100, 1)
                : 0;

            // Get related items (exams and homework for this chapter)
            $relatedItems = [];
            
            // Get exams related to this subject
            $exams = ExamMark::where('student_id', $student->id)
                ->where('subject_id', $chapter->subject_id)
                ->with('exam')
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();
            
            foreach ($exams as $examMark) {
                if ($examMark->exam) {
                    $totalMarks = $examMark->exam->total_marks ?? 100;
                    $relatedItems[] = [
                        'type' => 'exam',
                        'id' => $examMark->exam->id,
                        'title' => $examMark->exam->name,
                        'score' => $examMark->marks_obtained . '/' . $totalMarks,
                        'date' => $examMark->created_at->format('Y-m-d'),
                    ];
                }
            }
            
            // Get homework related to this subject
            $homework = \App\Models\Homework::where('subject_id', $chapter->subject_id)
                ->where('class_id', $student->class_id)
                ->orderBy('due_date', 'desc')
                ->limit(2)
                ->get();
            
            foreach ($homework as $hw) {
                $relatedItems[] = [
                    'type' => 'homework',
                    'id' => $hw->id,
                    'title' => $hw->title,
                    'due_date' => $hw->due_date?->format('Y-m-d'),
                ];
            }

            $chapterData = [
                'id' => (string) $chapter->id,
                'number' => $chapter->order,
                'title' => $chapter->title,
                'description' => 'Chapter ' . $chapter->order . ' content',
                'total_topics' => $chapterTotalTopics,
                'completed_topics' => $chapterCompletedTopics,
                'progress_percentage' => $progressPercentage,
                'status' => $chapterStatus,
                'topics' => $topics->values()->toArray(),
                'related_items' => $relatedItems,
            ];
            
            if ($currentTopicName) {
                $chapterData['current_topic'] = $currentTopicName;
            }
            
            return $chapterData;
        });

        $totalChapters = $curriculumData->count();
        $overallProgress = $totalChapters > 0 
            ? round(($completedChapters / $totalChapters) * 100, 1)
            : 0;

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'icon' => $subject->icon ?? '📚',
            'teacher' => $gradeSubject?->teacher?->user?->name ?? 'N/A',
            'total_chapters' => $totalChapters,
            'completed_chapters' => $completedChapters,
            'progress_percentage' => $overallProgress,
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'chapters' => $curriculumData->values()->toArray(),
        ];
    }

    private function getExamStatus(Exam $exam): string
    {
        $now = Carbon::now();
        
        if ($exam->end_date && $now->gt($exam->end_date)) {
            return 'completed';
        }
        
        if ($exam->start_date && $now->lt($exam->start_date)) {
            return 'scheduled';
        }
        
        return 'ongoing';
    }

    public function getPerformanceTrends(StudentProfile $student, ?string $subjectId = null): array
    {
        $query = ExamMark::where('student_id', $student->id)
            ->with(['exam', 'subject']);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $marks = $query->orderBy('created_at', 'asc')->get();

        $trends = $marks->map(function ($mark) {
            $totalMarks = $mark->exam?->total_marks ?? 100;
            $percentage = $totalMarks > 0 ? round(($mark->marks_obtained / $totalMarks) * 100, 1) : 0;

            return [
                'exam_id' => $mark->exam_id,
                'exam_name' => $mark->exam?->name ?? 'N/A',
                'subject' => $mark->subject?->name ?? 'N/A',
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'date' => $mark->created_at->format('Y-m-d'),
            ];
        });

        // Calculate trend direction
        $recentAvg = $trends->take(-3)->avg('percentage');
        $previousAvg = $trends->slice(-6, 3)->avg('percentage');
        $trendDirection = $recentAvg > $previousAvg ? 'improving' : ($recentAvg < $previousAvg ? 'declining' : 'stable');

        return [
            'overall_average' => round($trends->avg('percentage'), 1),
            'recent_average' => round($recentAvg, 1),
            'trend_direction' => $trendDirection,
            'total_exams' => $trends->count(),
            'data' => $trends->toArray(),
        ];
    }

    public function getUpcomingExams(StudentProfile $student): array
    {
        $exams = Exam::whereHas('examSchedules', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->where('start_date', '>=', Carbon::now())
            ->with(['examType', 'examSchedules' => function ($q) use ($student) {
                $q->where('class_id', $student->class_id)->with('subject');
            }])
            ->orderBy('start_date', 'asc')
            ->get();

        return $exams->map(function ($exam) {
            $schedule = $exam->examSchedules->first();
            $daysUntil = Carbon::now()->diffInDays($exam->start_date, false);

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $schedule?->subject?->name ?? 'Multiple Subjects',
                'date' => $exam->start_date?->format('Y-m-d'),
                'start_time' => $schedule?->start_time,
                'end_time' => $schedule?->end_time,
                'total_marks' => $exam->total_marks ?? 100,
                'room' => $schedule?->room ?? 'TBA',
                'days_until' => max(0, $daysUntil),
                'is_today' => $daysUntil === 0,
                'is_tomorrow' => $daysUntil === 1,
            ];
        })->toArray();
    }

    public function getPastExams(StudentProfile $student, int $limit = 10): array
    {
        $exams = Exam::whereHas('examSchedules', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->where('end_date', '<', Carbon::now())
            ->with(['examType', 'examSchedules' => function ($q) use ($student) {
                $q->where('class_id', $student->class_id)->with('subject');
            }])
            ->orderBy('end_date', 'desc')
            ->limit($limit)
            ->get();

        return $exams->map(function ($exam) use ($student) {
            $schedule = $exam->examSchedules->first();
            $result = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            $data = [
                'id' => $exam->id,
                'name' => $exam->name,
                'subject' => $schedule?->subject?->name ?? 'Multiple Subjects',
                'date' => $exam->end_date?->format('Y-m-d'),
                'total_marks' => $exam->total_marks ?? 100,
                'has_result' => $result !== null,
            ];

            if ($result) {
                $totalMarks = $exam->total_marks ?? 100;
                $percentage = $totalMarks > 0 ? round(($result->marks_obtained / $totalMarks) * 100, 1) : 0;
                
                $data['marks_obtained'] = $result->marks_obtained;
                $data['percentage'] = $percentage;
                $data['grade'] = $this->calculateGrade($percentage);
            }

            return $data;
        })->toArray();
    }

    public function getExamComparison(StudentProfile $student, array $examIds): array
    {
        $exams = Exam::whereIn('id', $examIds)
            ->with(['examType', 'examSchedules.subject'])
            ->get();

        $comparison = [];

        foreach ($exams as $exam) {
            $result = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            $classResults = ExamMark::where('exam_id', $exam->id)
                ->whereHas('student', function ($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                })
                ->get();

            $totalMarks = $exam->total_marks ?? 100;
            $percentage = $result && $totalMarks > 0 
                ? round(($result->marks_obtained / $totalMarks) * 100, 1) 
                : 0;

            $comparison[] = [
                'exam_id' => $exam->id,
                'exam_name' => $exam->name,
                'date' => $exam->start_date?->format('Y-m-d'),
                'marks_obtained' => $result?->marks_obtained ?? 0,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'class_average' => round($classResults->avg('marks_obtained'), 1),
                'class_highest' => $classResults->max('marks_obtained'),
                'rank' => $result ? $classResults->where('marks_obtained', '>', $result->marks_obtained)->count() + 1 : null,
            ];
        }

        return [
            'student_id' => $student->id,
            'comparison' => $comparison,
            'average_percentage' => round(collect($comparison)->avg('percentage'), 1),
        ];
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
