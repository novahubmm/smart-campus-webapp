<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class PaymentData
{
    /**
     * @param array<int, PaymentItemData> $items
     */
    public function __construct(
        public readonly string $student_id,
        public readonly float $amount,
        public readonly string $payment_date,
        public readonly string $payment_method,
        public readonly ?string $transaction_id,
        public readonly ?string $reference_number,
        public readonly ?string $notes,
        public readonly ?string $receptionist_id,
        public readonly ?string $receptionist_name,
        public readonly ?string $collected_by,
        public readonly array $items,
    ) {}

    public static function from(array $payload, ?string $collectedBy = null): self
    {
        $items = collect($payload['items'] ?? [])
            ->filter()
            ->map(fn(array $item) => PaymentItemData::from($item))
            ->values()
            ->all();

        return new self(
            student_id: $payload['student_id'],
            amount: (float) $payload['amount'],
            payment_date: $payload['payment_date'],
            payment_method: $payload['payment_method'] ?? 'cash',
            transaction_id: Arr::get($payload, 'transaction_id'),
            reference_number: Arr::get($payload, 'reference_number'),
            notes: Arr::get($payload, 'notes'),
            receptionist_id: Arr::get($payload, 'receptionist_id'),
            receptionist_name: Arr::get($payload, 'receptionist_name'),
            collected_by: $payload['collected_by'] ?? $collectedBy,
            items: $items,
        );
    }
}
