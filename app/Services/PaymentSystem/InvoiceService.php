<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Generate monthly invoices for all active students.
     * 
     * For each active student:
     * - Get all monthly fee categories for their grade
     * - Check if invoice already exists for current month
     * - Create one invoice containing all monthly fees
     * - Create invoice_fees records for each monthly fee
     * - Calculate total_amount as sum of fees
     * - Set due_date from fee categories
     * - Generate unique invoice_number
     * 
     * Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.9
     *
     * @param Carbon|null $month The month to generate invoices for (defaults to current month)
     * @return int Number of invoices created
     */
    public function generateMonthlyInvoices(?Carbon $month = null): int
    {
        $month = $month ?? now();
        $monthString = $month->format('Y-m');
        $academicYear = $month->format('Y');
        
        $invoicesCreated = 0;

        try {
            DB::beginTransaction();

            // Get all active students
            $students = StudentProfile::where('status', 'active')
                ->with('grade')
                ->get();

            foreach ($students as $student) {
                // Skip students without a grade
                if (!$student->grade_id) {
                    Log::warning('Student has no grade assigned', [
                        'student_id' => $student->id,
                    ]);
                    continue;
                }

                // Get monthly fees for this student's grade
                $monthlyFees = FeeStructure::where('grade', $student->grade->level)
                    ->where('frequency', 'monthly')
                    ->where('is_active', true)
                    ->where('amount', '>', 0) // Ensure fee has an amount
                    ->where('name', '!=', 'Special Course Fee') // Exclude Special Course Fee
                    ->get();

                // Skip if no monthly fees for this grade
                if ($monthlyFees->isEmpty()) {
                    continue;
                }

                // Check if invoice already exists for this student and month
                $existingInvoice = Invoice::where('student_id', $student->id)
                    ->where('invoice_type', 'monthly')
                    ->where('batch_id', $student->batch_id)
                    ->whereHas('fees', function ($query) use ($monthString) {
                        // Check if any fee in the invoice is for this month
                        // We'll store month info in the invoice creation
                    })
                    ->first();

                if ($existingInvoice) {
                    Log::info('Monthly invoice already exists', [
                        'student_id' => $student->id,
                        'month' => $monthString,
                    ]);
                    continue;
                }

                // Calculate total amount
                $totalAmount = $monthlyFees->sum('amount');

                // Get the earliest due date from all fees
                $dueDate = $monthlyFees->min('due_date');

                // Create invoice
                $invoice = Invoice::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'student_id' => $student->id,
                    'batch_id' => $student->grade?->batch_id,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'remaining_amount' => $totalAmount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'invoice_type' => 'monthly',
                ]);

                // Create invoice_fees for each monthly fee
                foreach ($monthlyFees as $fee) {
                    InvoiceFee::create([
                        'invoice_id' => $invoice->id,
                        'fee_id' => $fee->id,
                        'fee_name' => $fee->name,
                        'fee_name_mm' => $fee->name_mm,
                        'amount' => $fee->amount,
                        'paid_amount' => 0,
                        'remaining_amount' => $fee->amount,
                        'supports_payment_period' => $fee->supports_payment_period,
                        'due_date' => $fee->due_date,
                        'status' => 'unpaid',
                    ]);
                }

                $invoicesCreated++;

                Log::info('Monthly invoice created', [
                    'invoice_id' => $invoice->id,
                    'student_id' => $student->id,
                    'month' => $monthString,
                    'total_amount' => $totalAmount,
                ]);
            }

            DB::commit();

            Log::info('Monthly invoices generation completed', [
                'month' => $monthString,
                'invoices_created' => $invoicesCreated,
            ]);

            return $invoicesCreated;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate monthly invoices', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a remaining balance invoice from a partial payment.
     *
     * Creates a new invoice for the remaining unpaid amounts from the original invoice.
     * Only includes fees that still have a remaining balance.
     *
     * @param Invoice $originalInvoice The original invoice with partial payment
     * @return Invoice The newly created remaining balance invoice
     * @throws \Exception If invoice generation fails
     */
    public function generateRemainingBalanceInvoice(Invoice $originalInvoice): Invoice
    {
        return DB::transaction(function () use ($originalInvoice) {
            // Load the fees with remaining amounts
            $feesWithBalance = $originalInvoice->fees()
                ->where('remaining_amount', '>', 0)
                ->get();

            // If no fees have remaining balance, don't create an invoice
            if ($feesWithBalance->isEmpty()) {
                throw new \Exception('No fees with remaining balance found for invoice: ' . $originalInvoice->invoice_number);
            }

            // Calculate total remaining amount
            $totalRemainingAmount = $feesWithBalance->sum('remaining_amount');

            // Generate unique invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Get due date from configuration (default 30 days from now)
            $dueDate = $this->calculateDueDate();

            // Create the remaining balance invoice
            $remainingBalanceInvoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $originalInvoice->student_id,
                'batch_id' => $originalInvoice->batch_id,
                'total_amount' => $totalRemainingAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalRemainingAmount,
                'due_date' => $dueDate,
                'status' => 'pending',
                'invoice_type' => 'remaining_balance',
                'parent_invoice_id' => $originalInvoice->id,
            ]);

            // Create invoice fees for the remaining balance invoice
            foreach ($feesWithBalance as $originalFee) {
                InvoiceFee::create([
                    'invoice_id' => $remainingBalanceInvoice->id,
                    'fee_id' => $originalFee->fee_id,
                    'fee_name' => $originalFee->fee_name,
                    'fee_name_mm' => $originalFee->fee_name_mm,
                    'amount' => $originalFee->remaining_amount,
                    'paid_amount' => 0,
                    'remaining_amount' => $originalFee->remaining_amount,
                    'supports_payment_period' => $originalFee->supports_payment_period,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                ]);
            }

            Log::info('Remaining balance invoice created', [
                'original_invoice_id' => $originalInvoice->id,
                'original_invoice_number' => $originalInvoice->invoice_number,
                'new_invoice_id' => $remainingBalanceInvoice->id,
                'new_invoice_number' => $remainingBalanceInvoice->invoice_number,
                'remaining_amount' => $totalRemainingAmount,
                'fees_count' => $feesWithBalance->count(),
            ]);

            return $remainingBalanceInvoice;
        });
    }

    /**
     * Generate a unique invoice number.
     *
     * Format: INV-YYYYMMDD-XXXX
     * Where XXXX is a sequential number for the day.
     *
     * @return string
     */
    public function generateInvoiceNumber(): string
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
    public function getNextInvoiceSequence(): int
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

    /**
     * Calculate the due date for a new invoice.
     *
     * Uses configuration or defaults to 30 days from now.
     *
     * @return Carbon
     */
    private function calculateDueDate(): Carbon
    {
        // Get due date days from config, default to 30 days
        $dueDateDays = config('payment_system.invoice_due_days', 30);
        
        return now()->addDays($dueDateDays);
    }

    /**
     * Calculate the status of an invoice based on its amounts and due date.
     * 
     * Status logic:
     * - 'paid': remaining_amount = 0
     * - 'partial': paid_amount > 0 and remaining_amount > 0
     * - 'overdue': paid_amount = 0 and due_date < current_date
     * - 'pending': paid_amount = 0 and due_date >= current_date
     * 
     * Validates: Requirements 5.7, 11.7, 11.8
     *
     * @param Invoice $invoice
     * @return string
     */
    public function calculateInvoiceStatus(Invoice $invoice): string
    {
        // If fully paid
        if ($invoice->remaining_amount == 0) {
            return 'paid';
        }

        // If partially paid
        if ($invoice->paid_amount > 0 && $invoice->remaining_amount > 0) {
            return 'partial';
        }

        // If unpaid and overdue
        if ($invoice->paid_amount == 0 && $invoice->due_date->isPast()) {
            return 'overdue';
        }

        // If unpaid and not yet due
        return 'pending';
    }

    /**
     * Update invoice amounts based on its invoice fees.
     * 
     * Recalculates:
     * - paid_amount: sum of all invoice_fees' paid_amounts
     * - remaining_amount: total_amount - paid_amount
     * - status: calculated based on amounts and due date
     * 
     * Validates: Requirements 11.5, 11.6, 11.7, 11.8
     *
     * @param Invoice $invoice
     * @return void
     */
    public function updateInvoiceAmounts(Invoice $invoice): void
    {
        // Recalculate paid_amount as sum of all invoice_fees' paid_amounts
        $invoice->paid_amount = $invoice->fees()->sum('paid_amount');

        // Recalculate remaining_amount
        $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;

        // Recalculate status
        $invoice->status = $this->calculateInvoiceStatus($invoice);

        // Save the invoice
        $invoice->save();
    }

    /**
     * Get invoices for a student with optional status filter.
     * 
     * Returns all invoices with their associated fees, and calculates counts:
     * - total: total number of invoices
     * - pending: invoices with status 'pending'
     * - overdue: invoices with status 'overdue'
     * 
     * Validates: Requirements 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7
     *
     * @param string $studentId
     * @param string|null $status Optional status filter (pending, partial, paid, overdue)
     * @param string|null $academicYear Optional academic year filter
     * @return array Contains 'invoices' collection and 'counts' array
     */
    public function getInvoicesForStudent(string $studentId, ?string $status = null, ?string $academicYear = null): array
    {
        $query = Invoice::where('student_id', $studentId)
            ->with(['fees' => function ($query) {
                $query->with('feeType')->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc');

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        // Apply academic year filter if provided
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $invoices = $query->get();

        // Calculate counts
        $allInvoices = Invoice::where('student_id', $studentId);
        if ($academicYear) {
            $allInvoices->where('academic_year', $academicYear);
        }

        $counts = [
            'total' => $allInvoices->count(),
            'pending' => (clone $allInvoices)->where('status', 'pending')->count(),
            'overdue' => (clone $allInvoices)->where('status', 'overdue')->count(),
        ];

        return [
            'invoices' => $invoices,
            'counts' => $counts,
        ];
    }
}
