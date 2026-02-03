<?php

namespace App\Repositories\Teacher;

use App\Interfaces\Teacher\TeacherAuthRepositoryInterface;
use App\Models\User;

class TeacherAuthRepository implements TeacherAuthRepositoryInterface
{
    public function findTeacherByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->whereHas('teacherProfile')
            ->first();
    }

    public function findTeacherByLogin(string $login): ?User
    {
        return User::where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('phone', $login);
            })
            ->whereHas('teacherProfile')
            ->first();
    }

    public function createToken(User $user, string $deviceName): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
