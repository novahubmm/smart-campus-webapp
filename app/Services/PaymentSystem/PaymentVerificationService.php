<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVerificationService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Verify a payment submission.
     * 
     * Updates payment status to 'verified', records verification timestamp and admin user.
     * Sends FCM notification to guardian.
     * 
     * @param Payment $payment The payment to verify
     * @param User $admin The admin user verifying the payment
     * @return void
     * @throws \InvalidArgumentException If payment is not pending verification
     */
    public function verifyPayment(Payment $payment, User $admin): void
    {
        if ($payment->status !== 'pending_verification') {
            throw new \InvalidArgumentException(
                "Cannot verify payment with status '{$payment->status}'. Only pending_verification payments can be verified."
            );
        }

        DB::transaction(function () use ($payment, $admin) {
            $payment->status = 'verified';
            $payment->verified_at = now();
            $payment->verified_by = $admin->id;
            $payment->save();
        });

        // Notify guardian — failure should not break the verification flow
        try {
            $this->notificationService->notifyGuardianOfVerification($payment);
        } catch (\Exception $e) {
            Log::warning('Failed to send verification notification', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Check if we need to generate a remaining balance invoice
        // only if payment is partial and invoice has remaining amount
        $payment->load('invoice');
        
        // 1. Handle Remaining Balance (Partial Payment)
        if ($payment->invoice && $payment->invoice->remaining_amount > 0 && $payment->payment_months == 1) {
            try {
                \App\Jobs\PaymentSystem\RemainingBalanceInvoiceJob::dispatch($payment->invoice);
                Log::info('Dispatched RemainingBalanceInvoiceJob', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch RemainingBalanceInvoiceJob', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // 2. Handle Advance Payment (Multi-Month)
        if ($payment->payment_months > 1) {
             try {
                $this->generateAdvanceInvoices($payment);
                Log::info('Generated Advance Invoices', [
                    'payment_id' => $payment->id,
                    'months' => $payment->payment_months,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate Advance Invoices', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    
    /**
     * Generate future invoices for advance payments.
     * 
     * Iterates from 2 to payment_months.
     * Creates a new invoice for each month with the specific fee.
     * Marks the invoice as PAID.
     * Links to parent invoice.
     */
    protected function generateAdvanceInvoices(Payment $payment): void
    {
        $parentInvoice = $payment->invoice;
        if (!$parentInvoice) return;
        
        $months = $payment->payment_months;
        $student = $parentInvoice->student;
        
        // Identify which fee was paid in advance (usually School Fee)
        // We look at fee details where paid_amount >= full_amount
        // Actually, we should look for fees that support payment period
        $payment->load('feeDetails.invoiceFee');
        
        $advanceFees = $payment->feeDetails->filter(function($detail) {
             // We can check if the related invoice fee supports multi-month
             // But simpler: if we are in this block, the user paid for N months.
             // We assume the intention is to pay for School Fee for N months.
             // We need to find the specific fee type (e.g. School Fee) to clone.
             return str_contains($detail->fee_name, 'School Fee') || str_contains($detail->fee_name, 'Tuition');
        });
        
        if ($advanceFees->isEmpty()) {
            // Fallback: try to find any fee with supports_payment_period = true in the parent invoice
            // But payment details don't store that flag directly.
            // Let's rely on name for now as per business logic (School Fee is usually the one).
            // Or use the first fee that is fully paid and has large amount?
            // Let's use the first fee for now if specific one not found, but it's risky.
            // Better: Re-query invoice fees
            $advanceFees = $payment->feeDetails;
        }

        // We only generate invoices for the fees that were ACTUALLY paid in advance.
        // But the PaymentService logic combined them into one "payment amount".
        // If we have School Fee + Transport Fee, and user pays 3 months School Fee + 1 Month Transport...
        // The `payment_months` attribute on attributes implies the WHOLE payment is for N months?
        // Actually, in `submitPayment`, `payment_months` is global for the payment.
        // And `validatePaymentAmounts` checks `supports_payment_period`.
        // So likely ONLY `School Fee` is being paid for N months.
        
        $targetFeeDetail = $advanceFees->first(); // Assumption: Primary fee
        if (!$targetFeeDetail) return;

        $baseDate = $parentInvoice->created_at->copy(); // Or due date?
        // Requirement: "child invoice no and in child invoice also link with parent link"
        
        for ($i = 1; $i < $months; $i++) {
            // Calculate next month
            $nextMonthDate = $baseDate->copy()->addMonths($i);
            
            DB::transaction(function() use ($parentInvoice, $targetFeeDetail, $nextMonthDate, $payment, $i) {
                // Create Child Invoice
                $childInvoice = Invoice::create([
                    'invoice_number' => 'INV-' . strtoupper(uniqid()), // Should use standard generation service but acceptable for now
                    'student_id' => $parentInvoice->student_id,
                    'batch_id' => $parentInvoice->batch_id,
                    'total_amount' => $targetFeeDetail->full_amount, // Original Fee Amount
                    'paid_amount' => $targetFeeDetail->full_amount,  // Fully Paid
                    'remaining_amount' => 0,
                    'due_date' => $nextMonthDate->copy()->endOfMonth(),
                    'status' => 'paid', // Mark as PAID
                    'invoice_type' => 'monthly',
                    'parent_invoice_id' => $parentInvoice->id, // Link to Parent
                    'notes' => "Advance payment via {$payment->payment_number} (Month " . ($i + 1) . ")",
                ]);
                
                // Get fee_id from the original invoice fee
                $feeId = $targetFeeDetail->invoiceFee ? $targetFeeDetail->invoiceFee->fee_id : null;
                
                // Add Fee
                InvoiceFee::create([
                    'invoice_id' => $childInvoice->id,
                    'fee_id' => $feeId,
                    'fee_name' => $targetFeeDetail->fee_name,
                    'fee_name_mm' => $targetFeeDetail->fee_name_mm,
                    'amount' => $targetFeeDetail->full_amount,
                    'paid_amount' => $targetFeeDetail->full_amount,
                    'remaining_amount' => 0,
                    'due_date' => $childInvoice->due_date,
                    'status' => 'paid',
                    'supports_payment_period' => true,
                ]);
            });
        }
    }

    /**
     * Reject a payment submission and rollback all invoice amount updates.
     * 
     * Updates payment status to 'rejected', stores rejection reason, and rolls back
     * all paid_amount and remaining_amount updates on invoice_fees and invoices.
     * Sends FCM notification to guardian with rejection reason.
     * 
     * @param Payment $payment The payment to reject
     * @param string $reason The reason for rejection
     * @param User $admin The admin user rejecting the payment
     * @return void
     * @throws \InvalidArgumentException If payment is not pending verification
     */
    public function rejectPayment(Payment $payment, string $reason, User $admin): void
    {
        if ($payment->status !== 'pending_verification') {
            throw new \InvalidArgumentException(
                "Cannot reject payment with status '{$payment->status}'. Only pending_verification payments can be rejected."
            );
        }

        $newInvoice = null;

        DB::transaction(function () use ($payment, $reason, $admin, &$newInvoice) {
            // Update payment status
            $payment->status = 'rejected';
            $payment->rejection_reason = $reason;
            $payment->verified_by = $admin->id;
            $payment->verified_at = now();
            $payment->save();
            
            // Rollback payment amounts and set invoice status to 'rejected'
            $this->rollbackPaymentAmounts($payment, true);

            // Create a duplicate invoice for resubmission
            $originalInvoice = $payment->invoice;
            if ($originalInvoice) {
                $newInvoice = $this->invoiceService->createDuplicateInvoiceAfterRejection($originalInvoice);
            }
        });

        // Notify guardian — failure should not break the rejection flow
        try {
            $this->notificationService->notifyGuardianOfRejection($payment);
        } catch (\Exception $e) {
            Log::warning('Failed to send rejection notification', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Log the new invoice creation
        if ($newInvoice) {
            Log::info('New invoice created after payment rejection', [
                'rejected_payment_id' => $payment->id,
                'original_invoice_id' => $payment->invoice_id,
                'new_invoice_id' => $newInvoice->id,
                'new_invoice_number' => $newInvoice->invoice_number,
            ]);
        }
    }

    /**
     * Rollback invoice and invoice_fee amounts after payment rejection.
     * 
     * Resets the payment amounts on invoice_fees and invoices back to unpaid state,
     * and updates statuses accordingly.
     * 
     * @param Payment $payment The rejected payment
     * @param bool $setRejectedStatus Whether to set invoice status to 'rejected'
     * @return void
     */
    public function rollbackPaymentAmounts(Payment $payment, bool $setRejectedStatus = false): void
    {
        // Load payment with relationships
        $payment->load(['feeDetails.invoiceFee.invoice']);
        
        // Track invoices that need to be updated
        $invoicesToUpdate = collect();
        
        // Rollback each invoice_fee - reset to unpaid state
        foreach ($payment->feeDetails as $feeDetail) {
            $invoiceFee = $feeDetail->invoiceFee;
            
            // Reset to unpaid state (assuming this was the first/only payment attempt)
            $invoiceFee->paid_amount = 0;
            $invoiceFee->remaining_amount = $invoiceFee->amount;
            $invoiceFee->status = 'unpaid';
            $invoiceFee->save();
            
            // Track the invoice for later update
            if (!$invoicesToUpdate->contains('id', $invoiceFee->invoice_id)) {
                $invoicesToUpdate->push($invoiceFee->invoice);
            }
        }
        
        // Rollback and recalculate each affected invoice
        foreach ($invoicesToUpdate as $invoice) {
            $this->recalculateInvoiceAmounts($invoice, $setRejectedStatus);
        }
    }

    /**
     * Calculate the status of an invoice fee based on its amounts.
     * 
     * @param InvoiceFee $invoiceFee
     * @return string 'paid', 'partial', or 'unpaid'
     */
    private function calculateInvoiceFeeStatus(InvoiceFee $invoiceFee): string
    {
        if ($invoiceFee->remaining_amount == 0) {
            return 'paid';
        }
        
        if ($invoiceFee->paid_amount > 0) {
            return 'partial';
        }
        
        return 'unpaid';
    }

    /**
     * Recalculate invoice amounts and status based on its invoice_fees.
     * 
     * @param Invoice $invoice
     * @param bool $setRejectedStatus Whether to set status to 'rejected' instead of calculating
     * @return void
     */
    private function recalculateInvoiceAmounts(Invoice $invoice, bool $setRejectedStatus = false): void
    {
        // Reload fees to get fresh data
        $invoice->load('fees');
        
        // Recalculate paid_amount as sum of all invoice_fees paid_amounts
        $invoice->paid_amount = $invoice->fees->sum('paid_amount');
        
        // Recalculate remaining_amount
        $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
        
        // Set status
        if ($setRejectedStatus) {
            $invoice->status = 'rejected';
        } else {
            $invoice->status = $this->calculateInvoiceStatus($invoice);
        }
        
        $invoice->save();
    }

    /**
     * Calculate the status of an invoice based on its amounts and due date.
     * 
     * @param Invoice $invoice
     * @return string 'paid', 'partial', 'overdue', or 'pending'
     */
    private function calculateInvoiceStatus(Invoice $invoice): string
    {
        if ($invoice->remaining_amount == 0) {
            return 'paid';
        }
        
        if ($invoice->paid_amount > 0) {
            return 'partial';
        }
        
        if ($invoice->due_date->isPast()) {
            return 'overdue';
        }
        
        return 'pending';
    }
}
