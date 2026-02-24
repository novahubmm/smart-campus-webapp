<?php

namespace App\DTOs\Finance;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class FinanceFilterData
{
    public function __construct(
        public readonly ?int $year,
        public readonly ?int $month,
        public readonly ?int $day,
        public readonly ?string $feePaymentDate,
        public readonly ?string $incomeDate,
        public readonly ?string $expenseDate,
        public readonly ?string $dailyPlDate,
        public readonly ?string $monthlyPlPeriod,
        public readonly ?string $category,
        public readonly ?string $paymentMethod,
        public readonly ?string $search,
        public readonly int $perPage = 12,
    ) {}

    public static function from(array $input): self
    {
        $period = $input['period'] ?? $input['month'] ?? null;
        $parsedYear = null;
        $parsedMonth = null;
        $parsedDay = null;

        // Handle full date format (YYYY-MM-DD) - extract year, month, and day
        if ($period && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $period, $matches)) {
            $parsedYear = (int) $matches[1];
            $parsedMonth = (int) $matches[2];
            $parsedDay = (int) $matches[3];
        }
        // Handle month format (YYYY-MM)
        elseif ($period && preg_match('/^(\d{4})-(\d{2})$/', $period, $matches)) {
            $parsedYear = (int) $matches[1];
            $parsedMonth = (int) $matches[2];
        }

        $year = $parsedYear ?? ($input['year'] ?? null);
        $month = $parsedMonth ?? null;
        $day = $parsedDay ?? null;

        // Default to current month if no filter is provided
        if (!$year && !$month && !$day) {
            $year = now()->year;
            $month = now()->month;
        }

        return new self(
            year: $year ? (int) $year : null,
            month: $month ? (int) $month : null,
            day: $day ? (int) $day : null,
            feePaymentDate: Arr::get($input, 'fee_payment_date') ?? now()->format('Y-m-d'),
            incomeDate: Arr::get($input, 'income_date') ?? now()->format('Y-m-d'),
            expenseDate: Arr::get($input, 'expense_date') ?? now()->format('Y-m-d'),
            dailyPlDate: Arr::get($input, 'daily_pl_date') ?? now()->format('Y-m-d'),
            monthlyPlPeriod: Arr::get($input, 'monthly_pl_period') ?? now()->format('Y-m'),
            category: Arr::get($input, 'category'),
            paymentMethod: Arr::get($input, 'payment_method'),
            search: Arr::get($input, 'search'),
            perPage: (int) ($input['per_page'] ?? 12),
        );
    }

    public function periodLabel(): string
    {
        if ($this->year && $this->month && $this->day) {
            $date = Carbon::createFromDate($this->year, $this->month, $this->day);
            return $date->format('F j, Y');
        }

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
