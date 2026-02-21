<?php

namespace Database\Seeders;

use App\Models\FeeStructure as FinanceFeeStructure;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Batch;
use App\Models\PaymentSystem\FeeStructure as PaymentSystemFeeStructure;
use Illuminate\Database\Seeder;

class PaymentSystemFeeStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Converts Finance fee structures to PaymentSystem fee structures.
     * Also includes School Fee from grade.price_per_month
     */
    public function run(): void
    {
        $this->command->info('Seeding PaymentSystem fee structures...');

        $currentBatch = Batch::where('status', true)->first();
        
        if (!$currentBatch) {
            $this->command->warn('No active batch found.');
            return;
        }

        $created = 0;
        $updated = 0;

        // Get all grades
        $grades = Grade::where('batch_id', $currentBatch->id)->get();

        foreach ($grades as $grade) {
            // 1. Add School Fee from grade.price_per_month
            if ($grade->price_per_month && $grade->price_per_month > 0) {
                $schoolFeeData = [
                    'name' => 'School Fee',
                    'name_mm' => 'ကျောင်းလခ',
                    'description' => 'Monthly school tuition fee',
                    'description_mm' => 'လစဉ် ကျောင်းလခ',
                    'amount' => $grade->price_per_month,
                    'frequency' => 'monthly',
                    'fee_type' => 'SCHOOL_FEE',
                    'grade' => (string) $grade->level,
                    'batch' => $currentBatch->name ?? now()->format('Y'),
                    'target_month' => null,
                    'due_date' => now()->endOfMonth(),
                    'supports_payment_period' => true,
                    'is_active' => true,
                ];

                $paymentSystemStructure = PaymentSystemFeeStructure::updateOrCreate(
                    [
                        'fee_type' => 'SCHOOL_FEE',
                        'grade' => (string) $grade->level,
                        'frequency' => 'monthly',
                        'batch' => $currentBatch->name ?? now()->format('Y'),
                    ],
                    $schoolFeeData
                );

                if ($paymentSystemStructure->wasRecentlyCreated) {
                    $created++;
                    $this->command->info("✓ Created: Grade {$grade->level} - School Fee ({$grade->price_per_month} MMK) - Due: {$schoolFeeData['due_date']->format('Y-m-d')}");
                } else {
                    $updated++;
                    $this->command->info("✓ Updated: Grade {$grade->level} - School Fee ({$grade->price_per_month} MMK) - Due: {$schoolFeeData['due_date']->format('Y-m-d')}");
                }
            }

            // 2. Add other fee categories from Finance fee structures
            $financeFeeStructures = FinanceFeeStructure::with(['feeType'])
                ->where('grade_id', $grade->id)
                ->get();

            foreach ($financeFeeStructures as $financeStructure) {
                $feeType = $financeStructure->feeType;
                
                if (!$feeType) {
                    $this->command->warn("⚠ Skipping fee structure {$financeStructure->id} - no fee type found");
                    continue;
                }
                
                // Calculate due date based on fee type's due_date_type
                $dueDate = $this->calculateDueDate($feeType, $financeStructure);
                
                // Determine frequency from fee structure or default to one_time
                $frequency = $financeStructure->frequency ?? 'one_time';
                
                // Determine if supports payment period
                $supportsPaymentPeriod = $financeStructure->supports_payment_period ?? ($frequency === 'monthly');

                $data = [
                    'name' => $feeType->name,
                    'name_mm' => $feeType->name_mm,
                    'description' => $feeType->description,
                    'description_mm' => $feeType->description_mm,
                    'amount' => $financeStructure->amount,
                    'frequency' => $frequency,
                    'fee_type' => $feeType->code ?? strtoupper(str_replace(' ', '_', $feeType->name)),
                    'grade' => (string) $grade->level,
                    'batch' => $currentBatch->name ?? now()->format('Y'),
                    'target_month' => null,
                    'due_date' => $dueDate,
                    'supports_payment_period' => $supportsPaymentPeriod,
                    'is_active' => $financeStructure->status ?? true,
                ];

                $paymentSystemStructure = PaymentSystemFeeStructure::updateOrCreate(
                    [
                        'fee_type' => $data['fee_type'],
                        'grade' => $data['grade'],
                        'frequency' => $data['frequency'],
                        'batch' => $currentBatch->name ?? now()->format('Y'),
                    ],
                    $data
                );

                if ($paymentSystemStructure->wasRecentlyCreated) {
                    $created++;
                    $this->command->info("✓ Created: Grade {$data['grade']} - {$data['name']} ({$data['amount']} MMK) - {$data['frequency']} - Due: {$dueDate->format('Y-m-d')} - Active: " . ($data['is_active'] ? 'Yes' : 'No'));
                } else {
                    $updated++;
                    $this->command->info("✓ Updated: Grade {$data['grade']} - {$data['name']} ({$data['amount']} MMK) - {$data['frequency']} - Due: {$dueDate->format('Y-m-d')} - Active: " . ($data['is_active'] ? 'Yes' : 'No'));
                }
            }
        }

        $this->command->newLine();
        $this->command->info('✓ PaymentSystem fee structures seeded successfully!');
        $this->command->info("Created: {$created}, Updated: {$updated}");
    }

    /**
     * Calculate due date based on fee type's due_date_type and due_date
     */
    private function calculateDueDate(?FeeType $feeType, ?FinanceFeeStructure $feeStructure = null): \Carbon\Carbon
    {
        // If fee structure has applicable_from, use that as base
        $baseDate = $feeStructure && $feeStructure->applicable_from 
            ? \Carbon\Carbon::parse($feeStructure->applicable_from)
            : now();

        // If no fee type, default to end of month
        if (!$feeType) {
            return $baseDate->copy()->endOfMonth();
        }

        // If fee type has due_date (number of days), add to base date
        if ($feeType->due_date && is_numeric($feeType->due_date)) {
            return $baseDate->copy()->addDays($feeType->due_date);
        }

        // Otherwise use due_date_type
        if ($feeType->due_date_type) {
            return match($feeType->due_date_type) {
                'end_of_month' => $baseDate->copy()->endOfMonth(),
                'next_15_days' => $baseDate->copy()->addDays(15),
                'next_30_days' => $baseDate->copy()->addDays(30),
                'today' => $baseDate->copy(),
                'start_of_month' => $baseDate->copy()->startOfMonth(),
                default => $baseDate->copy()->endOfMonth(),
            };
        }

        // Default to end of month
        return $baseDate->copy()->endOfMonth();
    }
}
