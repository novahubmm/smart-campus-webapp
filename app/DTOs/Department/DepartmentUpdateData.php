<?php

namespace App\DTOs\Department;

use App\Models\Department;

class DepartmentUpdateData
{
    public function __construct(
        public readonly Department $department,
        public readonly string $code,
        public readonly string $name,
        public readonly bool $isActive,
    ) {}

    public static function from(Department $department, array $validated): self
    {
        return new self(
            department: $department,
            code: $validated['code'],
            name: $validated['name'],
            isActive: (bool) ($validated['is_active'] ?? false),
        );
    }
}
