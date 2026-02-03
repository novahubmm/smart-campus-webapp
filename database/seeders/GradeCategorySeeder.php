<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeCategory;

class GradeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Primary', 'color' => '#3b82f6'],
            ['name' => 'Middle School', 'color' => '#f59e0b'],
            ['name' => 'High School', 'color' => '#ef4444'],
        ];

        foreach ($categories as $category) {
            GradeCategory::updateOrCreate(
                ['name' => $category['name']],
                ['color' => $category['color']]
            );
        }

        $this->command->info('Grade Categories created successfully!');
    }
}
