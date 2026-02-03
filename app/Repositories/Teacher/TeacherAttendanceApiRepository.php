<?php

namespace App\Repositories\Teacher;

use App\Interfaces\Teacher\TeacherAttendanceApiRepositoryInterface;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TeacherAttendanceApiRepository implements TeacherAttendanceApiRepositoryInterface
{
    public function getStudentsForAttendance(User $teacher, string $classId, ?string $periodId, string $date): ?array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return null;
        }

        // Verify teacher has access to this class
        // 1. Check if teacher is the class teacher
        $isClassTeacher = SchoolClass::where('id', $classId)
            ->where('teacher_id', $teacherProfile->id)
            ->exists();

        // 2. Check if teacher has periods assigned in this class's timetable
        $hasPeriodAccess = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->exists();

        // 3. Check if class name is in teacher's current_classes array
        $class = SchoolClass::with('grade')->find($classId);
        $hasClassInProfile = false;
        if ($class && $teacherProfile->current_classes) {
            $currentClasses = is_array($teacherProfile->current_classes) 
                ? $teacherProfile->current_classes 
                : json_decode($teacherProfile->current_classes, true) ?? [];
            $className = $class->grade?->name . ($class->name ? ' ' . $class->name : '');
            $hasClassInProfile = in_array($class->name, $currentClasses) || in_array($className, $currentClasses);
        }

        $hasAccess = $isClassTeacher || $hasPeriodAccess || $hasClassInProfile;

        if (!$hasAccess) {
            return null;
        }

        $class = SchoolClass::with(['enrolledStudents.user', 'grade'])->find($classId);
        if (!$class) {
            return null;
        }

        $dateObj = Carbon::parse($date);

        // Check if attendance exists for this date and period
        $query = StudentAttendance::whereDate('date', $dateObj)
            ->whereIn('student_id', $class->enrolledStudents->pluck('id'));
        
        if ($periodId) {
            $query->where('period_id', $periodId);
        }
        
        $existingAttendance = $query->get()->keyBy('student_id');

        $attendanceExists = $existingAttendance->isNotEmpty();

        $students = $class->enrolledStudents->map(function ($student) use ($existingAttendance) {
            $attendance = $existingAttendance->get($student->id);
            return [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_id ?? $student->student_identifier ?? '',
                'avatar' => avatar_url($student->photo_path, 'student'),
                'status' => $attendance?->status ?? 'present',
            ];
        });

        $stats = [
            'present' => $students->where('status', 'present')->count(),
            'absent' => $students->where('status', 'absent')->count(),
            'leave' => $students->where('status', 'leave')->count(),
        ];

        return [
            'class_info' => [
                'id' => $class->id,
                'current_period_id' => $periodId,
                'name' => $class->grade?->name . ($class->name ? ' ' . $class->name : ''),
                'section' => $class->name ?? '',
                'total_students' => $class->enrolledStudents->count(),
            ],
            'date' => $dateObj->format('Y-m-d'),
            'attendance_exists' => $attendanceExists,
            'students' => $students->values()->toArray(),
            'stats' => $stats,
        ];
    }

    public function saveAttendance(User $teacher, array $data): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            throw new \Exception('Teacher profile not found');
        }

        $classId = $data['class_id'];
        $periodId = $data['current_period_id'] ?? null;
        $date = Carbon::parse($data['date']);
        $attendanceData = $data['attendance'];

        // Verify teacher has access (class teacher, period access, or in current_classes)
        $isClassTeacher = SchoolClass::where('id', $classId)
            ->where('teacher_id', $teacherProfile->id)
            ->exists();

        $hasPeriodAccess = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->exists();

        $class = SchoolClass::with('grade')->find($classId);
        $hasClassInProfile = false;
        if ($class && $teacherProfile->current_classes) {
            $currentClasses = is_array($teacherProfile->current_classes) 
                ? $teacherProfile->current_classes 
                : json_decode($teacherProfile->current_classes, true) ?? [];
            $className = $class->grade?->name . ($class->name ? ' ' . $class->name : '');
            $hasClassInProfile = in_array($class->name, $currentClasses) || in_array($className, $currentClasses);
        }

        if (!$isClassTeacher && !$hasPeriodAccess && !$hasClassInProfile) {
            throw new \Exception('Access denied to this class');
        }

        if (!$class) {
            throw new \Exception('Class not found');
        }

        $stats = ['present' => 0, 'absent' => 0, 'leave' => 0, 'total' => 0];
        $collectTime = now()->format('H:i:s');
        $dateString = $date->toDateString();

        // Prepare data for bulk upsert (much faster than individual queries)
        $upsertData = [];
        foreach ($attendanceData as $record) {
            $upsertData[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'student_id' => $record['student_id'],
                'date' => $dateString,
                'period_id' => $periodId,
                'status' => $record['status'],
                'marked_by' => $teacher->id,
                'collect_time' => $collectTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $stats[$record['status']] = ($stats[$record['status']] ?? 0) + 1;
            $stats['total']++;
        }

        // Single query instead of N queries
        StudentAttendance::upsert(
            $upsertData,
            ['student_id', 'date', 'period_id'], // unique keys
            ['status', 'marked_by', 'collect_time', 'updated_at'] // columns to update
        );

        return [
            'class_id' => $classId,
            'class_name' => $class->grade?->name . ($class->name ? ' ' . $class->name : ''),
            'date' => $date->format('Y-m-d'),
            'stats' => $stats,
            'attendance_id' => Str::uuid()->toString(),
        ];
    }

    public function bulkUpdateAttendance(User $teacher, array $data): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            throw new \Exception('Teacher profile not found');
        }

        $classId = $data['class_id'];
        $periodId = $data['current_period_id'] ?? null;
        $date = Carbon::parse($data['date']);
        $status = $data['status'];

        // Verify teacher has access (class teacher, period access, or in current_classes)
        $isClassTeacher = SchoolClass::where('id', $classId)
            ->where('teacher_id', $teacherProfile->id)
            ->exists();

        $hasPeriodAccess = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->exists();

        $class = SchoolClass::with(['enrolledStudents.user', 'grade'])->find($classId);
        $hasClassInProfile = false;
        if ($class && $teacherProfile->current_classes) {
            $currentClasses = is_array($teacherProfile->current_classes) 
                ? $teacherProfile->current_classes 
                : json_decode($teacherProfile->current_classes, true) ?? [];
            $className = $class->grade?->name . ($class->name ? ' ' . $class->name : '');
            $hasClassInProfile = in_array($class->name, $currentClasses) || in_array($className, $currentClasses);
        }

        if (!$isClassTeacher && !$hasPeriodAccess && !$hasClassInProfile) {
            throw new \Exception('Access denied to this class');
        }

        if (!$class) {
            throw new \Exception('Class not found');
        }

        $students = [];
        $collectTime = now()->format('H:i:s');
        $dateString = $date->toDateString();
        
        // Prepare data for bulk upsert (much faster than individual queries)
        $upsertData = [];
        foreach ($class->enrolledStudents as $student) {
            $upsertData[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'student_id' => $student->id,
                'date' => $dateString,
                'period_id' => $periodId,
                'status' => $status,
                'marked_by' => $teacher->id,
                'collect_time' => $collectTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $students[] = [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_id ?? $student->student_identifier ?? '',
                'avatar' => avatar_url($student->photo_path, 'student'),
                'status' => $status,
            ];
        }

        // Single query instead of N queries
        StudentAttendance::upsert(
            $upsertData,
            ['student_id', 'date', 'period_id'], // unique keys
            ['status', 'marked_by', 'collect_time', 'updated_at'] // columns to update
        );

        $stats = [
            'present' => $status === 'present' ? count($students) : 0,
            'absent' => $status === 'absent' ? count($students) : 0,
            'leave' => $status === 'leave' ? count($students) : 0,
        ];

        return [
            'students' => $students,
            'stats' => $stats,
        ];
    }

    public function getAttendanceHistory(User $teacher, ?string $filter, ?string $dateFilter = null): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return $this->getEmptyHistory();
        }

        // Get date range based on filter or specific date
        if ($dateFilter) {
            // If date_filter is provided, use that specific date
            $specificDate = Carbon::parse($dateFilter);
            $dateRange = [
                'start' => $specificDate->copy()->format('Y-m-d'),
                'end' => $specificDate->copy()->format('Y-m-d'),
            ];
        } elseif ($filter && $this->isDateString($filter)) {
            // If filter looks like a date (YYYY-MM-DD), treat it as a specific date
            $specificDate = Carbon::parse($filter);
            $dateRange = [
                'start' => $specificDate->copy()->format('Y-m-d'),
                'end' => $specificDate->copy()->format('Y-m-d'),
            ];
            $dateFilter = $filter; // Set dateFilter for response
        } else {
            $range = $this->getDateRange($filter ?? 'thisMonth');
            $dateRange = [
                'start' => $range['start']->format('Y-m-d'),
                'end' => $range['end']->format('Y-m-d'),
            ];
        }

        // Get periods taught by this teacher
        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.grade', 'subject', 'room'])
            ->get();

        $classIds = $periods->pluck('timetable.class_id')->unique()->filter();
        $periodIds = $periods->pluck('id')->filter();

        // Get attendance records grouped by date, class, and period
        // Only include records where period_id matches teacher's periods
        $query = StudentAttendance::whereIn('period_id', $periodIds)
            ->whereHas('student', fn($q) => $q->whereIn('class_id', $classIds))
            ->with(['student.classModel.grade', 'period.subject']);
        
        // Apply date filter
        if ($dateRange['start'] === $dateRange['end']) {
            // Single date query
            $query->whereDate('date', $dateRange['start']);
        } else {
            // Date range query
            $query->whereBetween('date', [$dateRange['start'], $dateRange['end']]);
        }
        
        $attendanceRecords = $query->get();
        
        // Group by date, class, and period to create separate records for each period
        $groupedRecords = $attendanceRecords->groupBy(fn($a) => $a->date->format('Y-m-d') . '_' . $a->student->class_id . '_' . $a->period_id);

        $records = [];
        foreach ($groupedRecords as $key => $group) {
            $firstRecord = $group->first();
            $class = $firstRecord->student?->classModel;
            
            // Get unique students for this date+class+period
            $uniqueStudents = $group->groupBy('student_id')->map(function ($studentRecords) {
                // For each student, get their latest attendance status
                return $studentRecords->sortByDesc('created_at')->first();
            });

            $present = $uniqueStudents->where('status', 'present')->count();
            $absent = $uniqueStudents->where('status', 'absent')->count();
            $leave = $uniqueStudents->where('status', 'leave')->count();
            $total = $uniqueStudents->count();
            $percentage = $total > 0 ? round(($present / $total) * 100) : 0;

            // Get the period for display purposes
            $period = $firstRecord->period;

            $records[] = [
                'id' => $key,
                'grade' => $class?->grade?->name . ($class?->name ? ' ' . $class->name : ''),
                'class_id' => $class?->id,
                'subject' => $period?->subject?->name ?? 'N/A',
                'date' => $firstRecord->date->format('Y-m-d'),
                'time' => $period ? Carbon::parse($period->starts_at)->format('H:i') . ' - ' . Carbon::parse($period->ends_at)->format('H:i') : 'N/A',
                'period' => $period ? 'Period ' . $period->period_number : 'N/A',
                'percentage' => $percentage,
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'total' => $total,
            ];
        }

        // Sort by date descending
        usort($records, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        $totalRecords = count($records);
        $avgPercentage = $totalRecords > 0 
            ? round(array_sum(array_column($records, 'percentage')) / $totalRecords) 
            : 0;

        return [
            'date_filter' => $dateFilter,
            'records' => array_slice($records, 0, 20),
            'summary' => [
                'total_records' => $totalRecords,
                'average_percentage' => $avgPercentage,
            ],
        ];
    }

    public function getAttendanceDetail(User $teacher, string $recordId): ?array
    {
        // Record ID format: date_classId
        $parts = explode('_', $recordId);
        if (count($parts) < 2) {
            return null;
        }

        $date = $parts[0];
        $classId = $parts[1];

        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return null;
        }

        // Verify teacher has access (class teacher, period access, or in current_classes)
        $isClassTeacher = SchoolClass::where('id', $classId)
            ->where('teacher_id', $teacherProfile->id)
            ->exists();

        $hasPeriodAccess = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->exists();

        $class = SchoolClass::with(['grade', 'enrolledStudents.user'])->find($classId);
        $hasClassInProfile = false;
        if ($class && $teacherProfile->current_classes) {
            $currentClasses = is_array($teacherProfile->current_classes) 
                ? $teacherProfile->current_classes 
                : json_decode($teacherProfile->current_classes, true) ?? [];
            $className = $class->grade?->name . ($class->name ? ' ' . $class->name : '');
            $hasClassInProfile = in_array($class->name, $currentClasses) || in_array($className, $currentClasses);
        }

        if (!$isClassTeacher && !$hasPeriodAccess && !$hasClassInProfile) {
            return null;
        }

        if (!$class) {
            return null;
        }

        $attendanceRecords = StudentAttendance::where('date', $date)
            ->whereIn('student_id', $class->enrolledStudents->pluck('id'))
            ->with(['student.user', 'period.subject'])
            ->get()
            ->keyBy('student_id');

        $period = $attendanceRecords->first()?->period;

        $present = $attendanceRecords->where('status', 'present')->count();
        $absent = $attendanceRecords->where('status', 'absent')->count();
        $leave = $attendanceRecords->where('status', 'leave')->count();
        $total = $attendanceRecords->count();
        $percentage = $total > 0 ? round(($present / $total) * 100) : 0;

        $students = $class->enrolledStudents->map(function ($student) use ($attendanceRecords) {
            $attendance = $attendanceRecords->get($student->id);
            return [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_identifier ?? '',
                'avatar' => avatar_url($student->photo_path, 'student'),
                'status' => $attendance?->status ?? 'unknown',
            ];
        });

        return [
            'record' => [
                'id' => $recordId,
                'grade' => $class->grade?->name . ($class->name ? ' ' . $class->name : ''),
                'class_id' => $class->id,
                'subject' => $period?->subject?->name ?? 'N/A',
                'date' => $date,
                'time' => $period ? Carbon::parse($period->starts_at)->format('H:i') . ' - ' . Carbon::parse($period->ends_at)->format('H:i') : 'N/A',
                'period' => $period ? 'Period ' . $period->period_number : 'N/A',
                'percentage' => $percentage,
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'total' => $total,
            ],
            'students' => $students->values()->toArray(),
        ];
    }

    private function getDateRange(string $filter): array
    {
        $today = now();

        return match ($filter) {
            'today' => ['start' => $today->copy()->startOfDay(), 'end' => $today->copy()->endOfDay()],
            'thisWeek' => ['start' => $today->copy()->startOfWeek(), 'end' => $today->copy()->endOfWeek()],
            'thisMonth' => ['start' => $today->copy()->startOfMonth(), 'end' => $today->copy()->endOfMonth()],
            'lastMonth' => ['start' => $today->copy()->subMonth()->startOfMonth(), 'end' => $today->copy()->subMonth()->endOfMonth()],
            default => ['start' => $today->copy()->startOfMonth(), 'end' => $today->copy()->endOfMonth()],
        };
    }

    private function isDateString(string $value): bool
    {
        // Check if the string matches date format (YYYY-MM-DD)
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function getEmptyHistory(): array
    {
        return [
            'date_filter' => null,
            'records' => [],
            'summary' => ['total_records' => 0, 'average_percentage' => 0],
        ];
    }
}
