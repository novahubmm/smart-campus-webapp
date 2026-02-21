<?php

namespace App\Services;

use App\DTOs\StudentProfile\StudentProfileStoreData;
use App\DTOs\StudentProfile\StudentProfileUpdateData;
use App\DTOs\User\UserStoreData;
use App\DTOs\User\UserUpdateData;
use App\Enums\RoleEnum;
use App\Interfaces\StudentProfileRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentProfileService
{
    public function __construct(
        private readonly StudentProfileRepositoryInterface $studentProfileRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function store(StudentProfileStoreData $data): StudentProfile
    {
        return DB::transaction(function () use ($data) {
            $user = $this->resolveUserForStore($data);

            $this->userRepository->syncRoles($user, [RoleEnum::STUDENT->value]);

            return $this->studentProfileRepository->create($data, $user->id);
        });
    }

    public function update(StudentProfileUpdateData $data): StudentProfile
    {
        return DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = $data->profile->user;

            $this->userRepository->update(new UserUpdateData(
                user: $user,
                name: $data->name,
                email: $data->email ?: null,
                phone: $data->phone ?: null,
                nrc: $data->nrc ?: null,
                isActive: $data->isActive,
            ));

            if ($data->password) {
                $this->userRepository->resetPassword($user, $data->password);
                $user->tokens()->delete();
            }

            $this->userRepository->syncRoles($user, [RoleEnum::STUDENT->value]);

            return $this->studentProfileRepository->update($data);
        });
    }

    private function resolveUserForStore(StudentProfileStoreData $data): User
    {
        if ($data->userId) {
            $user = User::findOrFail($data->userId);

            $this->userRepository->update(new UserUpdateData(
                user: $user,
                name: $data->name ?? $user->name,
                email: ($data->email ?: $user->email) ?: null,
                phone: ($data->phone ?: $user->phone) ?: null,
                nrc: ($data->nrc ?: $user->nrc) ?: null,
                isActive: $data->isActive,
            ));

            if ($data->password) {
                $this->userRepository->resetPassword($user, $data->password);
                $user->tokens()->delete();
            }

            return $user->refresh();
        }

        return $this->userRepository->create(new UserStoreData(
            name: $data->name ?? '',
            email: $data->email ?: null,
            phone: $data->phone ?: null,
            nrc: $data->nrc ?: null,
            password: $data->password ?? Str::random(12),
            roles: [RoleEnum::STUDENT->value],
            isActive: $data->isActive,
        ));
    }
}
