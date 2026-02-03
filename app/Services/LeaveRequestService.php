<?php

namespace App\Services;

use App\Interfaces\LeaveRequestRepositoryInterface;
use Illuminate\Support\Collection;

class LeaveRequestService
{
    public function __construct(private readonly LeaveRequestRepositoryInterface $repository) {}

    public function staffPending(array $filters = []): Collection
    {
        return $this->repository->getPendingStaffTeacher($filters);
    }

    public function staffHistory(array $filters = []): Collection
    {
        return $this->repository->getHistoryStaffTeacher($filters);
    }

    public function studentPending(array $filters = []): Collection
    {
        return $this->repository->getPendingStudents($filters);
    }

    public function studentHistory(array $filters = []): Collection
    {
        return $this->repository->getHistoryStudents($filters);
    }

    public function myHistory(string $userId, array $filters = []): Collection
    {
        return $this->repository->getForUser($userId, $filters);
    }

    public function submit(array $data): array
    {
        return $this->repository->create($data);
    }
}
