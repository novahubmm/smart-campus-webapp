<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianPaymentRepositoryInterface
{
    public function getFeeStructure(StudentProfile $student, ?string $academicYear = null): array;
    
    public function getPaymentMethods(?string $type = null, bool $activeOnly = true): array;
    
    public function submitPayment(StudentProfile $student, array $paymentData): array;
    
    public function getPaymentOptions(): array;
    
    public function getPaymentHistory(StudentProfile $student, ?string $status = null, int $limit = 10, int $page = 1): array;
}
