<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianReportCardRepositoryInterface;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use Carbon\Carbon;

class GuardianReportCardRepository implements GuardianReportCardRepositoryInterface
{
    public function getReportCards(StudentProfile $student): array
    {
        // Get exams that have results for this student
        $exams = Exam::whereHas('examMarks', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->get();

        return $exams->map(function ($exam) use ($student) {
            $marks = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->get();

            $totalObtained = $marks->sum('marks_obtained');
            $totalPossible = $marks->count() * ($exam->total_marks ?? 100);
            $percentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
            $gpa = $this->calculateGPA($percentage);

            // Calculate rank
            $allStudentTotals = ExamMark::where('exam_id', $exam->id)
                ->selectRaw('student_id, SUM(marks_obtained) as total')
                ->groupBy('student_id')
                ->orderByDesc('total')
                ->get();

            $rank = $allStudentTotals->search(function ($item) use ($student) {
                return $item->student_id === $student->id;
            });
            $rank = $rank !== false ? $rank + 1 : null;

            return [
                'id' => $exam->id,
                'term' => $exam->name,
                'year' => $exam->end_date?->year ?? Carbon::now()->year,
                'gpa' => $gpa,
                'rank' => $rank,
                'total_students' => $allStudentTotals->count(),
                'generated_date' => $exam->end_date?->format('Y-m-d'),
                'status' => 'published',
            ];
        })->toArray();
    }

    public function getReportCardDetail(string $reportCardId, StudentProfile $student): array
    {
        $exam = Exam::findOrFail($reportCardId);

        $marks = ExamMark::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with('subject')
            ->get();

        // Calculate subject-wise results
        $subjects = $marks->map(function ($mark) use ($exam) {
            $totalMarks = $exam->total_marks ?? 100;
            $percentage = $totalMarks > 0 ? round($mark->marks_obtained / $totalMarks * 100, 1) : 0;

            return [
                'name' => $mark->subject?->name ?? 'N/A',
                'marks' => $mark->marks_obtained,
                'total_marks' => $totalMarks,
                'grade' => $this->calculateGrade($percentage),
                'rank' => null, // TODO: Calculate subject rank
            ];
        });

        // Calculate overall stats
        $totalObtained = $marks->sum('marks_obtained');
        $totalPossible = $marks->count() * ($exam->total_marks ?? 100);
        $overallPercentage = $totalPossible > 0 ? round($totalObtained / $totalPossible * 100, 1) : 0;
        $overallGPA = $this->calculateGPA($overallPercentage);

        // Calculate rank
        $allStudentTotals = ExamMark::where('exam_id', $exam->id)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        $rank = $allStudentTotals->search(function ($item) use ($student) {
            return $item->student_id === $student->id;
        });
        $rank = $rank !== false ? $rank + 1 : null;

        // Get attendance percentage for the term
        $attendancePercentage = $this->getAttendancePercentage($student, $exam);

        return [
            'id' => $exam->id,
            'student' => [
                'name' => $student->user?->name ?? 'N/A',
                'student_id' => $student->student_identifier ?? $student->student_id,
                'grade' => $student->grade?->name ?? 'N/A',
                'section' => $student->classModel?->section ?? 'N/A',
            ],
            'term' => $exam->name,
            'year' => $exam->end_date?->year ?? Carbon::now()->year,
            'subjects' => $subjects->toArray(),
            'overall_gpa' => $overallGPA,
            'overall_rank' => $rank,
            'total_students' => $allStudentTotals->count(),
            'attendance_percentage' => $attendancePercentage,
            'remarks' => $this->generateRemarks($overallPercentage),
        ];
    }

    private function calculateGPA(float $percentage): float
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 80) return 3.7;
        if ($percentage >= 70) return 3.3;
        if ($percentage >= 60) return 3.0;
        if ($percentage >= 50) return 2.5;
        if ($percentage >= 40) return 2.0;
        return 0.0;
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

    private function getAttendancePercentage(StudentProfile $student, Exam $exam): float
    {
        $startDate = $exam->start_date ?? Carbon::now()->startOfYear();
        $endDate = $exam->end_date ?? Carbon::now();

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $total = $records->count();
        $present = $records->whereIn('status', ['present', 'late'])->count();

        return $total > 0 ? round($present / $total * 100, 1) : 0;
    }

    private function generateRemarks(float $percentage): string
    {
        if ($percentage >= 90) return 'Outstanding performance! Keep up the excellent work.';
        if ($percentage >= 80) return 'Excellent performance. Continue to strive for excellence.';
        if ($percentage >= 70) return 'Good performance. There is room for improvement.';
        if ($percentage >= 60) return 'Satisfactory performance. More effort is needed.';
        if ($percentage >= 50) return 'Average performance. Needs to work harder.';
        return 'Below average. Requires significant improvement.';
    }
}
