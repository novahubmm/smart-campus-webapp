<?php

namespace App\DTOs\User;

class UserStoreData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $nrc,
        public readonly string $password,
        /** @var string[] */
        public readonly array $roles,
        public readonly bool $isActive,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'] ?? null,
            phone: $validated['phone'] ?? null,
            nrc: $validated['nrc'] ?? null,
            password: $validated['password'],
            roles: $validated['roles'],
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }
}
