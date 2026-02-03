<?php

namespace App\Services;

use App\DTOs\Department\DepartmentStoreData;
use App\DTOs\Department\DepartmentUpdateData;
use App\Interfaces\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    public function __construct(private readonly DepartmentRepositoryInterface $departmentRepository) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->departmentRepository->paginate($filters);
    }

    public function totals(): array
    {
        return $this->departmentRepository->totals();
    }

    public function store(DepartmentStoreData $data): Department
    {
        return DB::transaction(fn() => $this->departmentRepository->create($data));
    }

    public function update(DepartmentUpdateData $data): Department
    {
        return DB::transaction(fn() => $this->departmentRepository->update($data));
    }

    public function delete(Department $department): void
    {
        DB::transaction(fn() => $this->departmentRepository->delete($department));
    }
}
