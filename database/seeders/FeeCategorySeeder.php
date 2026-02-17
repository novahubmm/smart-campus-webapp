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
                'name' => 'Transportation Fee',
                'name_mm' => 'ယာဉ်စီးခ',
                'code' => 'TRANSPORT',
                'description' => 'Monthly school bus transportation fee for students',
                'is_mandatory' => true,  // Changed to true for YKST
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
