<?php

namespace App\Services;

use App\DTOs\Dashboard\DashboardMetricsData;
use App\Interfaces\DashboardRepositoryInterface;

class DashboardService
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function getDashboardData(): DashboardMetricsData
    {
        $setting = $this->dashboardRepository->getSetting();


        $all_setup_completed = $setting->setup_completed_school_info
            && $setting->setup_completed_academic
            && $setting->setup_completed_event_and_announcements
            && $setting->setup_completed_time_table_and_attendance
            && $setting->setup_completed_finance;
            
        return DashboardMetricsData::from([
            'setting' => $setting,
            'counts' => $this->dashboardRepository->getCounts(),
            'todayAttendance' => $this->dashboardRepository->getTodayAttendance(),
            'feeCollectionPercent' => $this->dashboardRepository->getFeeCollectionPercent(),
            'upcomingEvents' => $this->dashboardRepository->getUpcomingEvents(),
            'upcomingExams' => $this->dashboardRepository->getUpcomingExams(),
            'setupFlags' => $this->dashboardRepository->getSetupFlags($setting),
            'all_setup_completed' => $all_setup_completed,
        ]);
    }
}
