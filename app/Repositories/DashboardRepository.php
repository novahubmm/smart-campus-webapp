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
            'students' => StudentProfile::where('status', 'active')->count(),
            'staff' => StaffProfile::where('status', 'active')->count(),
            'teachers' => TeacherProfile::where('status', 'active')->count(),
        ];
    }

    public function getTodayAttendance(): array
    {
        $today = Date::now()->toDateString();

        return [
            'students' => $this->getAttendancePercent('student_attendance', $today, StudentProfile::where('status', 'active')->count()),
            'staff' => $this->getAttendancePercent('staff_attendance', $today, StaffProfile::where('status', 'active')->count()),
            'teachers' => $this->getAttendancePercent('teacher_attendance', $today, TeacherProfile::where('status', 'active')->count()),
        ];
    }

    public function getFeeCollectionPercent(): float
    {
        // Check for new payment system table first
        if (Schema::hasTable('invoices_payment_system')) {
            $currentMonth = Date::now()->format('Y-m');

            // Get total amount due for this month
            // Check both due_date and created_at to be more flexible
            $totalDue = DB::table('invoices_payment_system')
                ->where(function($q) use ($currentMonth) {
                    $q->whereRaw("strftime('%Y-%m', due_date) = ?", [$currentMonth])
                      ->orWhereRaw("strftime('%Y-%m', created_at) = ?", [$currentMonth]);
                })
                ->sum('total_amount');

            // Get total amount paid for this month
            $totalPaid = DB::table('invoices_payment_system')
                ->where(function($q) use ($currentMonth) {
                    $q->whereRaw("strftime('%Y-%m', due_date) = ?", [$currentMonth])
                      ->orWhereRaw("strftime('%Y-%m', created_at) = ?", [$currentMonth]);
                })
                ->sum('paid_amount');

            if ($totalDue <= 0) {
                return 0.0;
            }

            return round(min(100, ($totalPaid / $totalDue) * 100), 1);
        }

        // Fallback to old invoices table
        if (!Schema::hasTable('invoices')) {
            return 0.0;
        }

        $startOfMonth = Date::now()->startOfMonth();
        $endOfMonth = Date::now()->endOfMonth();

        // Get total amount due for this month (from invoices)
        $totalDue = DB::table('invoices')
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->sum('total_amount');

        // Get total amount paid for this month (from invoices)
        $totalPaid = DB::table('invoices')
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->whereNull('deleted_at')
            ->sum('paid_amount');

        if ($totalDue <= 0) {
            return 0.0;
        }

        return round(min(100, ($totalPaid / $totalDue) * 100), 1);
    }

    public function getUpcomingEvents(int $limit = 5): Collection
    {
        if (!Schema::hasTable('events')) {
            return collect();
        }

        $today = Date::now()->toDateString();

        // Get all active events that are upcoming or ongoing
        $events = DB::table('events')
            ->select('id', 'title', 'start_date', 'end_date', 'venue', 'type')
            ->whereNull('deleted_at')
            ->get();
        // Filter in PHP to handle date logic more reliably
        return $events->filter(function($event) use ($today) {
            $startDate = $event->start_date;
            $endDate = $event->end_date ?? $event->start_date;
            
            // Include if event hasn't ended yet (end_date >= today)
            return $endDate >= $today;
        })
        ->sortBy('start_date')
        ->take($limit)
        ->values();
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
