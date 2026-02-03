<?php

namespace App\DTOs\Timetable;

class PeriodData
{
    public function __construct(
        public readonly string $day_of_week,
        public readonly int $period_number,
        public readonly string $starts_at,
        public readonly string $ends_at,
        public readonly bool $is_break,
        public readonly ?string $subject_id,
        public readonly ?string $teacher_profile_id,
        public readonly ?string $room_id,
        public readonly ?string $notes,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['day_of_week'],
            (int) $payload['period_number'],
            $payload['starts_at'],
            $payload['ends_at'],
            (bool) ($payload['is_break'] ?? false),
            $payload['subject_id'] ?? null,
            $payload['teacher_profile_id'] ?? null,
            $payload['room_id'] ?? null,
            $payload['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'day_of_week' => $this->day_of_week,
            'period_number' => $this->period_number,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_break' => $this->is_break,
            'subject_id' => $this->subject_id,
            'teacher_profile_id' => $this->teacher_profile_id,
            'room_id' => $this->room_id,
            'notes' => $this->notes,
        ];
    }
}
