<?php

namespace App\DTOs\User;

use App\Models\User;

class UserPasswordResetData
{
    public function __construct(
        public readonly User $user,
        public readonly string $password,
    ) {}

    public static function from(User $user, array $validated): self
    {
        return new self(
            user: $user,
            password: $validated['password'],
        );
    }
}
