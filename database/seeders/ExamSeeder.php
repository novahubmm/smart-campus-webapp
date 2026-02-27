<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Create exam types first
        $examTypes = [
            ['name' => 'Mid-Term'],
            ['name' => 'Final'],
            ['name' => 'Tutorial'],
            ['name' => 'Quiz'],
            ['name' => 'Practical'],
        ];

        foreach ($examTypes as $type) {
            ExamType::firstOrCreate(['name' => $type['name']]);
        }

        // Get or create a batch
        $batch = Batch::firstOrCreate(
            ['name' => '2024-2025'],
            [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'status' => true,
            ]
        );

        // Get or create grades
        $grades = [];
        for ($i = 9; $i <= 11; $i++) {
            $grades[] = Grade::firstOrCreate(
                ['level' => $i, 'batch_id' => $batch->id],
                ['price_per_month' => 50000]
            );
        }

        // Get exam types
        $midTerm = ExamType::where('name', 'Mid-Term')->first();
        $tutorial = ExamType::where('name', 'Tutorial')->first();
        $final = ExamType::where('name', 'Final')->first();

        // Create sample exams
        $exams = [
            [
                'name' => 'Mid-term Exam',
                'exam_type_id' => $midTerm->id,
                'batch_id' => $batch->id,
                'grade_id' => $grades[1]->id, // Grade 10
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(14),
                'description' => 'Mid-term examination for Grade 10',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Science Tutorial',
                'exam_type_id' => $tutorial->id,
                'batch_id' => $batch->id,
                'grade_id' => $grades[0]->id, // Grade 9
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(10),
                'description' => 'Science tutorial test for Grade 9',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Mathematics Tutorial 1',
                'exam_type_id' => $tutorial->id,
                'batch_id' => $batch->id,
                'grade_id' => $grades[0]->id, // Grade 9
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(5),
                'description' => 'Mathematics tutorial test for Grade 9-A',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Final Examination',
                'exam_type_id' => $final->id,
                'batch_id' => $batch->id,
                'grade_id' => $grades[2]->id, // Grade 11
                'start_date' => now()->addDays(20),
                'end_date' => now()->addDays(30),
                'description' => 'Final examination for Grade 11',
                'status' => 'upcoming',
            ],
            [
                'name' => 'Physics Practical',
                'exam_type_id' => ExamType::where('name', 'Practical')->first()->id,
                'batch_id' => $batch->id,
                'grade_id' => $grades[1]->id, // Grade 10
                'start_date' => now()->addDays(12),
                'end_date' => now()->addDays(12),
                'description' => 'Physics practical examination',
                'status' => 'upcoming',
            ],
        ];

        foreach ($exams as $exam) {
            Exam::firstOrCreate(
                ['name' => $exam['name'], 'batch_id' => $exam['batch_id']],
                $exam
            );
        }
    }
}
