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
            
            // Debug logging
            \Log::info('PaymentProcessingService - Processing Payment:', [
                'payment_type' => $paymentType,
                'payment_months_raw' => $paymentData['payment_months'] ?? 'not set',
                'fee_payment_months' => $feePaymentMonths,
                'invoice_id' => $invoice->id,
            ]);
            
            // For full payment with fee-specific months, use the maximum months for the payment record
            if ($paymentType === 'full' && !empty($feePaymentMonths)) {
                $paymentMonths = max($feePaymentMonths);
                \Log::info('Updated payment_months to max:', ['payment_months' => $paymentMonths]);
            }
            
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
                            'paid_amount' => $feeFinal,
                            'is_partial' => false,
                            'payment_months' => $months, // Store the number of months for this fee
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
                    'payment_months' => $feePayment['payment_months'] ?? 1,
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
        // Try to find exact match in promotions table
        $promotion = PaymentPromotion::where('months', $months)
            ->where('is_active', true)
            ->first();
            
        if ($promotion) {
            return (float) $promotion->discount_percent;
        }
        
        // If no exact match, interpolate based on tiers
        // Tiers: 1-2 months = 0%, 3 months = 1%, 6 months = 10%, 9 months = 15%, 12+ months = 20%
        if ($months < 3) {
            return 0;
        }
        
        if ($months == 3) {
            return 1;
        }
        
        if ($months < 6) {
            // Interpolate between 3 (1%) and 6 (10%)
            return 1 + (($months - 3) / 3) * 9;
        }
        
        if ($months == 6) {
            return 10;
        }
        
        if ($months < 9) {
            // Interpolate between 6 (10%) and 9 (15%)
            return 10 + (($months - 6) / 3) * 5;
        }
        
        if ($months == 9) {
            return 15;
        }
        
        if ($months < 12) {
            // Interpolate between 9 (15%) and 12 (20%)
            return 15 + (($months - 9) / 3) * 5;
        }
        
        // 12 or more months = 20%
        return 20;
    }

    /**
     * Check if discount should apply to this fee
     */
    private function shouldApplyDiscount($fee): bool
    {
        // Load fee type if not already loaded
        if (!$fee->relationLoaded('feeType')) {
            $fee->load('feeType');
        }
        
        $feeType = $fee->feeType;
        
        if (!$feeType) {
            return false;
        }
        
        // School Fee (SCHOOL_FEE) always supports discounts
        $isSchoolFee = $feeType->code === 'SCHOOL_FEE';
        
        // Check discount_status for other fee types
        return $isSchoolFee || ($feeType->discount_status === true || $feeType->discount_status === 1);
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
