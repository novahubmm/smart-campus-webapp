<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class InvoiceItemData
{
    public function __construct(
        public readonly string $fee_type_id,
        public readonly ?string $description,
        public readonly int $quantity,
        public readonly float $unit_price,
    ) {}

    public static function from(array $payload): self
    {
        $quantity = (int) ($payload['quantity'] ?? 1);
        $unitPrice = (float) ($payload['unit_price'] ?? 0);

        return new self(
            fee_type_id: $payload['fee_type_id'],
            description: Arr::get($payload, 'description'),
            quantity: max(1, $quantity),
            unit_price: $unitPrice,
        );
    }

    public function amount(): float
    {
        return round($this->quantity * $this->unit_price, 2);
    }

    public function toArray(): array
    {
        return [
            'fee_type_id' => $this->fee_type_id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'amount' => $this->amount(),
        ];
    }
}
