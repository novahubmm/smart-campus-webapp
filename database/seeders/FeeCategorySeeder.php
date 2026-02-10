<?php

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

class FeeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding fee categories...');

        $categories = [
            [
                'name' => 'Tuition Fee',
                'code' => 'TUITION',
                'description' => 'Monthly tuition fee for regular classes',
                'is_mandatory' => true,
                'status' => true,
            ],
            [
                'name' => 'Library Fee',
                'code' => 'LIBRARY',
                'description' => 'Access to library resources and books',
                'is_mandatory' => false,
                'status' => true,
            ],
            [
                'name' => 'Lab Fee',
                'code' => 'LAB',
                'description' => 'Science and computer lab usage fee',
                'is_mandatory' => false,
                'status' => true,
            ],
            [
                'name' => 'Sports Fee',
                'code' => 'SPORTS',
                'description' => 'Sports facilities and equipment fee',
                'is_mandatory' => false,
                'status' => true,
            ],
            [
                'name' => 'Transportation Fee',
                'code' => 'TRANSPORT',
                'description' => 'School bus transportation fee',
                'is_mandatory' => false,
                'status' => true,
            ],
            [
                'name' => 'Exam Fee',
                'code' => 'EXAM',
                'description' => 'Examination and assessment fee',
                'is_mandatory' => true,
                'status' => true,
            ],
            [
                'name' => 'Activity Fee',
                'code' => 'ACTIVITY',
                'description' => 'Extra-curricular activities fee',
                'is_mandatory' => false,
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            $feeType = FeeType::withTrashed()->where('code', $category['code'])->first();
            
            if ($feeType) {
                // Restore if soft deleted
                if ($feeType->trashed()) {
                    $feeType->restore();
                }
                // Update existing
                $feeType->update([
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_mandatory' => $category['is_mandatory'],
                    'status' => $category['status'],
                ]);
                $action = 'Updated';
            } else {
                // Create new
                $feeType = FeeType::create($category);
                $action = 'Created';
            }
            
            $mandatory = $category['is_mandatory'] ? '(Mandatory)' : '(Optional)';
            $this->command->info("✓ {$action}: {$feeType->name} {$mandatory}");
        }

        $this->command->newLine();
        $this->command->info('✓ Fee categories seeded successfully!');
        $this->command->info('Total: ' . count($categories) . ' fee categories');
    }
}
