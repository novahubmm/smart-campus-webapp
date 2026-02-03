<?php

namespace App\DTOs\Academic;

use Illuminate\Contracts\Support\Arrayable;

class SchoolClassData implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly string $grade_id,
        public readonly ?string $batch_id = null,
        public readonly ?string $room_id = null,
        public readonly ?string $teacher_id = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name: $data['name'],
            grade_id: $data['grade_id'],
            batch_id: $data['batch_id'] ?? null,
            room_id: $data['room_id'] ?? null,
            teacher_id: $data['teacher_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'grade_id' => $this->grade_id,
            'batch_id' => $this->batch_id,
            'room_id' => $this->room_id,
            'teacher_id' => $this->teacher_id,
        ], fn($value) => $value !== null);
    }
}
