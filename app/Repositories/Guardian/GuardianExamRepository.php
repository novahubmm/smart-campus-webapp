<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianExamRepositoryInterface;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\GradeSubject;
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
            ->with(['subject', 'teacher.user'])
            ->get();

        return $gradeSubjects->map(function ($gs) use ($student) {
            $subject = $gs->subject;
            
            // Get latest exam marks for this subject
            $latestMark = ExamMark::where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->orderBy('created_at', 'desc')
                ->first();

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon ?? 'book',
                'teacher' => $gs->teacher?->user?->name ?? 'N/A',
                'current_marks' => $latestMark?->marks_obtained ?? 0,
                'total_marks' => $latestMark?->exam?->total_marks ?? 100,
            ];
        })->toArray();
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
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();

        $schedule = $periods->map(function ($period) {
            return [
                'day' => ucfirst($period->day),
                'time' => $period->start_time . ' - ' . $period->end_time,
                'room' => $period->room?->name ?? 'TBA',
                'teacher' => $period->teacher?->user?->name ?? 'N/A',
            ];
        })->toArray();

        // Get upcoming classes (next 7 days)
        $upcomingClasses = [];
        $today = Carbon::now();
        $dayMap = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];

        foreach ($periods as $period) {
            $periodDayNum = $dayMap[strtolower($period->day)] ?? 0;
            $currentDayNum = $today->dayOfWeek;
            
            $daysUntil = $periodDayNum - $currentDayNum;
            if ($daysUntil <= 0) {
                $daysUntil += 7;
            }
            
            $classDate = $today->copy()->addDays($daysUntil);
            
            $upcomingClasses[] = [
                'date' => $classDate->format('Y-m-d'),
                'day' => ucfirst($period->day),
                'time' => $period->start_time . ' - ' . $period->end_time,
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
