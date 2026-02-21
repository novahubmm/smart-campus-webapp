<?php

namespace App\Console\Commands;

use App\Jobs\PaymentSystem\GenerateOneTimeFeeInvoicesJob;
use App\Models\PaymentSystem\FeeStructure;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateOneTimeFee extends Command
{
    protected $signature = 'payment:create-one-time-fee 
                            {name : The name of the fee}
                            {amount : The fee amount in MMK}
                            {grade : The target grade level}
                            {batch : The target batch}
                            {--name-mm= : Myanmar name (optional)}
                            {--description= : Description (optional)}
                            {--description-mm= : Myanmar description (optional)}
                            {--target-month=1 : Target month (1-12)}
                            {--due-days=30 : Days until due date}
                            {--fee-type=other : Fee type (tuition, transportation, library, lab, sports, course_materials, other)}
                            {--generate-invoices : Automatically generate invoices for all students}';

    protected $description = 'Create a one-time fee structure and optionally generate invoices';

    public function handle(): int
    {
        $name = $this->argument('name');
        $amount = $this->argument('amount');
        $grade = $this->argument('grade');
        $batch = $this->argument('batch');
        
        $nameMm = $this->option('name-mm');
        $description = $this->option('description');
        $descriptionMm = $this->option('description-mm');
        $targetMonth = (int) $this->option('target-month');
        $dueDays = (int) $this->option('due-days');
        $feeType = $this->option('fee-type');
        $generateInvoices = $this->option('generate-invoices');

        // Validate inputs
        if (!is_numeric($amount) || $amount <= 0) {
            $this->error('Amount must be a positive number');
            return self::FAILURE;
        }

        if (!is_numeric($targetMonth) || $targetMonth < 1 || $targetMonth > 12) {
            $this->error('Target month must be between 1 and 12');
            return self::FAILURE;
        }

        $validFeeTypes = ['tuition', 'transportation', 'library', 'lab', 'sports', 'course_materials', 'other'];
        if (!in_array($feeType, $validFeeTypes)) {
            $this->error('Invalid fee type. Must be one of: ' . implode(', ', $validFeeTypes));
            return self::FAILURE;
        }

        // Display summary
        $this->info('Creating one-time fee with the following details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Name (MM)', $nameMm ?? 'N/A'],
                ['Amount', number_format($amount) . ' MMK'],
                ['Grade', $grade],
                ['Batch', $batch],
                ['Fee Type', $feeType],
                ['Target Month', Carbon::create()->month($targetMonth)->format('F')],
                ['Due Date', Carbon::now()->addDays($dueDays)->format('Y-m-d')],
                ['Description', $description ?? 'N/A'],
            ]
        );

        if (!$this->confirm('Do you want to proceed?', true)) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        try {
            // Create fee structure
            $feeStructure = FeeStructure::create([
                'name' => $name,
                'name_mm' => $nameMm,
                'description' => $description,
                'description_mm' => $descriptionMm,
                'amount' => $amount,
                'frequency' => 'one_time',
                'fee_type' => $feeType,
                'grade' => $grade,
                'batch' => $batch,
                'target_month' => $targetMonth,
                'due_date' => Carbon::now()->addDays($dueDays),
                'supports_payment_period' => false,
                'is_active' => true,
            ]);

            $this->info("✓ Fee structure created successfully! (ID: {$feeStructure->id})");

            // Count students
            $studentCount = StudentProfile::where('status', 'active')
                ->whereHas('grade', function ($query) use ($grade) {
                    $query->where('level', $grade);
                })
                ->count();

            $this->info("Found {$studentCount} active student(s) in grade {$grade}");

            if ($studentCount === 0) {
                $this->warn('No students found in target grade. No invoices will be generated.');
                return self::SUCCESS;
            }

            // Generate invoices if requested
            if ($generateInvoices || $this->confirm('Generate invoices for all students now?', true)) {
                $this->info('Dispatching invoice generation job...');
                GenerateOneTimeFeeInvoicesJob::dispatch($feeStructure);
                $this->info("✓ Job dispatched! {$studentCount} invoice(s) will be generated in the background.");
                $this->info('Check the invoices_payment_system table to see the results.');
            } else {
                $this->info('Skipping invoice generation. You can generate invoices later using:');
                $this->line("php artisan payment:generate-one-time-invoices {$feeStructure->id}");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to create fee structure: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
