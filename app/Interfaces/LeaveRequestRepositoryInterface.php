<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface LeaveRequestRepositoryInterface
{
    public function getPendingStaffTeacher(array $filters = []): Collection;

    public function getHistoryStaffTeacher(array $filters = []): Collection;

    public function getPendingStudents(array $filters = []): Collection;

    public function getHistoryStudents(array $filters = []): Collection;

    public function getForUser(string $userId, array $filters = []): Collection;

    public function create(array $data): array;
}
