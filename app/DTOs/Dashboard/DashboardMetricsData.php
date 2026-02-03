<?php

namespace App\DTOs\Dashboard;

use App\Models\Setting;
use Illuminate\Support\Collection;

class DashboardMetricsData
{
    public function __construct(
        public readonly ?Setting $setting,
        public readonly array $counts,
        public readonly array $todayAttendance,
        public readonly float $feeCollectionPercent,
        public readonly Collection $upcomingEvents,
        public readonly Collection $upcomingExams,
        public readonly array $setupFlags,
        public readonly bool $allSetupCompleted
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['setting'] ?? null,
            $payload['counts'] ?? [],
            $payload['todayAttendance'] ?? [],
            (float) ($payload['feeCollectionPercent'] ?? 0),
            $payload['upcomingEvents'] ?? collect(),
            $payload['upcomingExams'] ?? collect(),
            $payload['setupFlags'] ?? [],
            (bool) ($payload['all_setup_completed'] ?? false)
        );
    }

    public function toArray(): array
    {
        return [
            'setting' => $this->setting,
            'counts' => $this->counts,
            'todayAttendance' => $this->todayAttendance,
            'feeCollectionPercent' => $this->feeCollectionPercent,
            'upcomingEvents' => $this->upcomingEvents,
            'upcomingExams' => $this->upcomingExams,
            'setupFlags' => $this->setupFlags,
            'all_setup_completed' => $this->allSetupCompleted,
        ];
    }
}
