<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding grades...');

        $grades = [
            ['level' => 0, 'price_per_month' => 50000],
            ['level' => 1, 'price_per_month' => 55000],
            ['level' => 2, 'price_per_month' => 60000],
            ['level' => 3, 'price_per_month' => 65000],
            ['level' => 4, 'price_per_month' => 70000],
            ['level' => 5, 'price_per_month' => 75000],
            ['level' => 6, 'price_per_month' => 80000],
            ['level' => 7, 'price_per_month' => 85000],
            ['level' => 8, 'price_per_month' => 90000],
            ['level' => 9, 'price_per_month' => 95000],
            ['level' => 10, 'price_per_month' => 100000],
            ['level' => 11, 'price_per_month' => 105000],
            ['level' => 12, 'price_per_month' => 110000],
        ];

        foreach ($grades as $gradeData) {
            $grade = Grade::updateOrCreate(
                ['level' => $gradeData['level']],
                ['price_per_month' => $gradeData['price_per_month']]
            );
            $this->command->info("✓ Updated: Grade {$grade->level} - {$gradeData['price_per_month']} MMK/month");
        }

        $this->command->newLine();
        $this->command->info('✓ Grade fees updated successfully!');
        $this->command->info('Total: ' . count($grades) . ' grades with monthly fees');
    }
}
