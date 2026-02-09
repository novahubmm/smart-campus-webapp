<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;
use Illuminate\Pagination\LengthAwarePaginator;

interface GuardianFeeRepositoryInterface
{
    public function getPendingFee(StudentProfile $student): ?array;

    public function getFeeDetails(string $feeId, StudentProfile $student): ?array;

    public function getAllFees(StudentProfile $student, array $filters): LengthAwarePaginator;

    public function initiatePayment(string $feeId, StudentProfile $student, array $data): array;

    public function getPaymentHistory(StudentProfile $student, array $filters): LengthAwarePaginator;

    // Enhanced methods for receipts and summaries
    public function generateReceipt(string $paymentId, StudentProfile $student): array;

    public function downloadReceipt(string $paymentId, StudentProfile $student): string;

    public function getPaymentSummary(StudentProfile $student, ?int $year = null): array;
}
