<?php

namespace App\DTOs\Announcement;

class AnnouncementFilterData
{
    public function __construct(
        public readonly ?string $type,
        public readonly ?string $priority,
        public readonly ?string $status,
        public readonly ?string $period,
        public readonly ?string $role,
        public readonly ?string $target,
        public readonly ?string $month,
    ) {
    }

    public static function from(array $payload): self
    {
        return new self(
            type: $payload['type'] ?? null,
            priority: $payload['priority'] ?? null,
            status: $payload['status'] ?? null,
            period: $payload['period'] ?? null,
            role: $payload['role'] ?? null,
            target: $payload['target'] ?? null,
            month: $payload['month'] ?? null,
        );
    }
}
