<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class InvoiceData
{
    /**
     * @param array<int, InvoiceItemData> $items
     */
    public function __construct(
        public readonly string $student_id,
        public readonly string $invoice_date,
        public readonly string $due_date,
        public readonly float $discount,
        public readonly ?string $notes,
        public readonly string $status,
        public readonly array $items,
    ) {}

    public static function from(array $payload): self
    {
        $items = collect($payload['items'] ?? [])
            ->filter()
            ->map(fn(array $item) => InvoiceItemData::from($item))
            ->values()
            ->all();

        return new self(
            student_id: $payload['student_id'],
            invoice_date: $payload['invoice_date'],
            due_date: $payload['due_date'],
            discount: (float) ($payload['discount'] ?? 0),
            notes: Arr::get($payload, 'notes'),
            status: $payload['status'] ?? 'draft',
            items: $items,
        );
    }
}
