<?php

namespace App\DTOs\Announcement;

use Illuminate\Support\Arr;

class AnnouncementData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $announcement_type_id,
        public readonly string $type,
        public readonly string $priority,
        public readonly ?string $location,
        public readonly ?string $event_id,
        /** @var array<int, string> */
        public readonly array $target_roles,
        /** @var array<int, string> */
        public readonly array $target_grades,
        /** @var array<int, string> */
        public readonly array $target_departments,
        public readonly ?string $publish_date,
        public readonly bool $is_published,
        public readonly ?string $attachment,
        public readonly bool $status,
        public readonly ?string $created_by,
    ) {}

    public static function from(array $payload, ?string $createdBy = null): self
    {
        $roles = collect($payload['target_roles'] ?? [])
            ->filter()
            ->values()
            ->all();

        // Parse target_grades from JSON if provided
        $targetGrades = $payload['target_grades'] ?? ['all'];
        if (isset($payload['target_grades_json'])) {
            $targetGrades = json_decode($payload['target_grades_json'], true) ?: ['all'];
        }

        // Parse target_departments from JSON if provided
        $targetDepartments = $payload['target_departments'] ?? ['all'];
        if (isset($payload['target_departments_json'])) {
            $targetDepartments = json_decode($payload['target_departments_json'], true) ?: ['all'];
        }

        return new self(
            title: $payload['title'],
            content: $payload['content'],
            announcement_type_id: Arr::get($payload, 'announcement_type_id') ?: null,
            type: $payload['type'] ?? 'general',
            priority: $payload['priority'] ?? 'medium',
            location: Arr::get($payload, 'location'),
            event_id: Arr::get($payload, 'event_id') ?: null,
            target_roles: $roles,
            target_grades: $targetGrades,
            target_departments: $targetDepartments,
            publish_date: Arr::get($payload, 'publish_date'),
            is_published: (bool) ($payload['is_published'] ?? false),
            attachment: Arr::get($payload, 'attachment'),
            status: (bool) ($payload['status'] ?? true),
            created_by: $payload['created_by'] ?? $createdBy,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'announcement_type_id' => $this->announcement_type_id,
            'type' => $this->type,
            'priority' => $this->priority,
            'location' => $this->location,
            'event_id' => $this->event_id,
            'target_roles' => $this->target_roles,
            'target_grades' => $this->target_grades,
            'target_departments' => $this->target_departments,
            'publish_date' => $this->publish_date,
            'is_published' => $this->is_published,
            'attachment' => $this->attachment,
            'status' => $this->status,
            'created_by' => $this->created_by,
        ];
    }
}
