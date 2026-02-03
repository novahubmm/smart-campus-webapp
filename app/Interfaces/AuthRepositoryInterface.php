<?php

namespace App\Interfaces;

use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;
use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Register a new user
     */
    public function register(RegisterData $data): User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create access token for user
     */
    public function createToken(User $user, string $deviceName): string;

    /**
     * Revoke user tokens
     */
    public function revokeTokens(User $user): void;
}
