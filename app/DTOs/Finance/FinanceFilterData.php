<?php

namespace App\DTOs\Finance;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class FinanceFilterData
{
    public function __construct(
        public readonly ?int $year,
        public readonly ?int $month,
        public readonly ?string $category,
        public readonly ?string $paymentMethod,
        public readonly ?string $search,
        public readonly int $perPage = 12,
    ) {}

    public static function from(array $input): self
    {
        $period = $input['period'] ?? null;
        $parsedYear = null;
        $parsedMonth = null;

        if ($period && preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            $parsedYear = (int) $matches[1];
            $parsedMonth = (int) $matches[2];
        }

        $year = $parsedYear ?? ($input['year'] ?? null);
        $month = $parsedMonth ?? ($input['month'] ?? null);

        return new self(
            year: $year ? (int) $year : null,
            month: $month ? (int) $month : null,
            category: Arr::get($input, 'category'),
            paymentMethod: Arr::get($input, 'payment_method'),
            search: Arr::get($input, 'search'),
            perPage: (int) ($input['per_page'] ?? 12),
        );
    }

    public function periodLabel(): string
    {
        if ($this->year && $this->month) {
            $date = Carbon::createFromDate($this->year, $this->month, 1);
            return $date->format('F Y');
        }

        if ($this->year) {
            return (string) $this->year;
        }

        return __('All time');
    }
}
