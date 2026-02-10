<?php

namespace App\Console\Commands;

use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Generate Monthly Fees Command
 * 
 * This command generates invoices for students including:
 * - Monthly fee structures (tuition, library, sports, etc.)
 * - Other fee structures when --include-other-fees flag is used (one-time, annual, semester fees)
 * 
 * Usage:
 * - Generate monthly fees only: php artisan fees:generate-monthly --month=2026-02
 * - Generate monthly + other fees: php artisan fees:generate-monthly --month=2026-02 --include-other-fees
 * - Force regenerate: php artisan fees:generate-monthly --month=2026-02 --force
 * 
 * The command will:
 * 1. Find all active students
 * 2. Get applicable fee structures for each student's grade
 * 3. Create invoices with all applicable fees
 * 4. Skip students who already have invoices (unless --force is used)
 */
class GenerateMonthlyFees extends Command
{
    protected $signature = 'fees:generate-monthly 
                            {--month= : Month to generate fees for (YYYY-MM format)}
                            {--student= : Generate for specific student ID only}
                            {--include-other-fees : Include one-time and other fee structures}
                            {--force : Force regenerate even if invoices exist}';

    protected $description = 'Generate monthly school fees and optionally other fee structures for all students';

    public function handle(): int
    {
        $this->info('🏫 Starting Monthly Fee Generation...');
        $this->newLine();

        $monthInput = $this->option('month');
        $targetDate = $monthInput ? Carbon::parse($monthInput) : Carbon::now();
        $monthName = $targetDate->format('F Y');

        $this->info("📅 Generating fees for: {$monthName}");
        $this->newLine();

        $studentsQuery = StudentProfile::with(['grade', 'classModel'])
            ->where('status', 'active');

        if ($studentId = $this->option('student')) {
            $studentsQuery->where('id', $studentId);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->warn('⚠️  No active students found.');
            return self::FAILURE;
        }

        $this->info("👥 Found {$students->count()} active student(s)");
        $this->newLine();

        // Get monthly fee structures
        $feeStructures = FeeStructure::where('frequency', 'monthly')
            ->where('status', true)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('applicable_from')
                    ->orWhere('applicable_from', '<=', $targetDate);
            })
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('applicable_to')
                    ->orWhere('applicable_to', '>=', $targetDate);
            })
            ->with('feeType')
            ->get();

        // Get other fee structures if option is enabled
        $otherFeeStructures = collect();
        if ($this->option('include-other-fees')) {
            $otherFeeStructures = FeeStructure::whereIn('frequency', ['one-time', 'annual', 'semester'])
                ->where('status', true)
                ->where(function ($query) use ($targetDate) {
                    $query->whereNull('applicable_from')
                        ->orWhere('applicable_from', '<=', $targetDate);
                })
                ->where(function ($query) use ($targetDate) {
                    $query->whereNull('applicable_to')
                        ->orWhere('applicable_to', '>=', $targetDate);
                })
                ->with('feeType')
                ->get();
            
            $this->info("💰 Found {$feeStructures->count()} monthly fee structure(s)");
            $this->info("💰 Found {$otherFeeStructures->count()} other fee structure(s)");
        } else {
            $this->info("💰 Found {$feeStructures->count()} monthly fee structure(s)");
        }

        if ($feeStructures->isEmpty() && $otherFeeStructures->isEmpty()) {
            $this->warn('⚠️  No active fee structures found.');
            return self::FAILURE;
        }

        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($students as $student) {
            try {
                $result = $this->generateFeeForStudent($student, $targetDate, $feeStructures, $otherFeeStructures);
                $stats[$result]++;
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->newLine();
                $this->error("❌ Error for student {$student->user?->name}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('📊 Generation Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Created', $stats['created']],
                ['⏭️  Skipped', $stats['skipped']],
                ['❌ Errors', $stats['errors']],
            ]
        );

        $this->newLine();
        $this->info('✨ Monthly fee generation completed!');

        return self::SUCCESS;
    }

    private function generateFeeForStudent(StudentProfile $student, Carbon $targetDate, $feeStructures, $otherFeeStructures = null): string
    {
        $invoiceDate = $targetDate->copy()->startOfMonth();
        $dueDate = $targetDate->copy()->endOfMonth();

        $existingInvoice = Invoice::where('student_id', $student->id)
            ->whereYear('invoice_date', $targetDate->year)
            ->whereMonth('invoice_date', $targetDate->month)
            ->first();

        if ($existingInvoice && !$this->option('force')) {
            return 'skipped';
        }

        // Filter monthly fees applicable to this student
        $applicableFees = $feeStructures->filter(function ($structure) use ($student) {
            if ($structure->grade_id && $structure->grade_id !== $student->grade_id) {
                return false;
            }
            return true;
        });

        // Filter other fees applicable to this student
        $applicableOtherFees = collect();
        if ($otherFeeStructures && $otherFeeStructures->isNotEmpty()) {
            $applicableOtherFees = $otherFeeStructures->filter(function ($structure) use ($student) {
                if ($structure->grade_id && $structure->grade_id !== $student->grade_id) {
                    return false;
                }
                return true;
            });
        }

        // Combine all applicable fees
        $allApplicableFees = $applicableFees->merge($applicableOtherFees);

        if ($allApplicableFees->isEmpty()) {
            return 'skipped';
        }

        $subtotal = $allApplicableFees->sum('amount');
        $discount = 0;
        $totalAmount = $subtotal - $discount;

        DB::beginTransaction();
        try {
            if ($existingInvoice && $this->option('force')) {
                $invoice = $existingInvoice;
                $invoice->update([
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'balance' => $totalAmount - $invoice->paid_amount,
                    'status' => $invoice->paid_amount > 0 ? 'partial' : 'sent',
                ]);
                $invoice->items()->delete();
            } else {
                $notesParts = ["School fees for {$targetDate->format('F Y')}"];
                if ($applicableOtherFees->isNotEmpty()) {
                    $notesParts[] = "including other fees";
                }
                
                $invoice = Invoice::create([
                    'invoice_number' => $this->generateInvoiceNumber($targetDate),
                    'student_id' => $student->id,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'balance' => $totalAmount,
                    'status' => 'sent',
                    'notes' => implode(' ', $notesParts),
                ]);
            }

            foreach ($allApplicableFees as $feeStructure) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'fee_type_id' => $feeStructure->fee_type_id,
                    'description' => $feeStructure->feeType->name . ' (' . ucfirst($feeStructure->frequency) . ')',
                    'quantity' => 1,
                    'unit_price' => $feeStructure->amount,
                    'amount' => $feeStructure->amount,
                ]);
            }

            DB::commit();
            return 'created';
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateInvoiceNumber(Carbon $date): string
    {
        $prefix = 'INV';
        $yearMonth = $date->format('Ym');
        
        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$yearMonth}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}-{$yearMonth}-{$sequence}";
    }
}
