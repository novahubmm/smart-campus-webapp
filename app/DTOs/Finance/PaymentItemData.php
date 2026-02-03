<?php

namespace App\DTOs\Finance;

class PaymentItemData
{
    public function __construct(
        public readonly ?string $invoice_id,
        public readonly float $amount,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            invoice_id: $payload['invoice_id'] ?? null,
            amount: (float) ($payload['amount'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoice_id,
            'amount' => $this->amount,
        ];
    }
}
