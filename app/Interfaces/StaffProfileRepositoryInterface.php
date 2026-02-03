<?php

namespace App\Interfaces;

use App\DTOs\StaffProfile\StaffProfileStoreData;
use App\DTOs\StaffProfile\StaffProfileUpdateData;
use App\Models\StaffProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StaffProfileRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function totals(): array;

    public function create(StaffProfileStoreData $data, string $userId): StaffProfile;

    public function update(StaffProfileUpdateData $data): StaffProfile;
}
