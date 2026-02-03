<?php

namespace App\Interfaces;

use App\DTOs\Department\DepartmentStoreData;
use App\DTOs\Department\DepartmentUpdateData;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DepartmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function totals(): array;

    public function create(DepartmentStoreData $data): Department;

    public function update(DepartmentUpdateData $data): Department;

    public function delete(Department $department): void;
}
