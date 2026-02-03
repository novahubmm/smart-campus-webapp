<?php

namespace App\Repositories\Teacher;

use App\Interfaces\Teacher\TeacherDashboardRepositoryInterface;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherDashboardRepository implements TeacherDashboardRepositoryInterface
{
    public function getQuickStats(User $teacher): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return $this->getEmptyStats();
        }

        // Get classes taught by this teacher
        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.students', 'timetable.schoolClass.grade'])
            ->get();

        $classIds = $periods->pluck('timetable.class_id')->unique()->filter();
        $classes = SchoolClass::whereIn('id', $classIds)->with(['students', 'grade'])->get();

        $totalStudents = $classes->sum(fn($class) => $class->students->count());
        $totalClasses = $classes->count();

        // Get today's classes count
        $today = strtolower(now()->format('l'));
        $todayClasses = $periods->where('day_of_week', $today)->count();

        // Weekly classes
        $weeklyClasses = $periods->count();

        // Pending items (placeholder - can be expanded)
        $pendingLeaveRequests = 0; // TODO: implement when leave request feature is ready
        $pendingHomework = 0; // TODO: implement when homework feature is ready
        $pendingAttendance = 0; // TODO: implement

        return [
            'students' => $totalStudents,
            'classes' => $totalClasses,
            'pending' => $pendingLeaveRequests + $pendingHomework + $pendingAttendance,
            'details' => [
                'students_breakdown' => [
                    'total' => $totalStudents,
                    'by_class' => $classes->map(fn($class) => [
                        'class' => $class->grade?->name . ($class->name ? '-' . $class->name : ''),
                        'count' => $class->students->count(),
                    ])->values()->toArray(),
                ],
                'classes_breakdown' => [
                    'total' => $totalClasses,
                    'today' => $todayClasses,
                    'weekly' => $weeklyClasses,
                ],
                'pending_breakdown' => [
                    'total' => $pendingLeaveRequests + $pendingHomework + $pendingAttendance,
                    'leave_requests' => $pendingLeaveRequests,
                    'homework_to_review' => $pendingHomework,
                    'attendance_pending' => $pendingAttendance,
                ],
            ],
        ];
    }

    public function getTodayClasses(User $teacher): Collection
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return collect();
        }

        $today = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i');

        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->where('day_of_week', $today)
            ->where('is_break', false)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with([
                'timetable.schoolClass.students',
                'timetable.schoolClass.grade',
                'subject',
                'room',
            ])
            ->orderBy('starts_at')
            ->get();

        return $periods->map(function ($period) use ($currentTime) {
            $startTime = $period->starts_at instanceof \Carbon\Carbon 
                ? $period->starts_at->format('H:i') 
                : (is_string($period->starts_at) ? substr($period->starts_at, 0, 5) : '00:00');
            $endTime = $period->ends_at instanceof \Carbon\Carbon 
                ? $period->ends_at->format('H:i') 
                : (is_string($period->ends_at) ? substr($period->ends_at, 0, 5) : '00:00');
            
            $status = 'upcoming';
            if ($currentTime >= $startTime && $currentTime < $endTime) {
                $status = 'ongoing';
            } elseif ($currentTime >= $endTime) {
                $status = 'completed';
            }

            $class = $period->timetable?->schoolClass;
            $totalStudents = $class?->students?->count() ?? 0;

            // Get attendance for this period if completed
            $attendance = $this->getPeriodAttendance($period, $class);

            return [
                'id' => $period->id,
                'class_id' => $class?->id,
                'grade' => $class?->grade?->name . ($class?->name ? '-' . $class->name : ''),
                'subject' => $period->subject?->name ?? 'N/A',
                'time' => $startTime . ' - ' . $endTime,
                'status' => $status,
                'period' => 'P' . $period->period_number,
                'room' => $period->room?->name ?? 'N/A',
                'chapter' => $period->notes ?? '',
                'present' => $attendance['present'],
                'absent' => $attendance['absent'],
                'leave' => $attendance['leave'],
                'total_students' => $totalStudents,
                'timetable_version' => $period->timetable?->version,
            ];
        });
    }

    public function getTodayClassDetail(User $teacher, string $periodId): ?array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return null;
        }

        $period = Period::where('id', $periodId)
            ->where('teacher_profile_id', $teacherProfile->id)
            ->with([
                'timetable.schoolClass.students.user',
                'timetable.schoolClass.grade',
                'subject',
                'room',
            ])
            ->first();

        if (!$period) {
            return null;
        }

        $class = $period->timetable?->schoolClass;
        $students = $class?->students ?? collect();
        $totalStudents = $students->count();

        $currentTime = now()->format('H:i');
        
        // Handle time parsing - could be Carbon object or string
        $startTime = $period->starts_at instanceof \Carbon\Carbon 
            ? $period->starts_at->format('H:i') 
            : (is_string($period->starts_at) ? substr($period->starts_at, 0, 5) : '00:00');
        $endTime = $period->ends_at instanceof \Carbon\Carbon 
            ? $period->ends_at->format('H:i') 
            : (is_string($period->ends_at) ? substr($period->ends_at, 0, 5) : '00:00');

        $status = 'upcoming';
        if ($currentTime >= $startTime && $currentTime < $endTime) {
            $status = 'ongoing';
        } elseif ($currentTime >= $endTime) {
            $status = 'completed';
        }

        $attendance = $this->getPeriodAttendance($period, $class);

        return [
            'id' => $period->id,
            'class_id' => $class?->id,
            'grade' => $class?->grade?->name . ($class?->name ? '-' . $class->name : ''),
            'subject' => $period->subject?->name ?? 'N/A',
            'time' => $startTime . ' - ' . $endTime,
            'date' => now()->format('M d, Y'),
            'status' => $status,
            'period' => 'P' . $period->period_number,
            'room' => $period->room?->name ?? 'N/A',
            'chapter' => $period->notes ?? '',
            'present' => $attendance['present'],
            'absent' => $attendance['absent'],
            'leave' => $attendance['leave'],
            'total_students' => $totalStudents,
            'notes' => $period->notes,
            'homework_assigned' => false, // TODO: implement when homework feature is ready
            'homework_title' => null,
            'timetable_version' => $period->timetable?->version,
            'students' => $students->map(fn($student) => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_number' => $student->student_identifier ?? '',
                'avatar' => avatar_url($student->photo_path, 'student'),
                'attendance_status' => 'present', // TODO: get actual attendance
            ])->values()->toArray(),
        ];
    }

    public function getWeeklySchedule(User $teacher): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return $this->getEmptyWeeklySchedule();
        }

        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->where('is_break', false)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.grade', 'subject'])
            ->get();

        $times = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00'];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dayMap = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat',
        ];

        $schedule = [];
        $legend = [];
        $colors = $this->getClassColors();

        foreach ($days as $day) {
            $schedule[$day] = [];
            foreach ($times as $time) {
                $schedule[$day][$time] = null;
            }
        }

        foreach ($periods as $period) {
            $dayKey = $dayMap[$period->day_of_week] ?? null;
            if (!$dayKey) continue;

            $startHour = $period->starts_at instanceof \Carbon\Carbon 
                ? $period->starts_at->format('H:00') 
                : (is_string($period->starts_at) ? substr($period->starts_at, 0, 2) . ':00' : '00:00');
            if (!in_array($startHour, $times)) continue;

            $class = $period->timetable?->schoolClass;
            $gradeName = $class?->grade?->level . ($class?->name ?? '');
            $colorIndex = crc32($gradeName) % count($colors);
            $color = $colors[$colorIndex];

            $schedule[$dayKey][$startHour] = [
                'grade' => $gradeName,
                'subject' => $this->getSubjectShortName($period->subject?->name),
                'color' => $color['color'],
                'bg_color' => $color['bg_color'],
                'border_color' => $color['border_color'],
            ];

            if (!isset($legend[$gradeName])) {
                $legend[$gradeName] = [
                    'key' => $gradeName,
                    'color' => $color['color'],
                    'bg_color' => $color['bg_color'],
                ];
            }
        }

        return [
            'times' => $times,
            'days' => $days,
            'schedule' => $schedule,
            'legend' => array_values($legend),
        ];
    }

    public function getFullSchedule(User $teacher): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return $this->getEmptyFullSchedule();
        }

        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->where('is_break', false)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.grade', 'subject', 'room'])
            ->orderBy('starts_at')
            ->get();

        $today = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i');
        $colors = $this->getClassColors();

        $todayClasses = $periods->where('day_of_week', $today)->count();
        $weeklyTotal = $periods->count();
        $freePeriods = max(0, 30 - $weeklyTotal); // Assuming 30 periods per week max

        $days = [
            ['key' => 'monday', 'label' => 'Mon'],
            ['key' => 'tuesday', 'label' => 'Tue'],
            ['key' => 'wednesday', 'label' => 'Wed'],
            ['key' => 'thursday', 'label' => 'Thu'],
            ['key' => 'friday', 'label' => 'Fri'],
            ['key' => 'saturday', 'label' => 'Sat'],
        ];

        $timetable = [];
        foreach ($days as $day) {
            $dayPeriods = $periods->where('day_of_week', $day['key']);
            $timetable[$day['key']] = $dayPeriods->map(function ($period) use ($today, $currentTime, $colors) {
                $class = $period->timetable?->schoolClass;
                $gradeName = $class?->grade?->level . ($class?->name ?? '');
                $colorIndex = crc32($gradeName) % count($colors);
                $color = $colors[$colorIndex];

                $startTime = $period->starts_at instanceof \Carbon\Carbon 
                    ? $period->starts_at->format('H:i') 
                    : (is_string($period->starts_at) ? substr($period->starts_at, 0, 5) : '00:00');
                $endTime = $period->ends_at instanceof \Carbon\Carbon 
                    ? $period->ends_at->format('H:i') 
                    : (is_string($period->ends_at) ? substr($period->ends_at, 0, 5) : '00:00');

                $status = 'upcoming';
                if ($period->day_of_week === $today) {
                    if ($currentTime >= $startTime && $currentTime < $endTime) {
                        $status = 'ongoing';
                    } elseif ($currentTime >= $endTime) {
                        $status = 'completed';
                    }
                }

                return [
                    'id' => $period->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'grade' => $class?->grade?->name . ($class?->name ? '-' . $class->name : ''),
                    'subject' => $period->subject?->name ?? 'N/A',
                    'room' => $period->room?->name ?? 'N/A',
                    'status' => $status,
                    'timetable_version' => $period->timetable?->version,
                    'colors' => [
                        'text' => $color['color'],
                        'bg' => $color['bg_color'],
                        'border' => $color['border_color'],
                        'shadow' => $color['color'],
                    ],
                ];
            })->values()->toArray();
        }

        return [
            'stats' => [
                'today_classes' => $todayClasses,
                'weekly_total' => $weeklyTotal,
                'free_periods' => $freePeriods,
            ],
            'days' => $days,
            'timetable' => $timetable,
        ];
    }

    private function getPeriodAttendance(Period $period, ?SchoolClass $class): array
    {
        if (!$class) {
            return ['present' => 0, 'absent' => 0, 'leave' => 0];
        }

        // TODO: Implement actual attendance lookup
        return ['present' => 0, 'absent' => 0, 'leave' => 0];
    }

    private function getEmptyStats(): array
    {
        return [
            'students' => 0,
            'classes' => 0,
            'pending' => 0,
            'details' => [
                'students_breakdown' => ['total' => 0, 'by_class' => []],
                'classes_breakdown' => ['total' => 0, 'today' => 0, 'weekly' => 0],
                'pending_breakdown' => ['total' => 0, 'leave_requests' => 0, 'homework_to_review' => 0, 'attendance_pending' => 0],
            ],
        ];
    }

    private function getEmptyWeeklySchedule(): array
    {
        return [
            'times' => ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00'],
            'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'schedule' => [],
            'legend' => [],
        ];
    }

    private function getEmptyFullSchedule(): array
    {
        return [
            'stats' => ['today_classes' => 0, 'weekly_total' => 0, 'free_periods' => 0],
            'days' => [],
            'timetable' => [],
        ];
    }

    private function getSubjectShortName(?string $name): string
    {
        if (!$name) return 'N/A';
        
        $shortNames = [
            'Mathematics' => 'Math',
            'English' => 'Eng',
            'Science' => 'Sci',
            'Myanmar' => 'Mya',
            'History' => 'His',
            'Geography' => 'Geo',
            'Physics' => 'Phy',
            'Chemistry' => 'Chem',
            'Biology' => 'Bio',
        ];

        return $shortNames[$name] ?? substr($name, 0, 4);
    }

    private function getClassColors(): array
    {
        return [
            ['color' => '#E53935', 'bg_color' => '#FFE4E6', 'border_color' => '#FECACA'],
            ['color' => '#1976D2', 'bg_color' => '#DBEAFE', 'border_color' => '#BFDBFE'],
            ['color' => '#7B1FA2', 'bg_color' => '#EDE9FE', 'border_color' => '#DDD6FE'],
            ['color' => '#F57C00', 'bg_color' => '#FEF3C7', 'border_color' => '#FDE68A'],
            ['color' => '#388E3C', 'bg_color' => '#DCFCE7', 'border_color' => '#BBF7D0'],
            ['color' => '#0097A7', 'bg_color' => '#CFFAFE', 'border_color' => '#A5F3FC'],
        ];
    }
}
