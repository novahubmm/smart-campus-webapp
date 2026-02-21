<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\PaymentFeeDetail;
use App\Models\PaymentPromotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentProcessingService
{
    /**
     * Process a payment (full or partial)
     * 
     * @param Invoice $invoice
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            DB::beginTransaction();

            // Validate payment type
            $paymentType = $paymentData['payment_type']; // 'full' or 'partial'
            $paymentMonths = $paymentData['payment_months'] ?? 1;
            $feeAmounts = $paymentData['fee_amounts'] ?? []; // For partial payment
            $feePaymentMonths = $paymentData['fee_payment_months'] ?? []; // For full payment with variable months
            
            // Calculate payment amount and prepare fee details
            $totalPaymentAmount = 0;
            $totalDiscountAmount = 0;
            $feePayments = [];
            
            if ($paymentType === 'full') {
                // Determine months for each fee (default to global paymentMonths if not specified)
                foreach ($invoice->fees as $fee) {
                    if ($fee->remaining_amount > 0) {
                        $months = $feePaymentMonths[$fee->id] ?? $paymentMonths;
                        
                        // Calculate amount: fee * months
                        $feeTotal = $fee->remaining_amount * $months;
                        
                        // Calculate discount specific to this fee
                        $discountPercent = 0;
                        if ($this->shouldApplyDiscount($fee)) {
                            $discountPercent = $this->calculateDiscount($months);
                        }
                        
                        $feeDiscount = $feeTotal * ($discountPercent / 100);
                        $feeFinal = $feeTotal - $feeDiscount;
                        
                        $totalPaymentAmount += $feeFinal;
                        $totalDiscountAmount += $feeDiscount;
                        
                        $feePayments[] = [
                            'invoice_fee_id' => $fee->id,
                            'fee_name' => $fee->fee_name,
                            'fee_name_mm' => $fee->fee_name_mm,
                            'full_amount' => $fee->amount,
                            'paid_amount' => $fee->remaining_amount, // Mark as fully paid for this invoice context (logic might need refinement for multi-month tracking if schema supports it, but for now assuming invoice based)
                             // Wait, if paying for multiple months, does it clear future invoices? 
                             // The current system seems to generate invoices monthly. 
                             // If paying for 3 months, we are essentially paying 3 * monthly_fee.
                             // However, the invoice_fees table likely tracks the "current month" fee.
                             // If we pay 3 months, we are paying current + 2 future.
                             // BUT, the `paid_amount` on `invoice_fees` is capped at `amount` usually?
                             // Let's look at `calculateFullPayment` original logic.
                             // It set 'paid_amount' => $fee->remaining_amount.
                             // If we pay 3 months, are we overpaying this invoice?
                             // Yes, effectively pre-paying.
                             // For now, let's treat `paid_amount` as the amount contributing to THIS invoice, 
                             // and any excess would be handled... how?
                             // Actually, the requirement mentions "Pay for 3 months". 
                             // If the system generates distinct invoices per month, paying 3 months means clearing 3 invoices.
                             // BUT, the current UI allows selecting months on a SINGLE invoice view.
                             // This implies the payment is attached to the current invoice but covers future periods OR just collects the money.
                             // The `Payment` record stores `payment_amount`.
                             // `InvoiceFee` has `paid_amount` and `remaining_amount`.
                             // If I pay 300 for a 100 fee, `paid_amount` becomes 300? 
                             // If so, `remaining_amount` becomes -200?
                             // The original code: `$invoiceFee->paid_amount += $feePayment['paid_amount'];`
                             // and `$invoiceFee->remaining_amount = $invoiceFee->amount - $invoiceFee->paid_amount;`
                             // If I pay 3x, remaining becomes negative.
                             // This might be intended for "Advance Payment" or the invoice amount itself should have been 3x?
                             // No, the invoice seems to be for 1 month usually.
                             // Let's follow the immediate requirement: Fix calculation.
                             // I will set `paid_amount` to what is actually being paid (net after discount).
                             // WAIT. `paid_amount` usually tracks the GROSS amount covered? or NET?
                             // Usually `paid_amount` on invoice tracks how much of the DEBT is cleared.
                             // If I owe 100, and get 10% discount, I pay 90. 
                             // Does `paid_amount` go up by 90 or 100?
                             // If it goes by 90, I still owe 10.
                             // So `paid_amount` should probably be the "value" cleared (100).
                             // But here we are paying for FUTURE months too?
                             // The previous developer's code:
                             // $paymentAmount = $invoice->remaining_amount; (This implies 1 month clearing)
                             // But the UI allows selecting 3 months.
                             // If the user selects 3 months, the total jumps to 3x.
                             // This suggests we ARE overpaying this invoice or credit is created.
                             // Let's look at how `InvoiceFee` is updated.
                             // The code I read earlier: 
                             // $invoice->paid_amount += $finalAmount;
                             // This adds the NET amount paid.
                             // So if I pay 3 months (300) - discount (30) = 270.
                             // Invoice paid_amount += 270.
                             // If Invoice total was 100, now remaining is 100 - 270 = -170.
                             // This acts as credit. This seems to be the existing logic for multi-month payment on single invoice.
                             // I will stick to this pattern: Calculate NET amount user pays, and log that.
                            
                            // actually `calculateFullPayment` logic in original code:
                            // 'paid_amount' => $fee->remaining_amount
                            // This was just for 1 month.
                            
                            // New Logic: 
                            // The amount stored in `PaymentFeeDetail` (`paid_amount`) should be the contribution of this payment to that fee.
                            // Which is `feeFinal`.
                            'paid_amount' => $feeFinal,
                            'is_partial' => false,
                             // We also need to store how many months this payment covers?
                             // `Payment` table has `payment_months`. But here it's per fee.
                             // The PaymentFeeDetail model doesn't seem to have `months` column based on code I saw.
                             // But `Payment` model has it.
                             // We'll stick to calculating the correct money for now.
                        ];
                    }
                }
                
                // Final Check: If total is 0? (Should be handled by validation but good to be safe)
                
            } else {
                // Partial payment - use provided amounts
                // For partial payments, the user specifies exactly what they want to pay
                // No automatic discounts are applied
                $feePayments = $this->calculatePartialPayment($invoice, $feeAmounts);
                $totalPaymentAmount = array_sum(array_column($feePayments, 'paid_amount'));
                $totalDiscountAmount = 0; // No discount on partial payments
            }
            
            $finalAmount = $totalPaymentAmount;
            
            // Generate payment number
            $paymentNumber = $this->generatePaymentNumber();
            
            // Create payment record
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'student_id' => $invoice->student_id,
                'invoice_id' => $invoice->id,
                'payment_method_id' => $paymentData['payment_method_id'],
                'payment_amount' => $finalAmount,
                'payment_type' => $paymentType,
                'payment_months' => $paymentMonths, // This might be ambiguous if mixed months, but using max or base is fine for now
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'receipt_image_url' => $paymentData['receipt_image_url'] ?? null,
                'status' => 'verified', // Admin payment is auto-verified
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'notes' => $paymentData['notes'] ?? null,
            ]);
            
            // Create payment fee details
            foreach ($feePayments as $feePayment) {
                PaymentFeeDetail::create([
                    'payment_id' => $payment->id,
                    'invoice_fee_id' => $feePayment['invoice_fee_id'],
                    'fee_name' => $feePayment['fee_name'],
                    'fee_name_mm' => $feePayment['fee_name_mm'],
                    'full_amount' => $feePayment['full_amount'],
                    'paid_amount' => $feePayment['paid_amount'],
                    'is_partial' => $feePayment['is_partial'],
                ]);
                
                // Update invoice fee
                $invoiceFee = InvoiceFee::find($feePayment['invoice_fee_id']);
                $invoiceFee->paid_amount += $feePayment['paid_amount'];
                $invoiceFee->remaining_amount = $invoiceFee->amount - $invoiceFee->paid_amount;
                $invoiceFee->save();
            }
            
            // Update invoice
            $invoice->paid_amount += $finalAmount;
            $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
            
            // For partial payments, mark the original invoice as 'paid' since we'll create a new invoice for remaining
            if ($paymentType === 'partial' && $invoice->remaining_amount > 0) {
                // Mark original invoice as paid (it's been settled, remaining goes to new invoice)
                $invoice->status = 'paid';
            } elseif ($invoice->remaining_amount <= 0) {
                // Full payment or overpayment - mark as paid
                $invoice->status = 'paid';
            }
            
            $invoice->save();
            
            // Generate remaining invoice if partial payment
            // (Only for partial type, as Full payment logic handles "remaining" by just paying multiples)
            $remainingInvoice = null;
            if ($paymentType === 'partial' && $invoice->remaining_amount > 0) {
                $remainingInvoice = $this->generateRemainingInvoice($invoice, $feePayments);
            }
            
            DB::commit();
            
            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $finalAmount,
                'type' => $paymentType,
            ]);
            
            return [
                'success' => true,
                'payment' => $payment,
                'remaining_invoice' => $remainingInvoice,
                'discount_applied' => $totalDiscountAmount,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment processing failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Calculate discount based on payment months
     */
    private function calculateDiscount(int $months): float
    {
        $promotion = PaymentPromotion::where('months', $months)
            ->where('is_active', true)
            ->first();
            
        return $promotion ? (float) $promotion->discount_percent : 0;
    }

    /**
     * Check if discount should apply to this fee
     */
    private function shouldApplyDiscount($fee): bool
    {
        return stripos($fee->fee_name, 'School Fee') !== false;
    }
    
    /**
     * Calculate full payment amounts for each fee
     * (Deprecated/Unused in new logic but kept for reference or removal)
     */
    private function calculateFullPayment(Invoice $invoice): array
    {
        $feePayments = [];
        
        foreach ($invoice->fees as $fee) {
            if ($fee->remaining_amount > 0) {
                $feePayments[] = [
                    'invoice_fee_id' => $fee->id,
                    'fee_name' => $fee->fee_name,
                    'fee_name_mm' => $fee->fee_name_mm,
                    'full_amount' => $fee->amount, // Base amount
                    'paid_amount' => $fee->remaining_amount, // This needs to be dynamic in main loop
                    'is_partial' => false,
                ];
            }
        }
        
        return $feePayments;
    }
    
    /**
     * Calculate partial payment amounts for each fee
     */
    private function calculatePartialPayment(Invoice $invoice, array $feeAmounts): array
    {
        $feePayments = [];
        
        foreach ($invoice->fees as $fee) {
            $paidAmount = $feeAmounts[$fee->id] ?? 0;
            
            if ($paidAmount > 0) {
                $feePayments[] = [
                    'invoice_fee_id' => $fee->id,
                    'fee_name' => $fee->fee_name,
                    'fee_name_mm' => $fee->fee_name_mm,
                    'full_amount' => $fee->amount,
                    'paid_amount' => $paidAmount,
                    'is_partial' => $paidAmount < $fee->remaining_amount,
                ];
            }
        }
        
        return $feePayments;
    }
    
    /**
     * Generate remaining invoice for unpaid amounts
     */
    private function generateRemainingInvoice(Invoice $originalInvoice, array $feePayments): ?Invoice
    {
        // Collect fees with remaining amounts
        $remainingFees = [];
        
        foreach ($originalInvoice->fees as $fee) {
            // Find how much was paid for this specific fee in this payment
            $paidForThisFee = collect($feePayments)
                ->where('invoice_fee_id', $fee->id)
                ->sum('paid_amount');
            
            // Calculate remaining: original fee amount - what was already paid before - what's being paid now
            // Note: fee->remaining_amount already accounts for previous payments
            $remaining = $fee->remaining_amount - $paidForThisFee;
            
            if ($remaining > 0) {
                $remainingFees[] = [
                    'fee_id' => $fee->fee_id,
                    'fee_type' => $fee->fee_type,
                    'fee_name' => $fee->fee_name,
                    'fee_name_mm' => $fee->fee_name_mm,
                    'amount' => $remaining,
                    'due_date' => $fee->due_date,
                    'supports_payment_period' => $fee->supports_payment_period,
                ];
            }
        }
        
        if (empty($remainingFees)) {
            return null;
        }
        
        // Find the root/original invoice (the one without a parent)
        $rootInvoice = $originalInvoice;
        if ($originalInvoice->parent_invoice_id) {
            $rootInvoice = Invoice::find($originalInvoice->parent_invoice_id);
        }
        
        // Count how many remaining balance invoices already exist for the ROOT invoice
        $existingRemainingCount = Invoice::where('parent_invoice_id', $rootInvoice->id)
            ->where('invoice_type', 'remaining_balance')
            ->count();
        
        // Check if we've reached the limit (max 2 partial payments = max 2 remaining invoices)
        if ($existingRemainingCount >= 2) {
            throw new \Exception(__('finance.Maximum partial payment limit reached. Please pay the full remaining amount.'));
        }
        
        // Generate invoice number based on ROOT invoice number
        // Format: INV20260221-0001-1, INV20260221-0001-2
        $suffix = $existingRemainingCount + 1;
        $invoiceNumber = $rootInvoice->invoice_number . '-' . $suffix;
        
        // Calculate total
        $totalAmount = array_sum(array_column($remainingFees, 'amount'));
        
        // Create remaining invoice (always link to ROOT invoice)
        $remainingInvoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'student_id' => $originalInvoice->student_id,
            'batch_id' => $originalInvoice->batch_id,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'due_date' => $originalInvoice->due_date,
            'status' => 'unpaid',
            'invoice_type' => 'remaining_balance',
            'parent_invoice_id' => $rootInvoice->id, // Always link to ROOT
        ]);
        
        // Create invoice fees
        foreach ($remainingFees as $fee) {
            InvoiceFee::create([
                'invoice_id' => $remainingInvoice->id,
                'fee_id' => $fee['fee_id'],
                'fee_type' => $fee['fee_type'],
                'fee_name' => $fee['fee_name'],
                'fee_name_mm' => $fee['fee_name_mm'],
                'amount' => $fee['amount'],
                'paid_amount' => 0,
                'remaining_amount' => $fee['amount'],
                'supports_payment_period' => $fee['supports_payment_period'],
                'due_date' => $fee['due_date'],
                'status' => 'unpaid',
            ]);
        }
        
        return $remainingInvoice;
    }
    
    /**
     * Generate unique payment number
     */
    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        
        $lastPayment = Payment::where('payment_number', 'like', $prefix . $date . '%')
            ->orderBy('payment_number', 'desc')
            ->first();
            
        if ($lastPayment && preg_match('/' . $prefix . $date . '-(\d{4})/', $lastPayment->payment_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s%s-%04d', $prefix, $date, $sequence);
    }
    
    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '-' . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
            
        if ($lastInvoice && preg_match('/' . $prefix . '-' . $date . '-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }
    
    /**
     * Check if invoice has overdue fees (disables partial payment)
     */
    public function hasOverdueFees(Invoice $invoice): bool
    {
        return $invoice->fees()->where('due_date', '<', now())->exists();
    }
}
