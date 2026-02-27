<?php

namespace App\DTOs\Academic;

use Illuminate\Contracts\Support\Arrayable;

class GradeData implements Arrayable
{
    public function __construct(
        public readonly string $level,
        public readonly string $batch_id,
        public readonly string $grade_category_id,
        public readonly float $price_per_month,
        public readonly ?string $start_date = null,
        public readonly ?string $end_date = null,
        public readonly array $subjects = [],
        public readonly array $classes = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            level: $data['level'],
            batch_id: $data['batch_id'],
            grade_category_id: $data['grade_category_id'],
            price_per_month: (float)($data['price_per_month'] ?? 0),
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            subjects: $data['subjects'] ?? [],
            classes: $data['classes'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'batch_id' => $this->batch_id,
            'grade_category_id' => $this->grade_category_id,
            'price_per_month' => $this->price_per_month,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'subjects' => $this->subjects,
            'classes' => $this->classes,
        ];
    }
}
