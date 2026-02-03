<?php

namespace App\Interfaces;

use App\Models\Setting;
use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    /**
     * Retrieve counts for key profiles.
     */
    public function getCounts(): array;

    /**
     * Retrieve today's attendance snapshot per role.
     */
    public function getTodayAttendance(): array;

    /**
     * Calculate fee collection percent for the current month.
     */
    public function getFeeCollectionPercent(): float;

    /**
     * Upcoming events within a window (may be empty if not yet implemented).
     */
    public function getUpcomingEvents(int $limit = 5): Collection;

    /**
     * Upcoming exams within a window.
     */
    public function getUpcomingExams(int $limit = 5): Collection;

    /**
     * Fetch the primary settings record.
     */
    public function getSetting(): ?Setting;

    /**
     * Map setup completion flags into a normalized array.
     */
    public function getSetupFlags(?Setting $setting): array;
}
