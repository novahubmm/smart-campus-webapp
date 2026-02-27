<?php

namespace App\Repositories;

use App\DTOs\Department\DepartmentStoreData;
use App\DTOs\Department\DepartmentUpdateData;
use App\Interfaces\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Department::query()
            ->withCount([
                'staffProfiles' => fn($q) => $q->where('status', 'active'),
                'teacherProfiles' => fn($q) => $q->where('status', 'active')
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->latest()->paginate(12)->withQueryString();
    }

    public function totals(): array
    {
        return [
            'all' => Department::count(),
            'active' => Department::where('is_active', true)->count(),
        ];
    }

    public function create(DepartmentStoreData $data): Department
    {
        return Department::create([
            'code' => $data->code,
            'name' => $data->name,
            'is_active' => $data->isActive,
        ]);
    }

    public function update(DepartmentUpdateData $data): Department
    {
        $data->department->update([
            'code' => $data->code,
            'name' => $data->name,
            'is_active' => $data->isActive,
        ]);

        return $data->department->refresh();
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}
