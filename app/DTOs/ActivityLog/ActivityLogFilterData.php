<?php

namespace App\DTOs\ActivityLog;

use Carbon\Carbon;

class ActivityLogFilterData
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $action,
        public readonly ?string $status,
        public readonly ?string $dateRange,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly int $perPage = 15,
    ) {}

    public static function from(array $input): self
    {
        return new self(
            search: $input['search'] ?? null,
            action: $input['action'] ?? null,
            status: $input['status'] ?? null,
            dateRange: $input['date_range'] ?? 'today',
            startDate: $input['start_date'] ?? null,
            endDate: $input['end_date'] ?? null,
            perPage: (int) ($input['per_page'] ?? 15),
        );
    }

    public function getDateRange(): array
    {
        if ($this->startDate && $this->endDate) {
            return [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ];
        }

        return match ($this->dateRange) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'last_7_days' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'all' => [null, null],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    public function dateRangeLabel(): string
    {
        return match ($this->dateRange) {
            'today' => __('Today'),
            'yesterday' => __('Yesterday'),
            'last_7_days' => __('Last 7 Days'),
            'last_30_days' => __('Last 30 Days'),
            'this_month' => __('This Month'),
            'all' => __('All Time'),
            default => __('Today'),
        };
    }
}
