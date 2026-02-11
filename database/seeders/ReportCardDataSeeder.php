<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportCardDataSeeder extends Seeder
{
    private $batch;
    private $subjects = [];
    private $exams = [];

    /**
     * Run the database seeds.
     * Creates sample report card data for all grades
     */
    public function run(): void
    {
        $this->command->info('🎓 Creating Report Card Sample Data for All Grades');
        $this->command->newLine();

        DB::beginTransaction();

        try {
            $this->getBatch();
            $this->getOrCreateSubjects();
            $this->createExams();
            $this->createExamMarks();

            DB::commit();

            $this->command->newLine();
            $this->command->info('✅ Report Card Data Created Successfully!');
            $this->displaySummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    private function getBatch(): void
    {
        $this->batch = Batch::where('status', true)
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$this->batch) {
            $this->batch = Batch::create([
                'id' => (string) Str::uuid(),
                'name' => '2025-2026',
                'start_date' => '2025-06-01',
                'end_date' => '2026-03-31',
                'status' => true,
            ]);
            $this->command->info('✓ Created default batch: 2025-2026');
        } else {
            $this->command->info('✓ Using batch: ' . $this->batch->name);
        }
    }

    private function getOrCreateSubjects(): void
    {
        $subjectData = [
            ['name' => 'Myanmar', 'code' => 'MYA'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Social Studies', 'code' => 'SOC'],
            ['name' => 'Life Skills', 'code' => 'LIFE'],
        ];

        foreach ($subjectData as $data) {
            $subject = Subject::firstOrCreate(
                ['code' => $data['code']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $data['name'],
                ]
            );
            $this->subjects[] = $subject;
        }

        $this->command->info('✓ Subjects ready: ' . count($this->subjects));
    }

    private function createExams(): void
    {
        $this->command->info('Creating exams...');

        // Get exam types (using LIKE because names include descriptions)
        $monthlyType = \App\Models\ExamType::where('name', 'LIKE', 'Monthly%')->first();
        $semesterType = \App\Models\ExamType::where('name', 'LIKE', 'Semester%')->first();
        $finalType = \App\Models\ExamType::where('name', 'LIKE', 'Final%')->first();

        if (!$monthlyType || !$semesterType || !$finalType) {
            $this->command->error('Required exam types not found!');
            throw new \Exception('Please ensure Monthly, Semester, and Final exam types exist');
        }

        // Get all grades
        $grades = Grade::where('batch_id', $this->batch->id)->get();

        foreach ($grades as $grade) {
            $examData = [
                [
                    'exam_id' => "EXAM-2025-FT-G{$grade->level}",
                    'name' => "First Term Exam - Grade {$grade->level}",
                    'exam_type_id' => $semesterType->id,
                    'start_date' => Carbon::parse('2025-09-01'),
                    'end_date' => Carbon::parse('2025-09-15'),
                ],
                [
                    'exam_id' => "EXAM-2025-MT-G{$grade->level}",
                    'name' => "Mid-Term Exam - Grade {$grade->level}",
                    'exam_type_id' => $monthlyType->id,
                    'start_date' => Carbon::parse('2025-11-01'),
                    'end_date' => Carbon::parse('2025-11-15'),
                ],
                [
                    'exam_id' => "EXAM-2026-FN-G{$grade->level}",
                    'name' => "Final Exam - Grade {$grade->level}",
                    'exam_type_id' => $finalType->id,
                    'start_date' => Carbon::parse('2026-02-01'),
                    'end_date' => Carbon::parse('2026-02-15'),
                ],
            ];

            foreach ($examData as $data) {
                $exam = Exam::firstOrCreate(
                    [
                        'exam_id' => $data['exam_id'],
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $data['name'],
                        'exam_type_id' => $data['exam_type_id'],
                        'batch_id' => $this->batch->id,
                        'grade_id' => $grade->id,
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'status' => 'completed',
                    ]
                );
                $this->exams[] = $exam;
            }
        }

        $this->command->info("  ✓ Created " . count($this->exams) . " exams for " . $grades->count() . " grades");
    }

    private function createExamMarks(): void
    {
        $this->command->info('Creating exam marks for all students...');

        // Get all grades
        $grades = Grade::where('batch_id', $this->batch->id)->get();

        foreach ($grades as $grade) {
            $this->command->info("  Processing Grade {$grade->level}...");

            // Get all classes in this grade
            $classes = SchoolClass::where('grade_id', $grade->id)
                ->where('batch_id', $this->batch->id)
                ->get();

            foreach ($classes as $class) {
                // Get all students in this class
                $students = StudentProfile::where('class_id', $class->id)
                    ->where('grade_id', $grade->id)
                    ->get();

                if ($students->isEmpty()) {
                    continue;
                }

                $this->command->info("    Class {$class->name}: {$students->count()} students");

                // Create marks for each exam
                foreach ($this->exams as $exam) {
                    foreach ($students as $student) {
                        $this->createMarksForStudent($student, $exam);
                    }
                }
            }
        }

        $this->command->info('✓ Exam marks created for all students');
    }

    private function createMarksForStudent(StudentProfile $student, Exam $exam): void
    {
        // Determine student performance level (random but realistic)
        $performanceLevel = rand(1, 100);
        
        // Excellent: 90-100, Good: 75-89, Average: 60-74, Below Average: 40-59, Poor: 0-39
        if ($performanceLevel >= 85) {
            $baseRange = [85, 100]; // Excellent
        } elseif ($performanceLevel >= 70) {
            $baseRange = [70, 89]; // Good
        } elseif ($performanceLevel >= 50) {
            $baseRange = [55, 74]; // Average
        } elseif ($performanceLevel >= 30) {
            $baseRange = [40, 59]; // Below Average
        } else {
            $baseRange = [25, 44]; // Poor
        }

        foreach ($this->subjects as $subject) {
            // Check if mark already exists
            $existingMark = ExamMark::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->first();

            if ($existingMark) {
                continue;
            }

            // Add some variation per subject
            $variation = rand(-10, 10);
            $marksObtained = max(0, min(100, rand($baseRange[0], $baseRange[1]) + $variation));

            ExamMark::create([
                'id' => (string) Str::uuid(),
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'marks_obtained' => $marksObtained,
                'total_marks' => 100,
                'grade' => $this->calculateGrade($marksObtained),
                'remark' => $this->generateRemarks($marksObtained),
            ]);
        }
    }

    private function calculateGrade(float $marks): string
    {
        $percentage = $marks;
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }

    private function generateRemarks(float $marks): string
    {
        $percentage = $marks;
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 80) return 'Very Good';
        if ($percentage >= 70) return 'Good';
        if ($percentage >= 60) return 'Satisfactory';
        if ($percentage >= 50) return 'Average';
        return 'Needs Improvement';
    }

    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('📊 REPORT CARD DATA SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();

        $this->command->info('📚 SUBJECTS: ' . count($this->subjects));
        foreach ($this->subjects as $subject) {
            $this->command->info("   • {$subject->name} ({$subject->code})");
        }
        $this->command->newLine();

        $this->command->info('📝 EXAMS: ' . count($this->exams));
        foreach ($this->exams as $exam) {
            $this->command->info("   • {$exam->name} ({$exam->end_date->format('Y-m-d')})");
        }
        $this->command->newLine();

        $totalMarks = ExamMark::count();
        $totalStudents = StudentProfile::where('status', 'active')->count();
        
        $this->command->info('📈 STATISTICS:');
        $this->command->info("   Total Students: {$totalStudents}");
        $this->command->info("   Total Exam Marks: {$totalMarks}");
        $this->command->info("   Marks per Student: " . ($totalStudents > 0 ? round($totalMarks / $totalStudents) : 0));
        $this->command->newLine();

        $this->command->info('🧪 TESTING:');
        $this->command->info('   Test with any student ID:');
        $this->command->info('   GET /api/v1/guardian/students/{student_id}/report-cards');
        $this->command->newLine();

        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
