<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $examTypes = [
            [
                'name' => 'Tutorial (25 marks, 1 subject)',
            ],
            [
                'name' => 'Monthly (100 marks, 6 subjects)',
            ],
            [
                'name' => 'Semester (100 marks, 6 subjects)',
            ],
            [
                'name' => 'Final (100 marks, 6 subjects)',
            ],
        ];

        foreach ($examTypes as $examType) {
            ExamType::firstOrCreate(['name' => $examType['name']], $examType);
        }

        $this->command->info('Exam Types created successfully!');
    }
}
