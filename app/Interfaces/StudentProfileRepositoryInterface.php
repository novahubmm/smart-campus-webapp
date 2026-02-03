<?php

namespace App\Interfaces;

use App\DTOs\StudentProfile\StudentProfileStoreData;
use App\DTOs\StudentProfile\StudentProfileUpdateData;
use App\Models\StudentProfile;

interface StudentProfileRepositoryInterface
{
    public function create(StudentProfileStoreData $data, string $userId): StudentProfile;

    public function update(StudentProfileUpdateData $data): StudentProfile;
}
