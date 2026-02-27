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
                'name' => 'Tutorial',
            ],
            [
                'name' => 'Monthly',
            ],
            [
                'name' => 'Semester',
            ],
            [
                'name' => 'Final',
            ],
        ];

        foreach ($examTypes as $examType) {
            ExamType::firstOrCreate(['name' => $examType['name']], $examType);
        }

        $this->command->info('Exam Types created successfully!');
    }
}
