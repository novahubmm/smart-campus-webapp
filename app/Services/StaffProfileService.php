<?php

namespace App\Services;

use App\DTOs\StaffProfile\StaffProfileStoreData;
use App\DTOs\StaffProfile\StaffProfileUpdateData;
use App\DTOs\User\UserStoreData;
use App\Enums\RoleEnum;
use App\Interfaces\StaffProfileRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffProfileService
{
    public function __construct(
        private readonly StaffProfileRepositoryInterface $staffProfileRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->staffProfileRepository->paginate($filters);
    }

    public function totals(): array
    {
        return $this->staffProfileRepository->totals();
    }

    public function store(StaffProfileStoreData $data): StaffProfile
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create(new UserStoreData(
                name: $data->name,
                email: $data->email,
                phone: $data->phone,
                nrc: $data->nrc,
                password: $data->password,
                roles: [RoleEnum::STAFF->value],
                isActive: $data->isActive,
            ));

            $this->userRepository->syncRoles($user, [RoleEnum::STAFF->value]);

            return $this->staffProfileRepository->create($data, $user->id);
        });
    }

    public function update(StaffProfileUpdateData $data): StaffProfile
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

            $this->userRepository->syncRoles($user, [RoleEnum::STAFF->value]);

            return $this->staffProfileRepository->update($data);
        });
    }
}
