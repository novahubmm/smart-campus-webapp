<?php

namespace App\DTOs\Academic;

use Illuminate\Contracts\Support\Arrayable;

class RoomData implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly string $building,
        public readonly ?string $floor,
        public readonly ?int $capacity,
        public readonly array $facilities = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            name: $data['name'],
            building: $data['building'] ?? '',
            floor: $data['floor'] ?? null,
            capacity: $data['capacity'] ?? null,
            facilities: $data['facilities'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'building' => $this->building,
            'floor' => $this->floor,
            'capacity' => $this->capacity,
            'facilities' => $this->facilities,
        ];
    }
}
