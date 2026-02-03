<?php

namespace App\DTOs\Exam;

class ExamFilterData
{
    public function __construct(
        public readonly ?string $exam_type_id,
        public readonly ?string $batch_id,
        public readonly ?string $grade_id,
        public readonly ?string $class_id,
        public readonly ?string $status,
        public readonly ?string $month,
        public readonly int $perPage = 10,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            exam_type_id: $payload['exam_type_id'] ?? null,
            batch_id: $payload['batch_id'] ?? null,
            grade_id: $payload['grade_id'] ?? null,
            class_id: $payload['class_id'] ?? null,
            status: $payload['status'] ?? null,
            month: $payload['month'] ?? null,
            perPage: (int) ($payload['per_page'] ?? 10),
        );
    }
}
