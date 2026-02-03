<?php

namespace App\Interfaces;

use App\DTOs\User\UserStoreData;
use App\DTOs\User\UserUpdateData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function create(UserStoreData $data): User;

    public function update(UserUpdateData $data): User;

    public function syncRoles(User $user, array $roles): void;

    public function setActive(User $user, bool $active): User;

    public function resetPassword(User $user, string $password): User;
}
