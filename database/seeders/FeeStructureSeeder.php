<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class FeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding fee structures...');
        $this->command->newLine();

        // Get current batch
        $batch = Batch::where('name', '2025-2026')->first();
        if (!$batch) {
            $this->command->error('Batch 2025-2026 not found!');
            return;
        }

        // Get all grades
        $grades = Grade::orderBy('level')->get();
        if ($grades->isEmpty()) {
            $this->command->error('No grades found!');
            return;
        }

        // Get all fee types (including soft-deleted)
        $feeTypes = FeeType::withTrashed()->get()->keyBy('code');
        if ($feeTypes->isEmpty()) {
            $this->command->error('No fee types found!');
            return;
        }

        // Fee structure amounts by grade level and fee type
        // Format: [grade_level => [fee_code => amount]]
        $feeAmounts = [
            'Kindergarten' => [
                'TUITION' => 50000,
                'LIBRARY' => 3000,
                'LAB' => 0, // No lab for KG
                'SPORTS' => 5000,
                'TRANSPORT' => 15000,
                'EXAM' => 5000,
                'ACTIVITY' => 8000,
            ],
            'Grade 1' => [
                'TUITION' => 60000,
                'LIBRARY' => 4000,
                'LAB' => 3000,
                'SPORTS' => 6000,
                'TRANSPORT' => 15000,
                'EXAM' => 8000,
                'ACTIVITY' => 10000,
            ],
            'Grade 2' => [
                'TUITION' => 65000,
                'LIBRARY' => 4000,
                'LAB' => 3000,
                'SPORTS' => 6000,
                'TRANSPORT' => 15000,
                'EXAM' => 8000,
                'ACTIVITY' => 10000,
            ],
            'Grade 3' => [
                'TUITION' => 70000,
                'LIBRARY' => 5000,
                'LAB' => 4000,
                'SPORTS' => 7000,
                'TRANSPORT' => 18000,
                'EXAM' => 10000,
                'ACTIVITY' => 12000,
            ],
            'Grade 4' => [
                'TUITION' => 75000,
                'LIBRARY' => 5000,
                'LAB' => 4000,
                'SPORTS' => 7000,
                'TRANSPORT' => 18000,
                'EXAM' => 10000,
                'ACTIVITY' => 12000,
            ],
            'Grade 5' => [
                'TUITION' => 80000,
                'LIBRARY' => 6000,
                'LAB' => 5000,
                'SPORTS' => 8000,
                'TRANSPORT' => 20000,
                'EXAM' => 12000,
                'ACTIVITY' => 15000,
            ],
            'Grade 6' => [
                'TUITION' => 85000,
                'LIBRARY' => 6000,
                'LAB' => 5000,
                'SPORTS' => 8000,
                'TRANSPORT' => 20000,
                'EXAM' => 12000,
                'ACTIVITY' => 15000,
            ],
            'Grade 7' => [
                'TUITION' => 90000,
                'LIBRARY' => 7000,
                'LAB' => 6000,
                'SPORTS' => 9000,
                'TRANSPORT' => 22000,
                'EXAM' => 15000,
                'ACTIVITY' => 18000,
            ],
            'Grade 8' => [
                'TUITION' => 95000,
                'LIBRARY' => 7000,
                'LAB' => 6000,
                'SPORTS' => 9000,
                'TRANSPORT' => 22000,
                'EXAM' => 15000,
                'ACTIVITY' => 18000,
            ],
            'Grade 9' => [
                'TUITION' => 100000,
                'LIBRARY' => 8000,
                'LAB' => 8000,
                'SPORTS' => 10000,
                'TRANSPORT' => 25000,
                'EXAM' => 20000,
                'ACTIVITY' => 20000,
            ],
            'Grade 10' => [
                'TUITION' => 105000,
                'LIBRARY' => 8000,
                'LAB' => 8000,
                'SPORTS' => 10000,
                'TRANSPORT' => 25000,
                'EXAM' => 20000,
                'ACTIVITY' => 20000,
            ],
            'Grade 11' => [
                'TUITION' => 110000,
                'LIBRARY' => 10000,
                'LAB' => 10000,
                'SPORTS' => 12000,
                'TRANSPORT' => 28000,
                'EXAM' => 25000,
                'ACTIVITY' => 25000,
            ],
            'Grade 12' => [
                'TUITION' => 110000,
                'LIBRARY' => 10000,
                'LAB' => 10000,
                'SPORTS' => 12000,
                'TRANSPORT' => 28000,
                'EXAM' => 25000,
                'ACTIVITY' => 25000,
            ],
        ];

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($grades as $grade) {
            $gradeName = $grade->name;
            
            if (!isset($feeAmounts[$gradeName])) {
                $this->command->warn("⚠ No fee structure defined for {$gradeName}");
                continue;
            }

            $this->command->info("Processing {$gradeName}...");

            foreach ($feeAmounts[$gradeName] as $feeCode => $amount) {
                if (!isset($feeTypes[$feeCode])) {
                    $this->command->warn("  ⚠ Fee type {$feeCode} not found");
                    continue;
                }

                // Skip if amount is 0 (not applicable)
                if ($amount == 0) {
                    $this->command->line("  - Skipped {$feeCode} (not applicable)");
                    $totalSkipped++;
                    continue;
                }

                $feeType = $feeTypes[$feeCode];

                // Determine frequency based on fee type
                $frequency = match($feeCode) {
                    'TUITION', 'LIBRARY', 'LAB', 'SPORTS', 'TRANSPORT', 'ACTIVITY' => 'monthly',
                    'EXAM' => 'one-time',
                    default => 'monthly',
                };

                $feeStructure = FeeStructure::updateOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'batch_id' => $batch->id,
                        'fee_type_id' => $feeType->id,
                    ],
                    [
                        'amount' => $amount,
                        'frequency' => $frequency,
                        'applicable_from' => now()->startOfYear(),
                        'applicable_to' => now()->endOfYear(),
                        'status' => true,
                    ]
                );

                $this->command->line("  ✓ {$feeType->name}: " . number_format($amount) . " MMK ({$frequency})");
                $totalCreated++;
            }

            $this->command->newLine();
        }

        $this->command->info('✓ Fee structures seeded successfully!');
        $this->command->info("Total created/updated: {$totalCreated}");
        $this->command->info("Total skipped: {$totalSkipped}");
        $this->command->newLine();
        
        // Display summary
        $this->command->info('Summary by Grade:');
        foreach ($grades as $grade) {
            $gradeName = $grade->name;
            if (isset($feeAmounts[$gradeName])) {
                $total = array_sum($feeAmounts[$gradeName]);
                $this->command->line("  • {$gradeName}: " . number_format($total) . " MMK/month (total fees)");
            }
        }
    }
}
