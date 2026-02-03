<?php

namespace App\Repositories;

use App\DTOs\User\UserStoreData;
use App\DTOs\User\UserUpdateData;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = User::query()->with([
            'roles',
            'staffProfile',
            'teacherProfile',
            'studentProfile',
            'guardianProfile',
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function create(UserStoreData $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'nrc' => $data->nrc,
            'password' => Hash::make($data->password),
            'is_active' => $data->isActive,
        ]);
    }

    public function update(UserUpdateData $data): User
    {
        $data->user->update([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'nrc' => $data->nrc,
            'is_active' => $data->isActive,
        ]);

        return $data->user->refresh();
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    public function setActive(User $user, bool $active): User
    {
        $user->is_active = $active;
        $user->save();

        return $user->refresh();
    }

    public function resetPassword(User $user, string $password): User
    {
        $user->password = Hash::make($password);
        $user->save();

        return $user->refresh();
    }
}
