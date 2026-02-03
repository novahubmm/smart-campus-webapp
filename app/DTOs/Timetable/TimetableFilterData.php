<?php

namespace App\DTOs\Timetable;

class TimetableFilterData
{
    public function __construct(
        public readonly ?string $batch_id,
        public readonly ?string $grade_id,
        public readonly ?string $class_id,
        public readonly ?string $teacher_profile_id,
        public readonly ?string $student_id,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['batch_id'] ?? null,
            $payload['grade_id'] ?? null,
            $payload['class_id'] ?? null,
            $payload['teacher_profile_id'] ?? null,
            $payload['student_id'] ?? null,
        );
    }
}
