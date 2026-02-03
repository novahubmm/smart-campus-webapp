<?php

namespace App\Interfaces\Teacher;

use App\Models\User;

interface TeacherAuthRepositoryInterface
{
    public function findTeacherByEmail(string $email): ?User;

    public function findTeacherByLogin(string $login): ?User;

    public function createToken(User $user, string $deviceName): string;

    public function revokeTokens(User $user): void;
}
