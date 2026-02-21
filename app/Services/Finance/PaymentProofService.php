<?php

namespace App\Services\Finance;

use App\Models\PaymentSystem\Payment;
use App\Models\PaymentProof;
use App\Repositories\Finance\InvoiceRepository;
use App\Repositories\Finance\PaymentProofRepository;
use App\Repositories\Finance\PaymentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProofService
{
    public function __construct(
        private PaymentProofRepository $paymentProofRepo,
        private InvoiceRepository $invoiceRepo,
        private PaymentRepository $paymentRepo,
        private NotificationService $notificationService
    ) {}

    /**
     * Approve a payment proof and update all related records
     */
    public function approvePaymentProof(string $paymentProofId, string $adminId): Payment
    {
        return DB::transaction(function () use ($paymentProofId, $adminId) {
            // 1. Load payment proof with relationships
            $paymentProof = $this->paymentProofRepo->find($paymentProofId);
            
            if (!$paymentProof) {
                throw new \Exception('Payment proof not found');
            }

            if ($paymentProof->status !== 'pending_verification') {
                throw new \Exception('Payment proof has already been processed');
            }

            // 2. Update payment proof status
            $paymentProof->approve($adminId);

            // 3. Get all invoices from fee_ids
            $invoices = $this->invoiceRepo->getInvoicesByIds($paymentProof->fee_ids ?? []);

            // 4. Mark all invoices as paid
            foreach ($invoices as $invoice) {
                $invoice->markAsPaid($paymentProof->id, $paymentProof->payment_date);
            }

            // 5. Generate payment number
            $paymentNumber = $this->paymentRepo->generatePaymentNumber();

            // 6. Create Payment record
            $payment = $this->paymentRepo->createPayment([
                'payment_number' => $paymentNumber,
                'student_id' => $paymentProof->student_id,
                'payment_proof_id' => $paymentProof->id,
                'payment_method_id' => $paymentProof->payment_method_id,
                'amount' => $paymentProof->payment_amount,
                'payment_date' => $paymentProof->payment_date,
                'invoice_ids' => $paymentProof->fee_ids,
                'recorded_by' => $adminId,
                'notes' => $paymentProof->notes,
            ]);

            // 7. Update finance/accounting system (if needed)
            // This would integrate with your existing finance system
            // $this->updateFinanceRecords($payment);

            Log::info('Payment proof approved', [
                'payment_proof_id' => $paymentProofId,
                'payment_id' => $payment->id,
                'admin_id' => $adminId,
            ]);

            return $payment;
        });
    }

    /**
     * Reject a payment proof and notify guardian
     */
    public function rejectPaymentProof(string $paymentProofId, string $adminId, string $reason): PaymentProof
    {
        return DB::transaction(function () use ($paymentProofId, $adminId, $reason) {
            // 1. Load payment proof
            $paymentProof = $this->paymentProofRepo->find($paymentProofId);
            
            if (!$paymentProof) {
                throw new \Exception('Payment proof not found');
            }

            if ($paymentProof->status !== 'pending_verification') {
                throw new \Exception('Payment proof has already been processed');
            }

            // 2. Update payment proof status
            $paymentProof->reject($adminId, $reason);

            // 3. Ensure all associated invoices remain unpaid
            // (They should already be unpaid, but this is a safety check)
            $invoices = $this->invoiceRepo->getInvoicesByIds($paymentProof->fee_ids ?? []);
            foreach ($invoices as $invoice) {
                if ($invoice->status === 'pending_verification') {
                    $invoice->markAsUnpaid();
                }
            }

            Log::info('Payment proof rejected', [
                'payment_proof_id' => $paymentProofId,
                'admin_id' => $adminId,
                'reason' => $reason,
            ]);

            return $paymentProof;
        });
    }

    /**
     * Send rejection notification after transaction completes
     */
    public function sendRejectionNotification(PaymentProof $paymentProof): void
    {
        try {
            $this->notificationService->sendRejectionNotification(
                $paymentProof,
                $paymentProof->rejection_reason
            );
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'payment_proof_id' => $paymentProof->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate payment proof data before submission
     */
    public function validatePaymentProof(string $studentId, array $feeIds, float $paymentAmount): array
    {
        $errors = [];

        // 1. Validate fee_ids ownership
        $invoices = $this->invoiceRepo->getInvoicesByIds($feeIds);
        
        foreach ($invoices as $invoice) {
            if ($invoice->student_id != $studentId) {
                $errors[] = "Invoice {$invoice->invoice_number} does not belong to this student";
            }
            
            if ($invoice->status !== 'unpaid') {
                $errors[] = "Invoice {$invoice->invoice_number} is not in unpaid status";
            }
        }

        // 2. Validate amount sum
        $totalAmount = $invoices->sum('total_amount');
        if (abs($totalAmount - $paymentAmount) > 0.01) { // Allow for floating point precision
            $errors[] = "Payment amount ({$paymentAmount}) does not match sum of invoices ({$totalAmount})";
        }

        return $errors;
    }

    /**
     * Get payment proof details for modal display
     */
    public function getPaymentProofDetails(string $paymentProofId): array
    {
        $paymentProof = $this->paymentProofRepo->getProofDetails($paymentProofId);
        
        if (!$paymentProof) {
            throw new \Exception('Payment proof not found');
        }

        // Get related invoices
        $invoices = $this->invoiceRepo->getInvoicesByIds($paymentProof->fee_ids ?? []);

        return [
            'id' => $paymentProof->id,
            'student' => [
                'id' => $paymentProof->student->id,
                'name' => $paymentProof->student->user->name,
                'identifier' => $paymentProof->student->student_identifier,
                'grade' => $paymentProof->student->grade?->name,
                'class' => $paymentProof->student->classModel?->name,
            ],
            'payment_amount' => $paymentProof->payment_amount,
            'payment_months' => $paymentProof->payment_months,
            'payment_date' => $paymentProof->payment_date->format('Y-m-d'),
            'payment_method' => $paymentProof->paymentMethod?->name,
            'receipt_image' => $paymentProof->receipt_image 
                ? asset('storage/' . $paymentProof->receipt_image) 
                : null,
            'notes' => $paymentProof->notes,
            'status' => $paymentProof->status,
            'submitted_at' => $paymentProof->created_at->format('Y-m-d H:i:s'),
            'verified_by' => $paymentProof->verifiedBy?->name,
            'verified_at' => $paymentProof->verified_at?->format('Y-m-d H:i:s'),
            'rejection_reason' => $paymentProof->rejection_reason,
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date?->format('Y-m-d'),
                    'fee_type' => $invoice->feeStructure?->feeType?->name ?? 'Unknown',
                ];
            })->toArray(),
        ];
    }
}
