<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class ExpenseData
{
    public function __construct(
        public readonly string $title,
        public readonly string $expense_category_id,
        public readonly float $amount,
        public readonly string $expense_date,
        public readonly string $payment_method,
        public readonly ?string $description,
        public readonly ?string $vendor_name,
        public readonly ?string $invoice_number,
        public readonly ?string $receipt_file,
        public readonly ?string $notes,
        public readonly bool $status,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            title: $validated['title'],
            expense_category_id: $validated['expense_category_id'],
            amount: (float) $validated['amount'],
            expense_date: $validated['expense_date'],
            payment_method: $validated['payment_method'],
            description: Arr::get($validated, 'description'),
            vendor_name: Arr::get($validated, 'vendor_name'),
            invoice_number: Arr::get($validated, 'invoice_number'),
            receipt_file: Arr::get($validated, 'receipt_file'),
            notes: Arr::get($validated, 'notes'),
            status: (bool) ($validated['status'] ?? true),
        );
    }
}
