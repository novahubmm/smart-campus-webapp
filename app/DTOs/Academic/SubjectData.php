<?php

namespace App\DTOs\Academic;

use Illuminate\Contracts\Support\Arrayable;

class SubjectData implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $code,
        public readonly ?string $subject_type_id,
        public readonly ?string $icon,
        public readonly ?string $icon_color,
        public readonly ?string $progress_color,
        public readonly array $grade_ids = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'] ?? null,
            subject_type_id: $data['subject_type_id'] ?? null,
            icon: $data['icon'] ?? null,
            icon_color: $data['icon_color'] ?? null,
            progress_color: $data['progress_color'] ?? null,
            grade_ids: $data['grade_ids'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'subject_type_id' => $this->subject_type_id,
            'icon' => $this->icon,
            'icon_color' => $this->icon_color,
            'progress_color' => $this->progress_color,
        ];
    }

    public function gradeIds(): array
    {
        return $this->grade_ids;
    }
}
