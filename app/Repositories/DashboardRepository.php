<?php

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;
use App\Models\Setting;
use App\Models\Exam;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Date;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getCounts(): array
    {
        return [
            'students' => StudentProfile::count(),
            'staff' => StaffProfile::count(),
            'teachers' => TeacherProfile::count(),
        ];
    }

    public function getTodayAttendance(): array
    {
        $today = Date::now()->toDateString();

        return [
            'students' => $this->getAttendancePercent('student_attendance', $today, StudentProfile::count()),
            'staff' => $this->getAttendancePercent('staff_attendance', $today, StaffProfile::count()),
            'teachers' => $this->getAttendancePercent('teacher_attendance', $today, TeacherProfile::count()),
        ];
    }

    public function getFeeCollectionPercent(): float
    {
        if (!Schema::hasTable('student_fees')) {
            return 0.0;
        }

        $startOfMonth = Date::now()->startOfMonth();
        $endOfMonth = Date::now()->endOfMonth();

        $due = DB::table('student_fees')
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->sum('amount_due');

        $paid = DB::table('student_fees')
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(amount_paid, 0)'));

        if ($due <= 0) {
            return 0.0;
        }

        return round(min(100, ($paid / $due) * 100), 1);
    }

    public function getUpcomingEvents(int $limit = 5): Collection
    {
        if (!Schema::hasTable('events')) {
            return collect();
        }

        return DB::table('events')
            ->select('id', 'title', 'start_date', 'venue', 'type')
            ->whereNull('deleted_at')
            ->where('status', true)
            ->whereDate('start_date', '>=', Date::now()->toDateString())
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    public function getUpcomingExams(int $limit = 5): Collection
    {
        if (!Schema::hasTable('exams')) {
            return collect();
        }

        return Exam::query()
            ->with(['grade', 'examType'])
            ->select('id', 'exam_id', 'name', 'start_date', 'end_date', 'grade_id', 'exam_type_id', 'status')
            ->where('status', 'upcoming')
            ->orderBy('start_date')
            ->limit($limit)
            ->get();
    }

    public function getSetting(): ?Setting
    {
        return Setting::first();
    }

    public function getSetupFlags(?Setting $setting): array
    {
        return [
            'school_info' => (bool) ($setting?->setup_completed_school_info ?? false),
            'academic' => (bool) ($setting?->setup_completed_academic ?? false),
            'events' => (bool) ($setting?->setup_completed_event_and_announcements ?? false),
            'attendance' => (bool) ($setting?->setup_completed_time_table_and_attendance ?? false),
            'finance' => (bool) ($setting?->setup_completed_finance ?? false),
        ];
    }

    private function countPresent(string $table, string $date): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)
            ->whereDate('date', $date)
            ->where('status', 'present')
            ->count();
    }

    private function getAttendancePercent(string $table, string $date, int $total): string
    {
        if ($total <= 0) {
            return '0%';
        }

        $present = $this->countPresent($table, $date);

        if ($present <= 0) {
            return '0%';
        }

        return round(($present / $total) * 100, 1) . '%';
    }
}
