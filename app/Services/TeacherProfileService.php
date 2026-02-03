<?php

namespace App\Services;

use App\DTOs\TeacherProfile\TeacherProfileStoreData;
use App\DTOs\TeacherProfile\TeacherProfileUpdateData;
use App\DTOs\User\UserStoreData;
use App\Enums\RoleEnum;
use App\Interfaces\TeacherProfileRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherProfileService
{
    public function __construct(
        private readonly TeacherProfileRepositoryInterface $teacherProfileRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->teacherProfileRepository->paginate($filters);
    }

    public function totals(): array
    {
        return $this->teacherProfileRepository->totals();
    }

    public function store(TeacherProfileStoreData $data): TeacherProfile
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create(new UserStoreData(
                name: $data->name,
                email: $data->email,
                phone: $data->phone,
                nrc: $data->nrc,
                password: $data->password,
                roles: [RoleEnum::TEACHER->value],
                isActive: $data->isActive,
            ));

            $this->userRepository->syncRoles($user, [RoleEnum::TEACHER->value]);

            return $this->teacherProfileRepository->create($data, $user->id);
        });
    }

    public function update(TeacherProfileUpdateData $data): TeacherProfile
    {
        return DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = $data->profile->user;

            $user->update([
                'name' => $data->name,
                'email' => $data->email,
                'phone' => $data->phone,
                'nrc' => $data->nrc,
                'is_active' => $data->isActive,
            ]);

            if ($data->password) {
                $user->update(['password' => Hash::make($data->password)]);
                $user->tokens()->delete();
            }

            $this->userRepository->syncRoles($user, [RoleEnum::TEACHER->value]);

            return $this->teacherProfileRepository->update($data);
        });
    }
}
