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
    ) {}

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
        ];
    }
}
