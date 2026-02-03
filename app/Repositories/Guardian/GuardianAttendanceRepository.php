<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianAttendanceRepositoryInterface;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use Carbon\Carbon;

class GuardianAttendanceRepository implements GuardianAttendanceRepositoryInterface
{
    public function getAttendanceRecords(StudentProfile $student, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return $records->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'status' => $record->status,
                'check_in_time' => $record->check_in_time,
                'check_out_time' => $record->check_out_time,
                'remarks' => $record->remarks,
            ];
        })->toArray();
    }

    public function getAttendanceSummary(StudentProfile $student, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalDays = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        $percentage = $totalDays > 0 ? round(($present + $late) / $totalDays * 100, 1) : 0;

        return [
            'total_days' => $totalDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'percentage' => $percentage,
        ];
    }

    public function getAttendanceCalendar(StudentProfile $student, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $records = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $calendar = [];
        foreach ($records as $record) {
            $calendar[$record->date->format('Y-m-d')] = $record->status;
        }

        return $calendar;
    }

    public function getAttendanceStats(StudentProfile $student): array
    {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $yearStart = $now->copy()->startOfYear();

        // Current month
        $currentMonthRecords = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$currentMonthStart, $now])
            ->get();
        
        $currentMonthTotal = $currentMonthRecords->count();
        $currentMonthPresent = $currentMonthRecords->whereIn('status', ['present', 'late'])->count();
        $currentMonthPercentage = $currentMonthTotal > 0 ? round($currentMonthPresent / $currentMonthTotal * 100, 1) : 0;

        // Last month
        $lastMonthRecords = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->get();
        
        $lastMonthTotal = $lastMonthRecords->count();
        $lastMonthPresent = $lastMonthRecords->whereIn('status', ['present', 'late'])->count();
        $lastMonthPercentage = $lastMonthTotal > 0 ? round($lastMonthPresent / $lastMonthTotal * 100, 1) : 0;

        // Year to date
        $yearRecords = StudentAttendance::where('student_id', $student->id)
            ->whereBetween('date', [$yearStart, $now])
            ->get();
        
        $yearTotal = $yearRecords->count();
        $yearPresent = $yearRecords->whereIn('status', ['present', 'late'])->count();
        $yearAbsent = $yearRecords->where('status', 'absent')->count();
        $yearPercentage = $yearTotal > 0 ? round($yearPresent / $yearTotal * 100, 1) : 0;

        // Consecutive present days
        $consecutiveDays = $this->getConsecutivePresentDays($student);

        return [
            'current_month_percentage' => $currentMonthPercentage,
            'last_month_percentage' => $lastMonthPercentage,
            'year_to_date_percentage' => $yearPercentage,
            'total_present' => $yearPresent,
            'total_absent' => $yearAbsent,
            'consecutive_present_days' => $consecutiveDays,
        ];
    }

    private function getConsecutivePresentDays(StudentProfile $student): int
    {
        $records = StudentAttendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->get();

        $consecutive = 0;
        foreach ($records as $record) {
            if (in_array($record->status, ['present', 'late'])) {
                $consecutive++;
            } else {
                break;
            }
        }

        return $consecutive;
    }
}
