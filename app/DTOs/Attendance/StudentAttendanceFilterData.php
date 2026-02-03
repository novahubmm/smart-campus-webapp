<?php

namespace App\DTOs\Attendance;

class StudentAttendanceFilterData
{
    public function __construct(
        public readonly ?string $class_id,
        public readonly ?string $grade_id,
        public readonly ?string $search,
        public readonly ?string $status,
        public readonly ?string $date,
        public readonly ?string $month,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['class_id'] ?? null,
            $payload['grade_id'] ?? null,
            $payload['search'] ?? null,
            $payload['status'] ?? null,
            $payload['date'] ?? null,
            $payload['month'] ?? null,
        );
    }
}
