<?php

namespace App\DTOs\Department;

class DepartmentStoreData
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly bool $isActive,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            code: $validated['code'],
            name: $validated['name'],
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }
}
