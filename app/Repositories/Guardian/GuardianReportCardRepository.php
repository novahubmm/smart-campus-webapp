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
        $exams = Exam::whereHas('marks', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->get();

        $reportCards = $exams->map(function ($exam) use ($student) {
            $marks = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->with('subject')
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

            // Get class teacher
            $classTeacher = $student->classModel?->classTeacher;
            
            // Get subjects with details
            $subjects = $marks->map(function ($mark) use ($exam) {
                $totalMarks = $exam->total_marks ?? 100;
                $percentage = $totalMarks > 0 ? round($mark->marks_obtained / $totalMarks * 100, 1) : 0;
                $teacher = $mark->subject?->teachers()->first();

                return [
                    'subject_id' => $mark->subject_id,
                    'subject' => $mark->subject?->name ?? 'N/A',
                    'subject_mm' => $mark->subject?->name_mm ?? $mark->subject?->name ?? 'N/A',
                    'teacher_id' => $teacher?->id,
                    'teacher_name' => $teacher?->user?->name ?? 'N/A',
                    'teacher_name_mm' => $teacher?->user?->name ?? 'N/A',
                    'marks_obtained' => $mark->marks_obtained,
                    'total_marks' => $totalMarks,
                    'percentage' => $percentage,
                    'grade' => $this->calculateGrade($percentage),
                    'remarks' => $this->generateSubjectRemarks($percentage),
                    'remarks_mm' => $this->generateSubjectRemarksMM($percentage),
                ];
            })->toArray();

            return [
                'id' => $exam->id,
                'term' => $exam->name,
                'term_mm' => $this->translateTerm($exam->name),
                'academic_year' => $exam->academic_year ?? ($exam->end_date?->year ?? Carbon::now()->year),
                'exam_name' => $exam->name,
                'exam_name_mm' => $this->translateTerm($exam->name),
                'exam_date' => $exam->end_date?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
                'gpa' => $gpa,
                'overall_rank' => $rank,
                'class_rank' => $rank,
                'total_students' => $allStudentTotals->count(),
                'average_score' => $percentage,
                'total_score' => $totalObtained,
                'total_marks' => $totalPossible,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'class_teacher_id' => $classTeacher?->id,
                'class_teacher' => $classTeacher?->user?->name ?? 'N/A',
                'class_teacher_mm' => $classTeacher?->user?->name ?? 'N/A',
                'class_teacher_remark' => $this->generateRemarks($percentage),
                'class_teacher_remark_mm' => $this->generateRemarksMM($percentage),
                'generated_date' => $exam->end_date?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
                'status' => 'published',
                'pdf_url' => null,
                'subjects' => $subjects,
            ];
        })->values()->toArray();

        return [
            'report_cards' => $reportCards,
        ];
    }

    public function getLatestReportCard(StudentProfile $student): ?array
    {
        // Get the most recent exam that has results for this student
        $exam = Exam::whereHas('marks', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->first();

        if (!$exam) {
            return null;
        }

        return $this->getReportCardDetail($exam->id, $student);
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
            $teacher = $mark->subject?->teachers()->first();

            return [
                'subject_id' => $mark->subject_id,
                'subject' => $mark->subject?->name ?? 'N/A',
                'subject_mm' => $mark->subject?->name_mm ?? $mark->subject?->name ?? 'N/A',
                'teacher_id' => $teacher?->id,
                'teacher_name' => $teacher?->user?->name ?? 'N/A',
                'teacher_name_mm' => $teacher?->user?->name ?? 'N/A',
                'marks_obtained' => $mark->marks_obtained,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'grade' => $this->calculateGrade($percentage),
                'remarks' => $this->generateSubjectRemarks($percentage),
                'remarks_mm' => $this->generateSubjectRemarksMM($percentage),
            ];
        })->toArray();

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

        // Get class teacher
        $classTeacher = $student->classModel?->classTeacher;

        return [
            'id' => $exam->id,
            'term' => $exam->name,
            'term_mm' => $this->translateTerm($exam->name),
            'academic_year' => $exam->academic_year ?? ($exam->end_date?->year ?? Carbon::now()->year),
            'exam_name' => $exam->name,
            'exam_name_mm' => $this->translateTerm($exam->name),
            'exam_date' => $exam->end_date?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
            'gpa' => $overallGPA,
            'overall_rank' => $rank,
            'class_rank' => $rank,
            'total_students' => $allStudentTotals->count(),
            'average_score' => $overallPercentage,
            'total_score' => $totalObtained,
            'total_marks' => $totalPossible,
            'percentage' => $overallPercentage,
            'grade' => $this->calculateGrade($overallPercentage),
            'class_teacher_id' => $classTeacher?->id,
            'class_teacher' => $classTeacher?->user?->name ?? 'N/A',
            'class_teacher_mm' => $classTeacher?->user?->name ?? 'N/A',
            'class_teacher_remark' => $this->generateRemarks($overallPercentage),
            'class_teacher_remark_mm' => $this->generateRemarksMM($overallPercentage),
            'generated_date' => $exam->end_date?->format('Y-m-d') ?? Carbon::now()->format('Y-m-d'),
            'status' => 'published',
            'pdf_url' => null,
            'subjects' => $subjects,
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

    private function generateRemarksMM(float $percentage): string
    {
        if ($percentage >= 90) return 'အလွန်ကောင်းမွန်သော စွမ်းဆောင်ရည်! ဆက်လက်ကြိုးစားပါ။';
        if ($percentage >= 80) return 'ကောင်းမွန်သော စွမ်းဆောင်ရည်။ ဆက်လက်ကြိုးစားပါ။';
        if ($percentage >= 70) return 'ကောင်းသော စွမ်းဆောင်ရည်။ တိုးတက်ရန် နေရာရှိသေးသည်။';
        if ($percentage >= 60) return 'လက်ခံနိုင်သော စွမ်းဆောင်ရည်။ ပိုမိုကြိုးစားရန် လိုအပ်သည်။';
        if ($percentage >= 50) return 'ပျမ်းမျှ စွမ်းဆောင်ရည်။ ပိုမိုကြိုးစားရန် လိုအပ်သည်။';
        return 'ပျမ်းမျှအောက်။ သိသိသာသာ တိုးတက်ရန် လိုအပ်သည်။';
    }

    private function generateSubjectRemarks(float $percentage): string
    {
        if ($percentage >= 90) return 'Excellent understanding of the subject.';
        if ($percentage >= 80) return 'Very good grasp of concepts.';
        if ($percentage >= 70) return 'Good understanding. Keep practicing.';
        if ($percentage >= 60) return 'Satisfactory. More practice needed.';
        if ($percentage >= 50) return 'Average. Needs improvement.';
        return 'Needs significant improvement.';
    }

    private function generateSubjectRemarksMM(float $percentage): string
    {
        if ($percentage >= 90) return 'ဘာသာရပ်ကို အလွန်ကောင်းမွန်စွာ နားလည်သည်။';
        if ($percentage >= 80) return 'အယူအဆများကို အလွန်ကောင်းမွန်စွာ ဖမ်းယူထားသည်။';
        if ($percentage >= 70) return 'ကောင်းမွန်စွာ နားလည်သည်။ ဆက်လက်လေ့ကျင့်ပါ။';
        if ($percentage >= 60) return 'လက်ခံနိုင်သည်။ ပိုမိုလေ့ကျင့်ရန် လိုအပ်သည်။';
        if ($percentage >= 50) return 'ပျမ်းမျှ။ တိုးတက်ရန် လိုအပ်သည်။';
        return 'သိသိသာသာ တိုးတက်ရန် လိုအပ်သည်။';
    }

    private function translateTerm(string $term): string
    {
        $translations = [
            'Mid-Term' => 'အလယ်ပိုင်းစာမေးပွဲ',
            'Final' => 'နောက်ဆုံးစာမေးပွဲ',
            'First Term' => 'ပထမ သင်ကြားရေးကာလ',
            'Second Term' => 'ဒုတိယ သင်ကြားရေးကာလ',
            'Third Term' => 'တတိယ သင်ကြားရေးကာလ',
        ];

        foreach ($translations as $english => $myanmar) {
            if (stripos($term, $english) !== false) {
                return $myanmar;
            }
        }

        return $term;
    }
}
