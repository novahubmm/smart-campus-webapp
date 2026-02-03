<?php

namespace App\DTOs\Timetable;

class TimetableData
{
    public function __construct(
        public readonly string $batch_id,
        public readonly string $grade_id,
        public readonly string $class_id,
        public readonly ?string $name,
        public readonly string $status,
        public readonly ?string $effective_from,
        public readonly ?string $effective_to,
        public readonly ?int $minutes_per_period,
        public readonly ?int $break_duration,
        public readonly ?string $school_start_time,
        public readonly ?string $school_end_time,
        public readonly ?array $week_days,
        public readonly ?int $number_of_periods_per_day,
        public readonly ?array $custom_period_times,
        public readonly bool $use_custom_settings,
        public readonly int $version,
        public readonly ?string $created_by,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['batch_id'],
            $payload['grade_id'],
            $payload['class_id'],
            $payload['name'] ?? null,
            $payload['status'] ?? 'draft',
            $payload['effective_from'] ?? null,
            $payload['effective_to'] ?? null,
            isset($payload['minutes_per_period']) ? (int) $payload['minutes_per_period'] : null,
            isset($payload['break_duration']) ? (int) $payload['break_duration'] : null,
            $payload['school_start_time'] ?? null,
            $payload['school_end_time'] ?? null,
            $payload['week_days'] ?? null,
            isset($payload['number_of_periods_per_day']) ? (int) $payload['number_of_periods_per_day'] : null,
            $payload['custom_period_times'] ?? null,
            (bool) ($payload['use_custom_settings'] ?? false),
            isset($payload['version']) ? (int) $payload['version'] : 1,
            $payload['created_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'batch_id' => $this->batch_id,
            'grade_id' => $this->grade_id,
            'class_id' => $this->class_id,
            'name' => $this->name,
            'status' => $this->status,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'minutes_per_period' => $this->minutes_per_period,
            'break_duration' => $this->break_duration,
            'school_start_time' => $this->school_start_time,
            'school_end_time' => $this->school_end_time,
            'week_days' => $this->week_days,
            'version' => $this->version,
            'created_by' => $this->created_by,
        ];
    }
}
