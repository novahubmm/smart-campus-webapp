<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;

class FeeStructureData
{
    public function __construct(
        public readonly string $grade_id,
        public readonly string $batch_id,
        public readonly string $fee_type_id,
        public readonly float $amount,
        public readonly string $frequency,
        public readonly ?string $applicable_from,
        public readonly ?string $applicable_to,
        public readonly bool $status,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            grade_id: $payload['grade_id'],
            batch_id: $payload['batch_id'],
            fee_type_id: $payload['fee_type_id'],
            amount: (float) $payload['amount'],
            frequency: $payload['frequency'] ?? 'monthly',
            applicable_from: Arr::get($payload, 'applicable_from'),
            applicable_to: Arr::get($payload, 'applicable_to'),
            status: (bool) ($payload['status'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'grade_id' => $this->grade_id,
            'batch_id' => $this->batch_id,
            'fee_type_id' => $this->fee_type_id,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'applicable_from' => $this->applicable_from,
            'applicable_to' => $this->applicable_to,
            'status' => $this->status,
        ];
    }
}
