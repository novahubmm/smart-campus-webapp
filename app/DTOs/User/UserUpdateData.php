<?php

namespace App\DTOs\User;

use App\Models\User;

class UserUpdateData
{
    public function __construct(
        public readonly User $user,
        public readonly string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $nrc,
        public readonly bool $isActive,
    ) {}

    public static function from(User $user, array $validated): self
    {
        return new self(
            user: $user,
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            nrc: $validated['nrc'] ?? null,
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }
}
