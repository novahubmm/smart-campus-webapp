<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\ExamMark;

class SubjectPerformanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Updates exam marks for student b0ae26d7-0cb6-42db-9e90-4a057d27c50b using existing exam data
     */
    public function run(): void
    {
        $this->command->info('🎯 Updating Subject Performance Data...');

        DB::beginTransaction();

        try {
            $studentId = 'b0ae26d7-0cb6-42db-9e90-4a057d27c50b';
            
            // Get the student with relationships
            $student = StudentProfile::with(['grade', 'classModel'])->find($studentId);
            
            if (!$student) {
                $this->command->error("❌ Student not found: {$studentId}");
                return;
            }

            if (!$student->grade || !$student->classModel) {
                $this->command->error("❌ Student missing grade or class relationship");
                return;
            }

            $this->command->info("✓ Found student: {$student->name}");
            $this->command->info("  Grade: {$student->grade->name}, Class: {$student->classModel->name}");

            // Get existing exam for this student's grade (ignore batch for now)
            $exam = Exam::where('grade_id', $student->grade_id)
                ->where('status', 'completed')
                ->latest('exam_date')
                ->first();

            if (!$exam) {
                $this->command->error("❌ No exam found for this student's grade");
                $this->command->info("Please run ReportCardDataSeeder first to create exam data");
                return;
            }

            $this->command->info("✓ Using existing exam: {$exam->name}");

            // Get subjects for the student's grade
            $subjects = Subject::whereHas('grades', function($q) use ($student) {
                $q->where('grade_id', $student->grade_id);
            })->get();

            if ($subjects->isEmpty()) {
                // Try to get all subjects if none are attached to grade
                $subjects = Subject::limit(6)->get();
                
                if ($subjects->isEmpty()) {
                    $this->command->error("❌ No subjects found");
                    $this->command->info("Please run ReportCardDataSeeder first to create subject data");
                    return;
                }
                
                $this->command->info("✓ Using first 6 subjects (not grade-specific)");
            } else {
                $this->command->info("✓ Found {$subjects->count()} subjects for grade");
            }

            // Define performance data matching the expected output
            $performanceData = [
                'Mathematics' => ['percentage' => 95, 'rank' => 1],
                'Science' => ['percentage' => 88, 'rank' => 3],
                'English' => ['percentage' => 82, 'rank' => 5],
                'Myanmar' => ['percentage' => 90, 'rank' => 2],
                'Social Studies' => ['percentage' => 85, 'rank' => 4],
            ];

            // Get total students in class
            $totalStudents = StudentProfile::where('class_id', $student->class_id)
                ->where('status', 'active')
                ->count();

            $this->command->info("✓ Total students in class: {$totalStudents}");

            $this->command->info("\nUpdating exam marks for test student...");
            
            // Get other students in the same class
            $otherStudents = StudentProfile::where('class_id', $student->class_id)
                ->where('id', '!=', $student->id)
                ->where('status', 'active')
                ->get();

            foreach ($subjects as $subject) {
                // Check if marks already exist
                $existingMark = ExamMark::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->first();

                // Get performance data or use default
                $performance = $performanceData[$subject->name] ?? [
                    'percentage' => rand(70, 95),
                    'rank' => rand(1, min(10, $totalStudents))
                ];

                $marksObtained = $performance['percentage'];
                $totalMarks = 100;

                if ($existingMark) {
                    // Update existing mark
                    $existingMark->update([
                        'marks_obtained' => $marksObtained,
                        'total_marks' => $totalMarks,
                        'grade' => $this->calculateGrade($performance['percentage']),
                    ]);
                } else {
                    // Create new mark
                    ExamMark::create([
                        'id' => (string) Str::uuid(),
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'marks_obtained' => $marksObtained,
                        'total_marks' => $totalMarks,
                        'grade' => $this->calculateGrade($performance['percentage']),
                    ]);
                }

                // Update marks for other students to establish proper ranking
                $targetRank = $performance['rank'];
                $studentsAbove = $targetRank - 1;

                foreach ($otherStudents as $index => $otherStudent) {
                    $existingOtherMark = ExamMark::where('exam_id', $exam->id)
                        ->where('student_id', $otherStudent->id)
                        ->where('subject_id', $subject->id)
                        ->first();

                    // Distribute marks to achieve desired rank
                    if ($index < $studentsAbove) {
                        // Students ranked above (higher marks)
                        $otherMarks = rand($marksObtained + 1, min(100, $marksObtained + 5));
                    } else {
                        // Students ranked below (lower marks)
                        $otherMarks = rand(max(40, $marksObtained - 20), $marksObtained - 1);
                    }

                    if ($existingOtherMark) {
                        $existingOtherMark->update([
                            'marks_obtained' => $otherMarks,
                            'total_marks' => $totalMarks,
                            'grade' => $this->calculateGrade(($otherMarks / $totalMarks) * 100),
                        ]);
                    } else {
                        ExamMark::create([
                            'id' => (string) Str::uuid(),
                            'exam_id' => $exam->id,
                            'student_id' => $otherStudent->id,
                            'subject_id' => $subject->id,
                            'marks_obtained' => $otherMarks,
                            'total_marks' => $totalMarks,
                            'grade' => $this->calculateGrade(($otherMarks / $totalMarks) * 100),
                        ]);
                    }
                }

                $grade = $this->calculateGrade($performance['percentage']);
                $this->command->info("  ✓ {$subject->name}: {$marksObtained}% - Grade {$grade} - Rank #{$targetRank}");
            }

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Subject Performance Data Updated Successfully!');
            $this->command->newLine();
            $this->command->info('📝 Test the API:');
            $this->command->info("   GET {{base_url}}/guardian/students/{$studentId}/profile/subject-performance");
            $this->command->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
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

    private function getRemarks(float $percentage): string
    {
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 80) return 'Very Good';
        if ($percentage >= 70) return 'Good';
        if ($percentage >= 60) return 'Satisfactory';
        if ($percentage >= 40) return 'Pass';
        return 'Needs Improvement';
    }
}
