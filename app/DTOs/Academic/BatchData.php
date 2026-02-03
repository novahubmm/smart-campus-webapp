<?php

namespace App\DTOs\Academic;

use Illuminate\Contracts\Support\Arrayable;

class BatchData implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly bool $status = true,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name: $data['name'],
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            status: $data['status'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ];
    }
}
