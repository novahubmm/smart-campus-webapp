<?php

namespace App\DTOs\Event;

use Illuminate\Support\Arr;

class EventData
{
    public function __construct(
        public readonly string $event_category_id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $type,
        public readonly string $start_date,
        public readonly ?string $end_date,
        public readonly ?string $start_time,
        public readonly ?string $end_time,
        public readonly ?string $venue,
        public readonly ?string $organized_by,
        public readonly ?string $banner_image,
        public readonly string $status,
        public readonly array $target_roles = [],
        public readonly array $target_grades = ['all'],
        public readonly array $target_teacher_grades = ['all'],
        public readonly array $target_guardian_grades = ['all'],
        public readonly array $target_departments = ['all'],
        public readonly array $schedules = [],
    ) {
    }

    public static function from(array $payload, ?string $organizedBy = null): self
    {
        return new self(
            event_category_id: $payload['event_category_id'],
            title: $payload['title'],
            description: $payload['description'] ?? null,
            type: $payload['type'] ?? 'other',
            start_date: $payload['start_date'],
            end_date: Arr::get($payload, 'end_date'),
            start_time: Arr::get($payload, 'start_time'),
            end_time: Arr::get($payload, 'end_time'),
            venue: Arr::get($payload, 'venue'),
            organized_by: $payload['organized_by'] ?? $organizedBy,
            banner_image: Arr::get($payload, 'banner_image'),
            status: $payload['status'] ?? 'upcoming',
            target_roles: $payload['target_roles'] ?? [],
            target_grades: json_decode($payload['target_grades_json'] ?? '["all"]', true) ?: ['all'],
            target_teacher_grades: json_decode($payload['target_teacher_grades_json'] ?? '["all"]', true) ?: ['all'],
            target_guardian_grades: json_decode($payload['target_guardian_grades_json'] ?? '["all"]', true) ?: ['all'],
            target_departments: json_decode($payload['target_departments_json'] ?? '["all"]', true) ?: ['all'],
            schedules: json_decode($payload['schedules_json'] ?? '[]', true) ?: [],
        );
    }

    public function toArray(): array
    {
        return [
            'event_category_id' => $this->event_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'venue' => $this->venue,
            'organized_by' => $this->organized_by,
            'banner_image' => $this->banner_image,
            'status' => $this->status,
            'target_roles' => $this->target_roles,
            'target_grades' => $this->target_grades,
            'target_teacher_grades' => $this->target_teacher_grades,
            'target_guardian_grades' => $this->target_guardian_grades,
            'target_departments' => $this->target_departments,
            'schedules' => $this->schedules,
        ];
    }
}
