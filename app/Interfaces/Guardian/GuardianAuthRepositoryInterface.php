<?php

namespace App\Interfaces\Guardian;

use App\Models\User;

interface GuardianAuthRepositoryInterface
{
    public function findGuardianByLogin(string $login): ?User;

    public function createToken(User $user, string $deviceName): string;

    public function revokeTokens(User $user): void;

    public function getGuardianStudents(User $user): \Illuminate\Database\Eloquent\Collection;

    public function createPasswordResetOtp(string $identifier): array;

    public function resendOtp(string $identifier): array;

    public function verifyOtp(string $identifier, string $otp): ?string;

    public function resetPassword(string $resetToken, string $password): bool;
}
