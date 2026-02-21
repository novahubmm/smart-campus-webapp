<?php

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\FeeStructure;
use App\Models\Grade;
use App\Models\Batch;
use Illuminate\Database\Seeder;

class FeeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: School Fee is NOT included here - it comes from grade.price_per_month
     * This seeder only creates optional/additional fee categories
     */
    public function run(): void
    {
        $this->command->info('Seeding fee categories and structures...');
        $this->command->info('Note: School Fee comes from grade.price_per_month');

        // Define fee categories (excluding School Fee)
        $categories = [
            [
                'name' => 'Transportation Fee',
                'name_mm' => 'ယာဉ်စီးခ',
                'code' => 'TRANSPORT',
                'description' => 'Monthly school bus transportation fee for students',
                'description_mm' => 'လစဉ် ကျောင်းဘတ်စ်ကား စီးခ',
                'is_mandatory' => false,  // Optional
                'status' => true,
                'partial_status' => true,  // Allows partial payment
                'due_date_type' => 'next_15_days',
            ],
            [
                'name' => 'Book Fee',
                'name_mm' => 'စာအုပ်ခ',
                'code' => 'BOOK_FEE',
                'description' => 'Monthly book and learning materials fee',
                'description_mm' => 'လစဉ် စာအုပ်နှင့် သင်ကြားရေးပစ္စည်းခ',
                'is_mandatory' => false,  // Optional
                'status' => true,
                'partial_status' => false,  // Does NOT allow partial payment
                'due_date_type' => 'next_15_days',
            ],
        ];

        // Fee amounts per grade level (KG and 1-12)
        $feeAmounts = [
            'TRANSPORT' => [
                0 => 25000,   // Kindergarten
                1 => 30000,   // Grade 1
                2 => 30000,   // Grade 2
                3 => 30000,   // Grade 3
                4 => 30000,   // Grade 4
                5 => 35000,   // Grade 5
                6 => 35000,   // Grade 6
                7 => 35000,   // Grade 7
                8 => 35000,   // Grade 8
                9 => 40000,   // Grade 9
                10 => 40000,  // Grade 10
                11 => 40000,  // Grade 11
                12 => 40000,  // Grade 12
            ],
            'BOOK_FEE' => [
                0 => 12000,   // Kindergarten
                1 => 15000,   // Grade 1
                2 => 15000,   // Grade 2
                3 => 18000,   // Grade 3
                4 => 18000,   // Grade 4
                5 => 20000,   // Grade 5
                6 => 20000,   // Grade 6
                7 => 22000,   // Grade 7
                8 => 22000,   // Grade 8
                9 => 25000,   // Grade 9
                10 => 25000,  // Grade 10
                11 => 28000,  // Grade 11
                12 => 28000,  // Grade 12
            ],
        ];

        // Get current batch
        $currentBatch = Batch::where('status', true)->first();
        
        if (!$currentBatch) {
            $this->command->error('No active batch found. Please create a batch first.');
            return;
        }

        // Create or update fee types
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
                    'name_mm' => $category['name_mm'],
                    'description' => $category['description'],
                    'description_mm' => $category['description_mm'] ?? null,
                    'is_mandatory' => $category['is_mandatory'],
                    'status' => $category['status'],
                    'partial_status' => $category['partial_status'] ?? true,
                    'due_date_type' => $category['due_date_type'] ?? 'end_of_month',
                ]);
                $action = 'Updated';
            } else {
                // Create new
                $feeType = FeeType::create($category);
                $action = 'Created';
            }
            
            $mandatory = $category['is_mandatory'] ? '(Mandatory)' : '(Optional)';
            $this->command->info("✓ {$action}: {$feeType->name} {$mandatory}");

            // Create fee structures for each grade
            $grades = Grade::where('batch_id', $currentBatch->id)->get();
            
            foreach ($grades as $grade) {
                $amount = $feeAmounts[$category['code']][$grade->level] ?? 0;
                
                if ($amount > 0) {
                    FeeStructure::updateOrCreate(
                        [
                            'grade_id' => $grade->id,
                            'batch_id' => $currentBatch->id,
                            'fee_type_id' => $feeType->id,
                        ],
                        [
                            'amount' => $amount,
                            'frequency' => 'monthly',
                            'applicable_from' => now()->startOfMonth(),
                            'applicable_to' => now()->endOfYear(),
                            'status' => true,
                        ]
                    );
                    
                    $this->command->info("  → Grade {$grade->level}: {$amount} MMK");
                }
            }
        }

        $this->command->newLine();
        $this->command->info('✓ Fee categories and structures seeded successfully!');
        $this->command->info('Total: ' . count($categories) . ' fee categories (excluding School Fee)');
    }
}
