<?php

namespace App\DTOs\Event;

class EventFilterData
{
    public function __construct(
        public readonly ?string $category_id,
        public readonly ?string $status,
        public readonly ?string $period,
        public readonly ?string $month,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            category_id: $payload['category_id'] ?? null,
            status: $payload['status'] ?? null,
            period: $payload['period'] ?? null,
            month: $payload['month'] ?? null,
        );
    }
}
