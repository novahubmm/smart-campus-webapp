<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianLeaveRequestRepositoryInterface
{
    public function getLeaveRequests(StudentProfile $student, ?string $status = null): array;

    public function getLeaveRequestDetail(string $requestId): array;

    public function getLeaveRequestDetailForStudent(string $requestId, string $studentId): ?array;

    public function createLeaveRequest(StudentProfile $student, string $guardianId, array $data): array;

    public function createBulkLeaveRequests(array $studentIds, string $guardianId, array $data): array;

    public function updateLeaveRequest(string $requestId, array $data): array;

    public function deleteLeaveRequest(string $requestId): bool;

    public function getLeaveStats(StudentProfile $student): array;

    public function getLeaveTypes(): array;
}
