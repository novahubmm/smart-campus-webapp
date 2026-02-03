<?php

namespace App\Interfaces;

use App\DTOs\TeacherProfile\TeacherProfileStoreData;
use App\DTOs\TeacherProfile\TeacherProfileUpdateData;
use App\Models\TeacherProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeacherProfileRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function totals(): array;

    public function create(TeacherProfileStoreData $data, string $userId): TeacherProfile;

    public function update(TeacherProfileUpdateData $data): TeacherProfile;
}
