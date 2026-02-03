<?php

namespace App\Services;

use App\DTOs\User\UserPasswordResetData;
use App\DTOs\User\UserStoreData;
use App\DTOs\User\UserUpdateData;
use App\Enums\RoleEnum;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters);
    }

    /**
     * @throws ValidationException
     */
    public function store(UserStoreData $data, User $actor): User
    {
        $this->assertCanAssignRoles($actor, $data->roles);

        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create($data);
            $this->userRepository->syncRoles($user, $data->roles);
            return $user->load('roles');
        });
    }

    public function update(UserUpdateData $data, User $actor): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->update($data);
            return $user->load('roles');
        });
    }

    public function setActive(User $user, bool $active): User
    {
        $updated = $this->userRepository->setActive($user, $active);

        // revoke tokens when deactivating
        if (!$active) {
            $updated->tokens()->delete();
        }

        return $updated->load('roles');
    }

    public function resetPassword(UserPasswordResetData $data): User
    {
        $user = $this->userRepository->resetPassword($data->user, $data->password);
        $user->tokens()->delete();

        return $user;
    }

    /**
     * @param string[] $roles
     *
     * @throws ValidationException
     */
    private function assertCanAssignRoles(User $actor, array $roles): void
    {
        if (in_array(RoleEnum::ADMIN->value, $roles, true) && !$actor->hasRole(RoleEnum::ADMIN->value)) {
            throw ValidationException::withMessages([
                'roles' => [__('You are not allowed to assign the admin role.')],
            ]);
        }
    }
}
