<?php

namespace App\Repositories;

use App\Interfaces\TeacherAttendanceRepositoryInterface;
use App\Models\TeacherAttendance;
use App\Models\TeacherProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeacherAttendanceRepository implements TeacherAttendanceRepositoryInterface
{
    private const STATUSES = ['present', 'absent', 'late', 'excused', 'off', 'holiday'];

    public function getDailyRegister(Carbon $date): Collection
    {
        $teachers = TeacherProfile::with(['user', 'department'])->orderBy('employee_id')->get();

        $attendance = TeacherAttendance::whereDate('date', $date)->get()->keyBy('teacher_id');

        return $teachers->map(function (TeacherProfile $teacher) use ($attendance) {
            $row = $attendance[$teacher->user_id] ?? null;
            return [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? '—',
                'employee_id' => $teacher->employee_id,
                'department' => $teacher->department?->name ?? '—',
                'status' => $this->normalizeStatusForUi($row?->status),
                'remark' => $row?->remarks,
                'start_time' => $this->formatTime($row?->check_in_time),
                'end_time' => $this->formatTime($row?->check_out_time),
                'marked_by' => null,
            ];
        });
    }

    public function getMonthlySummary(Carbon $month): Collection
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        return $this->getRangeSummary($start, $end);
    }

    public function getRangeSummary(Carbon $start, Carbon $end): Collection
    {
        $teachers = TeacherProfile::with(['user', 'department'])->orderBy('employee_id')->get();

        $attendance = TeacherAttendance::select('teacher_id', 'status')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('teacher_id');

        return $teachers->map(function (TeacherProfile $teacher) use ($attendance) {
            $rows = $attendance[$teacher->user_id] ?? collect();
            $counts = $this->countStatuses($rows);
            $total = array_sum($counts);
            $presentEquivalent = ($counts['present'] ?? 0) + ($counts['late'] ?? 0);
            $attendancePct = $total > 0 ? round(($presentEquivalent / $total) * 100, 1) : 0;

            return [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? '—',
                'employee_id' => $teacher->employee_id,
                'department' => $teacher->department?->name ?? '—',
                'attendance_pct' => $attendancePct,
                'counts' => $counts,
                'total' => $total,
            ];
        });
    }

    public function getTeacherDetail(string $teacherId, Carbon $start, Carbon $end): array
    {
        $teacher = TeacherProfile::with(['user', 'department'])->findOrFail($teacherId);

        $attendance = TeacherAttendance::where('teacher_id', $teacher->user_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $counts = $this->countStatuses($attendance);
        $total = array_sum($counts);
        $presentEquivalent = ($counts['present'] ?? 0) + ($counts['late'] ?? 0);
        $attendancePct = $total > 0 ? round(($presentEquivalent / $total) * 100, 1) : 0;

        $daily = $attendance->groupBy('date')->map(function ($items, $date) {
            $first = $items->first();
            return [
                'date' => $date,
                'status' => $this->normalizeStatusForUi($first->status),
                'remark' => $first->remarks,
                'start_time' => $this->formatTime($first->check_in_time),
                'end_time' => $this->formatTime($first->check_out_time),
            ];
        })->values();

        return [
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? '—',
                'employee_id' => $teacher->employee_id,
                'department' => $teacher->department?->name ?? '—',
            ],
            'summary' => [
                'attendance_pct' => $attendancePct,
                'counts' => $counts,
                'total' => $total,
            ],
            'daily' => $daily,
            'range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
        ];
    }

    private function countStatuses($rows): array
    {
        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $row) {
            $status = $this->normalizeStatusForUi($row->status ?? null);
            if (array_key_exists($status, $counts)) {
                $counts[$status]++;
            }
        }
        return $counts;
    }

    private function normalizeStatusForUi(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return match ($status) {
            'leave' => 'excused',
            'half_day' => 'late',
            default => $status,
        };
    }

    private function formatTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
    }
}
