<?php

namespace App\DTOs\SalaryPayroll;

use Illuminate\Support\Arr;

class PayrollFilterData
{
    public function __construct(
        public readonly ?int $year,
        public readonly ?int $month,
        public readonly ?string $status,
        public readonly int $perPage = 12,
    ) {}

    public static function from(array $input): self
    {
        return new self(
            year: Arr::get($input, 'year') ? (int) $input['year'] : null,
            month: Arr::get($input, 'month') ? (int) $input['month'] : null,
            status: Arr::get($input, 'status'),
            perPage: (int) ($input['per_page'] ?? 12),
        );
    }
}
