<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\CheckInRequest;
use App\Http\Requests\Teacher\CheckOutRequest;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherAttendanceController extends Controller
{
    /**
     * POST /api/v1/teacher/attendance/check-in
     * Teacher check-in (morning attendance)
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get teacher profile
        if (!$user->teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
                'error' => [
                    'code' => 'NO_TEACHER_PROFILE',
                ],
            ], 404);
        }
        
        $teacherId = $user->teacherProfile->id;
        $today = now()->format('Y-m-d');

        // Check if already checked in today
        $existingAttendance = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in today',
                'error' => [
                    'code' => 'ALREADY_CHECKED_IN',
                    'check_in_time' => $existingAttendance->check_in_time,
                ],
            ], 400);
        }

        $now = now();
        $checkInTime = $now->format('H:i:s');
        $checkInTimestamp = $now;

        // Create or update attendance record
        $attendance = TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'date' => $today,
            ],
            [
                'id' => TeacherAttendance::generateId($today),
                'day_of_week' => $now->format('l'),
                'check_in_time' => $checkInTime,
                'check_in_timestamp' => $checkInTimestamp,
                'status' => 'present',
                'location_lat' => $request->input('latitude'),
                'location_lng' => $request->input('longitude'),
                'device_info' => $request->input('device_info'),
                'app_version' => $request->input('app_version'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully',
            'data' => [
                'id' => $attendance->id,
                'teacher_id' => $attendance->teacher_id,
                'date' => $attendance->date->format('Y-m-d'),
                'check_in_time' => $attendance->check_in_time,
                'check_in_timestamp' => $attendance->check_in_timestamp->toIso8601String(),
                'status' => 'checked_in',
                'location' => [
                    'latitude' => $attendance->location_lat,
                    'longitude' => $attendance->location_lng,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/teacher/attendance/check-out
     * Teacher check-out (evening attendance)
     */
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get teacher profile
        if (!$user->teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
                'error' => [
                    'code' => 'NO_TEACHER_PROFILE',
                ],
            ], 404);
        }
        
        $teacherId = $user->teacherProfile->id;
        $today = now()->format('Y-m-d');

        // Find today's attendance record
        $attendance = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot check out without checking in first',
                'error' => [
                    'code' => 'NOT_CHECKED_IN',
                ],
            ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked out today',
                'error' => [
                    'code' => 'ALREADY_CHECKED_OUT',
                    'check_out_time' => $attendance->check_out_time,
                ],
            ], 400);
        }

        $now = now();
        $checkOutTime = $now->format('H:i:s');
        $checkOutTimestamp = $now;

        // Calculate working hours
        $workingHours = TeacherAttendance::calculateWorkingHours(
            $attendance->check_in_time,
            $checkOutTime
        );

        // Update attendance record
        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_timestamp' => $checkOutTimestamp,
            'working_hours_decimal' => $workingHours,
            'status' => 'present',
            'remarks' => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully',
            'data' => [
                'id' => $attendance->id,
                'teacher_id' => $attendance->teacher_id,
                'date' => $attendance->date->format('Y-m-d'),
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'check_in_timestamp' => $attendance->check_in_timestamp->toIso8601String(),
                'check_out_timestamp' => $attendance->check_out_timestamp->toIso8601String(),
                'working_hours' => $attendance->working_hours,
                'working_hours_decimal' => $attendance->working_hours_decimal,
                'status' => $attendance->status,
                'notes' => $attendance->remarks,
            ],
        ]);
    }

    /**
     * GET /api/v1/teacher/attendance/today
     * Get today's attendance status with school settings
     */
    public function today(): JsonResponse
    {
        $user = Auth::user();
        
        // Get teacher profile
        if (!$user->teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
                'error' => [
                    'code' => 'NO_TEACHER_PROFILE',
                ],
            ], 404);
        }
        
        $teacherId = $user->teacherProfile->id;
        $today = now()->format('Y-m-d');

        $attendance = TeacherAttendance::where('teacher_id', $teacherId)
            ->whereDate('date', $today)
            ->first();

        // Get school settings
        $setting = \App\Models\Setting::first();
        $schoolSettings = null;
        $currentWorkingHours = 0;
        $hasCompletedHours = false;

        if ($setting) {
            $requiredHours = $setting->required_working_hours ?? 8.0;
            
            $schoolSettings = [
                'required_working_hours' => (float) $requiredHours,
                'required_working_hours_display' => $this->formatHoursDisplay($requiredHours),
                'allow_early_checkout' => (bool) ($setting->allow_early_checkout ?? true),
                'office_start_time' => $setting->office_start_time,
                'office_end_time' => $setting->office_end_time,
                'break_duration_minutes' => $setting->office_break_duration_minutes ?? 60,
                'late_arrival_grace_minutes' => $setting->late_arrival_grace_minutes ?? 15,
            ];

            // Calculate current working hours if checked in
            if ($attendance && $attendance->check_in_time && !$attendance->check_out_time) {
                $checkInTime = \Carbon\Carbon::parse($attendance->check_in_timestamp ?? $attendance->check_in_time);
                $currentTime = now();
                $minutesWorked = $checkInTime->diffInMinutes($currentTime);
                $currentWorkingHours = round($minutesWorked / 60, 1);
                $hasCompletedHours = $currentWorkingHours >= $requiredHours;
            }
        }

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $today,
                    'is_checked_in' => false,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'working_hours' => null,
                    'status' => 'not_checked_in',
                    'school_settings' => $schoolSettings,
                ],
            ]);
        }

        $data = [
            'date' => $attendance->date->format('Y-m-d'),
            'is_checked_in' => (bool) $attendance->check_in_time && !$attendance->check_out_time,
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'working_hours' => $attendance->working_hours,
            'working_hours_decimal' => $attendance->working_hours_decimal,
            'status' => $this->determineStatus($attendance),
            'school_settings' => $schoolSettings,
        ];

        // Add current working hours if checked in
        if ($attendance->check_in_time && !$attendance->check_out_time) {
            $data['current_working_hours'] = $currentWorkingHours;
            $data['current_working_hours_display'] = $this->formatHoursDisplay($currentWorkingHours);
            $data['has_completed_required_hours'] = $hasCompletedHours;
        }

        // Add timestamps if available
        if ($attendance->check_in_timestamp) {
            $data['check_in_timestamp'] = $attendance->check_in_timestamp->toIso8601String();
        }

        if ($attendance->check_out_timestamp) {
            $data['check_out_timestamp'] = $attendance->check_out_timestamp->toIso8601String();
        }

        // Add elapsed time if checked in but not checked out
        if ($attendance->check_in_time && !$attendance->check_out_time) {
            $data['elapsed_time'] = $attendance->elapsed_time;
        }

        // Add leave information if on leave
        if ($attendance->status === 'leave') {
            $data['leave_type'] = $attendance->leave_type;
            $data['leave_reason'] = $attendance->remarks;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Format hours to display format (e.g., "8h 0m" or "7h 30m")
     */
    private function formatHoursDisplay(float $hours): string
    {
        $h = floor($hours);
        $m = round(($hours - $h) * 60);
        return sprintf('%dh %dm', $h, $m);
    }

    /**
     * GET /api/v1/teacher/attendance/settings
     * Get working hours settings for mobile app
     */
    public function getSettings(): JsonResponse
    {
        $setting = \App\Models\Setting::first();

        if (!$setting) {
            $defaultWorkingDays = [1, 2, 3, 4, 5]; // Monday-Friday
            $defaultDayLabels = array_map(fn($day) => $this->getDayName($day), $defaultWorkingDays);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'working_days' => $defaultWorkingDays,
                    'working_days_labels' => $defaultDayLabels,
                    'office_start_time' => '08:00',
                    'office_end_time' => '17:00',
                    'break_duration_minutes' => 60,
                    'break_duration_display' => $this->formatMinutesDisplay(60),
                    'required_working_hours' => 8.0,
                    'required_working_hours_display' => $this->formatHoursDisplay(8.0),
                    'allow_early_checkout' => true,
                    'late_arrival_grace_minutes' => 15,
                    'late_arrival_grace_display' => $this->formatMinutesDisplay(15),
                    'track_overtime' => true,
                ],
            ]);
        }

        $workingDays = $setting->office_working_days ?? [1, 2, 3, 4, 5];
        $selectedDayLabels = array_map(fn($day) => $this->getDayName($day), $workingDays);

        $requiredHours = (float) ($setting->required_working_hours ?? 8.0);
        $breakMinutes = (int) ($setting->office_break_duration_minutes ?? 60);
        $graceMinutes = (int) ($setting->late_arrival_grace_minutes ?? 15);

        return response()->json([
            'success' => true,
            'data' => [
                'working_days' => $workingDays,
                'working_days_labels' => $selectedDayLabels,
                'office_start_time' => $setting->office_start_time ?? '08:00',
                'office_end_time' => $setting->office_end_time ?? '17:00',
                'break_duration_minutes' => $breakMinutes,
                'break_duration_display' => $this->formatMinutesDisplay($breakMinutes),
                'required_working_hours' => $requiredHours,
                'required_working_hours_display' => $this->formatHoursDisplay($requiredHours),
                'allow_early_checkout' => (bool) ($setting->allow_early_checkout ?? true),
                'late_arrival_grace_minutes' => $graceMinutes,
                'late_arrival_grace_display' => $this->formatMinutesDisplay($graceMinutes),
                'track_overtime' => (bool) ($setting->track_overtime ?? true),
            ],
        ]);
    }

    /**
     * Get day name (simple English)
     */
    private function getDayName(int $day): string
    {
        $dayNames = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return $dayNames[$day] ?? '';
    }

    /**
     * Format minutes to display format
     */
    private function formatMinutesDisplay(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes === 1 ? '1 minute' : "{$minutes} minutes";
        }
        
        $hours = (int) floor($minutes / 60);
        $mins = $minutes % 60;
        
        if ($mins === 0) {
            return $hours === 1 ? '1 hour' : "{$hours} hours";
        }
        
        $hourText = $hours === 1 ? '1 hour' : "{$hours} hours";
        $minText = $mins === 1 ? '1 minute' : "{$mins} minutes";
        
        return "{$hourText} {$minText}";
    }

    /**
     * GET /api/v1/teacher/my-attendance
     * Get teacher's attendance history
     */
    public function myAttendance(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get teacher profile
        if (!$user->teacherProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
                'error' => [
                    'code' => 'NO_TEACHER_PROFILE',
                ],
            ], 404);
        }
        
        $teacherId = $user->teacherProfile->id;

        $validated = $request->validate([
            'month' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $query = TeacherAttendance::where('teacher_id', $teacherId);

        // Apply date filters
        if (!empty($validated['month'])) {
            if ($validated['month'] === 'current') {
                $query->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
            } else {
                // Format: YYYY-MM
                $parts = explode('-', $validated['month']);
                if (count($parts) === 2) {
                    $query->whereYear('date', $parts[0])
                        ->whereMonth('date', $parts[1]);
                }
            }
        } elseif (!empty($validated['start_date']) && !empty($validated['end_date'])) {
            $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
        } else {
            // Default: last 12 weeks
            $query->where('date', '>=', now()->subWeeks(12));
        }

        $records = $query->orderBy('date', 'desc')->get();

        // Calculate statistics
        $stats = $this->calculateStats($records);

        $recordsData = $records->map(function ($record) {
            $data = [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'day_of_week' => $record->day_of_week,
                'check_in_time' => $record->check_in_time,
                'check_out_time' => $record->check_out_time,
                'working_hours' => $record->working_hours,
                'working_hours_decimal' => $record->working_hours_decimal,
                'status' => $record->status,
                'remarks' => $record->remarks,
            ];

            // Add timestamps if available
            if ($record->check_in_timestamp) {
                $data['check_in_timestamp'] = $record->check_in_timestamp->toIso8601String();
            }

            if ($record->check_out_timestamp) {
                $data['check_out_timestamp'] = $record->check_out_timestamp->toIso8601String();
            }

            // Add leave information if on leave
            if ($record->status === 'leave') {
                $data['leave_type'] = $record->leave_type;
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $this->getMonthLabel($validated),
                'records' => $recordsData,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Determine the current status of attendance
     */
    private function determineStatus(TeacherAttendance $attendance): string
    {
        if ($attendance->status === 'leave') {
            return 'leave';
        }

        if ($attendance->check_in_time && $attendance->check_out_time) {
            return 'completed';
        }

        if ($attendance->check_in_time) {
            return 'checked_in';
        }

        return 'not_checked_in';
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateStats($records): array
    {
        $totalDays = $records->count();
        $presentDays = $records->where('status', 'present')->count() + 
                       $records->where('status', 'completed')->count();
        $absentDays = $records->where('status', 'absent')->count();
        $leaveDays = $records->where('status', 'leave')->count();
        $halfDays = $records->where('status', 'half_day')->count();

        $attendancePercentage = $totalDays > 0 
            ? round(($presentDays / $totalDays) * 100, 1) 
            : 0;

        // Calculate total and average working hours
        $totalMinutes = $records->sum(function ($record) {
            return $record->working_hours_decimal ? $record->working_hours_decimal * 60 : 0;
        });

        $avgMinutes = $presentDays > 0 ? $totalMinutes / $presentDays : 0;

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'half_days' => $halfDays,
            'attendance_percentage' => $attendancePercentage,
            'average_working_hours' => $this->formatMinutesToHours($avgMinutes),
            'total_working_hours' => $this->formatMinutesToHours($totalMinutes),
        ];
    }

    /**
     * Format minutes to HH:MM format
     */
    private function formatMinutesToHours(float $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = round($minutes % 60);
        return sprintf('%d:%02d', $hours, $mins);
    }

    /**
     * Get month label for display
     */
    private function getMonthLabel(array $validated): string
    {
        if (!empty($validated['month'])) {
            if ($validated['month'] === 'current') {
                return now()->format('F Y');
            }
            
            $parts = explode('-', $validated['month']);
            if (count($parts) === 2) {
                return Carbon::create($parts[0], $parts[1])->format('F Y');
            }
        }

        return 'Last 12 Weeks';
    }
}
