<?php

namespace App\Services;

use App\DTOs\Auth\LoginData;
use App\DTOs\Auth\RegisterData;
use App\Enums\RoleEnum;
use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository
    ) {}

    /**
     * Register a new user
     *
     * @throws ValidationException
     */
    public function register(RegisterData $data): array
    {
        // Check if email already exists
        if ($this->authRepository->findByEmail($data->email)) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        // Create user
        $user = $this->authRepository->register($data);

        // Assign default role
        $user->assignRole(RoleEnum::STUDENT->value);

        // Block inactive users
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('Your account is deactivate, connect to admin.')],
            ]);
        }

        // Create token
        $token = $this->authRepository->createToken($user, $data->device_name);

        return [
            'user' => $user->load('roles'),
            'token' => $token,
        ];
    }

    /**
     * Login user
     *
     * @throws ValidationException
     */
    public function login(LoginData $data): array
    {
        // Find user by email
        $user = $this->authRepository->findByEmail($data->email);

        // Validate credentials
        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $this->authRepository->createToken($user, $data->device_name);

        return [
            'user' => $user->load('roles', 'permissions'),
            'token' => $token,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $this->authRepository->revokeTokens($user);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(User $user): User
    {
        return $user->load('roles', 'permissions');
    }
}
