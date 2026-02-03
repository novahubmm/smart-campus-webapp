<?php

namespace App\Repositories;

use App\DTOs\Auth\RegisterData;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Register a new user
     */
    public function register(RegisterData $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'nrc' => $data->nrc,
            'password' => Hash::make($data->password),
        ]);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create access token for user
     */
    public function createToken(User $user, string $deviceName): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Revoke user tokens
     */
    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}
