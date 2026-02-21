<?php

namespace App\Repositories\Finance;

use App\Models\PaymentSystem\Payment;
use Illuminate\Support\Collection;

class PaymentRepository
{
    /**
     * Create a new payment record
     */
    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Get payments for a specific student
     */
    public function getPaymentsByStudent(int $studentId): Collection
    {
        return Payment::where('student_id', $studentId)
            ->with(['paymentMethod', 'paymentProof', 'recordedBy'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Generate a unique payment number
     */
    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        
        // Get the last payment number for today
        $lastPayment = Payment::where('payment_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Get payment by ID
     */
    public function find(string $id): ?Payment
    {
        return Payment::with(['student', 'paymentMethod', 'paymentProof', 'recordedBy'])
            ->find($id);
    }

    /**
     * Get payment by payment proof ID
     */
    public function getByPaymentProofId(string $paymentProofId): ?Payment
    {
        return Payment::where('payment_proof_id', $paymentProofId)
            ->with(['student', 'paymentMethod', 'recordedBy'])
            ->first();
    }
}
