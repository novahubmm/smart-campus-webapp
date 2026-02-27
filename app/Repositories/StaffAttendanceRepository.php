<?php

namespace App\Repositories;

use App\Interfaces\StaffAttendanceRepositoryInterface;
use App\Models\StaffAttendance;
use App\Models\StaffProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StaffAttendanceRepository implements StaffAttendanceRepositoryInterface
{
    private const STATUSES = ['present', 'absent', 'late', 'excused', 'off', 'holiday'];

    public function getDailyRegister(Carbon $date): Collection
    {
        $staff = StaffProfile::with(['user', 'department'])
            ->where('status', 'active')
            ->orderBy('employee_id')
            ->get();

        $attendance = StaffAttendance::whereDate('date', $date)->get()->keyBy('staff_id');

        return $staff->map(function (StaffProfile $staffProfile) use ($attendance) {
            $row = $attendance[$staffProfile->id] ?? null;
            return [
                'id' => $staffProfile->id,
                'name' => $staffProfile->user?->name ?? '—',
                'employee_id' => $staffProfile->employee_id,
                'department' => $staffProfile->department?->name ?? '—',
                'status' => $row?->status ?? null,
                'remark' => $row?->remark,
                'start_time' => $row?->start_time,
                'end_time' => $row?->end_time,
                'marked_by' => $row?->markedByUser?->name,
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
        $staff = StaffProfile::with(['user', 'department'])
            ->where('status', 'active')
            ->orderBy('employee_id')
            ->get();

        $attendance = StaffAttendance::select('staff_id', 'status')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('staff_id');

        return $staff->map(function (StaffProfile $staffProfile) use ($attendance) {
            $rows = $attendance[$staffProfile->id] ?? collect();
            $counts = $this->countStatuses($rows);
            $total = array_sum($counts);
            $presentEquivalent = ($counts['present'] ?? 0) + ($counts['late'] ?? 0) + (($counts['half-day'] ?? 0) * 0.5);
            $attendancePct = $total > 0 ? round(($presentEquivalent / $total) * 100, 1) : 0;

            return [
                'id' => $staffProfile->id,
                'name' => $staffProfile->user?->name ?? '—',
                'employee_id' => $staffProfile->employee_id,
                'department' => $staffProfile->department?->name ?? '—',
                'attendance_pct' => $attendancePct,
                'counts' => $counts,
                'total' => $total,
            ];
        });
    }

    public function getStaffDetail(string $staffId, Carbon $start, Carbon $end): array
    {
        $staff = StaffProfile::with(['user', 'department'])->findOrFail($staffId);

        $attendance = StaffAttendance::where('staff_id', $staffId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $counts = $this->countStatuses($attendance);
        $total = array_sum($counts);
        $presentEquivalent = ($counts['present'] ?? 0) + ($counts['late'] ?? 0) + (($counts['half-day'] ?? 0) * 0.5);
        $attendancePct = $total > 0 ? round(($presentEquivalent / $total) * 100, 1) : 0;

        $daily = $attendance->groupBy('date')->map(function ($items, $date) {
            $first = $items->first();
            return [
                'date' => $date,
                'status' => $first->status,
                'remark' => $first->remark,
                'start_time' => $first->start_time,
                'end_time' => $first->end_time,
            ];
        })->values();

        return [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->user?->name ?? '—',
                'employee_id' => $staff->employee_id,
                'department' => $staff->department?->name ?? '—',
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
            $status = $row->status ?? null;
            if (!$status) {
                continue;
            }

            if (!array_key_exists($status, $counts)) {
                $counts[$status] = 0;
            }

            $counts[$status]++;
        }

        return $counts;
    }
}
