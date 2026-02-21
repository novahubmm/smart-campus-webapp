<?php

namespace App\Jobs\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateOneTimeFeeInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The fee structure to generate invoices for.
     */
    protected FeeStructure $feeStructure;

    /**
     * Create a new job instance.
     */
    public function __construct(FeeStructure $feeStructure)
    {
        $this->feeStructure = $feeStructure;
    }

    /**
     * Execute the job.
     * 
     * Generates separate invoices for all active students in the target grade.
     * Each invoice contains only the one-time fee.
     * 
     * Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            // Query all active students in the target grade
            $students = StudentProfile::where('status', 'active')
                ->whereHas('grade', function ($query) {
                    $query->where('level', $this->feeStructure->grade);
                })
                ->get();

            $invoicesCreated = 0;

            foreach ($students as $student) {
                try {
                    // Generate unique invoice number
                    $invoiceNumber = $this->generateInvoiceNumber();

                    // Set academic year from batch or current year
                    $academicYear = $this->feeStructure->batch;

                    // Create invoice for this one-time fee
                    $invoice = Invoice::create([
                        'invoice_number' => $invoiceNumber,
                        'student_id' => $student->id,
                        'academic_year' => $academicYear,
                        'total_amount' => $this->feeStructure->amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $this->feeStructure->amount,
                        'due_date' => $this->feeStructure->due_date,
                        'status' => 'pending',
                        'invoice_type' => 'one_time',
                    ]);

                    // Create invoice_fee for the one-time fee
                    InvoiceFee::create([
                        'invoice_id' => $invoice->id,
                        'fee_id' => $this->feeStructure->id,
                        'fee_name' => $this->feeStructure->name,
                        'fee_name_mm' => $this->feeStructure->name_mm,
                        'amount' => $this->feeStructure->amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $this->feeStructure->amount,
                        'supports_payment_period' => $this->feeStructure->supports_payment_period,
                        'due_date' => $this->feeStructure->due_date,
                        'status' => 'unpaid',
                    ]);

                    $invoicesCreated++;

                    Log::info('One-time fee invoice created', [
                        'invoice_id' => $invoice->id,
                        'student_id' => $student->id,
                        'fee_structure_id' => $this->feeStructure->id,
                        'amount' => $this->feeStructure->amount,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create one-time fee invoice for student', [
                        'student_id' => $student->id,
                        'fee_structure_id' => $this->feeStructure->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with next student instead of failing entire batch
                }
            }

            DB::commit();

            Log::info('One-time fee invoices generation completed', [
                'fee_structure_id' => $this->feeStructure->id,
                'grade' => $this->feeStructure->grade,
                'batch' => $this->feeStructure->batch,
                'total_students' => $students->count(),
                'invoices_created' => $invoicesCreated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate one-time fee invoices', [
                'fee_structure_id' => $this->feeStructure->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a unique invoice number.
     *
     * Format: INV-YYYYMMDD-XXXX
     * Where XXXX is a sequential number for the day.
     *
     * @return string
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        
        // Try to get the next sequence number, with retry logic for uniqueness
        $maxAttempts = 10;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $sequence = $this->getNextInvoiceSequence();
            $invoiceNumber = sprintf('%s-%s-%04d', $prefix, $date, $sequence);
            
            // Check if this invoice number already exists
            if (!Invoice::where('invoice_number', $invoiceNumber)->exists()) {
                return $invoiceNumber;
            }
            
            // If it exists, wait a tiny bit and try again
            usleep(1000); // 1ms delay
        }
        
        // Fallback: use a random component if we can't get a unique sequence
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        return sprintf('%s-%s-%s', $prefix, $date, $random);
    }

    /**
     * Get the next invoice sequence number for today.
     *
     * @return int
     */
    private function getNextInvoiceSequence(): int
    {
        $lastInvoice = Invoice::whereDate('created_at', today())
            ->orderBy('invoice_number', 'desc')
            ->lockForUpdate()
            ->first();
        
        if ($lastInvoice && preg_match('/INV-\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
            return intval($matches[1]) + 1;
        }
        
        return 1;
    }
}
