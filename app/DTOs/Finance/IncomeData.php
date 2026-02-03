<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class IncomeData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $category,
        public readonly float $amount,
        public readonly string $income_date,
        public readonly string $payment_method,
        public readonly ?string $reference_number,
        public readonly ?string $invoice_id,
        public readonly ?string $grade_id,
        public readonly ?string $class_id,
        public readonly ?string $description,
        public readonly ?string $notes,
        public readonly bool $status,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            title: $validated['title'],
            category: $validated['category'] ?? null,
            amount: (float) $validated['amount'],
            income_date: $validated['income_date'],
            payment_method: $validated['payment_method'],
            reference_number: Arr::get($validated, 'reference_number'),
            invoice_id: Arr::get($validated, 'invoice_id'),
            grade_id: Arr::get($validated, 'grade_id'),
            class_id: Arr::get($validated, 'class_id'),
            description: Arr::get($validated, 'description'),
            notes: Arr::get($validated, 'notes'),
            status: (bool) ($validated['status'] ?? true),
        );
    }
}
