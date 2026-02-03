<?php

namespace App\DTOs\Finance;

class FeeFilterData
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?string $grade_id,
        public readonly ?string $class_id,
        public readonly ?string $month,
        public readonly ?string $search,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            status: $payload['status'] ?? null,
            grade_id: $payload['grade_id'] ?? null,
            class_id: $payload['class_id'] ?? null,
            month: $payload['month'] ?? null,
            search: $payload['search'] ?? null,
        );
    }
}
