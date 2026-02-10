<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRemark;
use App\Models\Homework;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\StudentRemark;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get Quick Stats for Dashboard
     * GET /api/v1/teacher/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            // Get today's day in full format (monday, tuesday, etc.)
            $todayFull = strtolower($today->format('l')); // monday, tuesday, etc.

            // Get today's classes count
            $todayClasses = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('day_of_week', $todayFull)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->count();

            // Get weekly total classes
            $weeklyTotal = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->count();

            // Calculate free periods for today (assuming 7 periods per day)
            $totalPeriodsPerDay = 7;
            $freePeriods = $totalPeriodsPerDay - $todayClasses;

            return response()->json([
                'success' => true,
                'data' => [
                    'today_classes' => $todayClasses,
                    'weekly_total' => $weeklyTotal,
                    'free_periods' => max(0, $freePeriods)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Today's Classes
     * GET /api/v1/teacher/today-classes
     */
    public function todayClasses(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $today = Carbon::today();
            $currentTime = Carbon::now();
            $todayFull = strtolower($today->format('l'));

            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('day_of_week', $todayFull)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->with([
                    'timetable.schoolClass.grade',
                    'timetable.schoolClass.enrolledStudents',
                    'timetable.schoolClass.room',
                    'subject.curriculumChapters.topics'
                ])
                ->orderBy('period_number')
                ->get();

            $classes = $periods->map(function ($period) use ($currentTime, $today) {
                $startTime = $period->starts_at;
                $endTime = $period->ends_at;
                
                // Determine status
                $status = 'upcoming';
                $nowTime = Carbon::createFromFormat('H:i', $currentTime->format('H:i'));
                $periodStart = Carbon::createFromFormat('H:i', is_string($startTime) ? substr($startTime, 0, 5) : $startTime->format('H:i'));
                $periodEnd = Carbon::createFromFormat('H:i', is_string($endTime) ? substr($endTime, 0, 5) : $endTime->format('H:i'));
                
                if ($nowTime->greaterThan($periodEnd)) {
                    $status = 'completed';
                } elseif ($nowTime->between($periodStart, $periodEnd)) {
                    $status = 'ongoing';
                }

                $class = $period->timetable?->schoolClass;
                $totalStudents = $class?->enrolledStudents?->count() ?? 0;
                
                // Get attendance for this specific period today
                $studentIds = $class?->enrolledStudents?->pluck('id') ?? collect();
                $attendance = \App\Models\StudentAttendance::whereIn('student_id', $studentIds)
                    ->where('period_id', $period->id)
                    ->whereDate('date', $today)
                    ->get();
                
                $present = $attendance->where('status', 'present')->count();
                $absent = $attendance->where('status', 'absent')->count();
                $leave = $attendance->where('status', 'leave')->count();
                $late = $attendance->where('status', 'late')->count();
                $isTaken = $attendance->count() > 0;

                // Format attendance data - show "-" for counts when not taken
                $attendanceData = $isTaken ? [
                    'present' => $present,
                    'absent' => $absent,
                    'leave' => $leave,
                    'late' => $late,
                    'total' => $totalStudents,
                    'is_taken' => true
                ] : [
                    'present' => '-',
                    'absent' => '-',
                    'leave' => '-',
                    'late' => '-',
                    'total' => $totalStudents,
                    'is_taken' => false
                ];

                // Get current chapter from curriculum
                $currentChapter = null;
                if ($period->subject) {
                    $classIdForProgress = $class?->id;
                    $chapters = $period->subject->curriculumChapters()->with(['topics.progress' => fn($q) => $q->where('class_id', $classIdForProgress)])->get();
                    foreach ($chapters as $chapter) {
                        $completedTopics = $chapter->topics->filter(fn($t) => $t->progress->where('status', 'completed')->count() > 0)->count();
                        $totalTopics = $chapter->topics->count();
                        if ($completedTopics < $totalTopics) {
                            $currentChapter = $chapter->title;
                            break;
                        }
                    }
                }

                return [
                    'period_id' => $period->id,
                    'class_id' => $class?->id,
                    'grade' => ($class?->grade?->name ?? 'Grade ' . ($class?->grade?->level ?? '')) . '-' . substr($class?->name ?? '', -1),
                    'subject' => $period->subject?->name ?? 'Unknown Subject',
                    'time' => format_time($startTime) . ' - ' . format_time($endTime),
                    'status' => $status,
                    'period' => 'P' . $period->period_number,
                    'room' => $class?->room?->name ?? $period->room?->name ?? 'Unknown Room',
                    'chapter' => $currentChapter,
                    'attendance' => $attendanceData,
                    'timetable_version' => $period->timetable?->version ?? 1,
                ];
            });
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $today->format('Y-m-d'),
                    'total_classes' => $classes->count(),
                    'classes' => $classes->values(),
                    'time_format' => get_time_format(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today\'s classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Today's Class Detail
     * GET /api/v1/teacher/today-classes/{id}
     */
    public function todayClassDetail($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $period = Period::where('id', $id)
                ->where('teacher_profile_id', $teacherProfile->id)
                ->with([
                    'timetable.schoolClass.grade',
                    'timetable.schoolClass.enrolledStudents.user',
                    'timetable.schoolClass.room',
                    'subject.curriculumChapters.topics',
                    'room'
                ])
                ->first();

            if (!$period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class not found'
                ], 404);
            }

            $today = Carbon::today();
            $currentTime = Carbon::now();
            $class = $period->timetable?->schoolClass;
            $classId = $class?->id;
            
            // Handle starts_at and ends_at
            $startsAt = $period->starts_at;
            $endsAt = $period->ends_at;
            $startTimeStr = is_string($startsAt) ? substr($startsAt, 0, 5) : $startsAt->format('H:i');
            $endTimeStr = is_string($endsAt) ? substr($endsAt, 0, 5) : $endsAt->format('H:i');
            
            // Determine status
            $status = 'upcoming';
            $nowTime = Carbon::createFromFormat('H:i', $currentTime->format('H:i'));
            $periodStart = Carbon::createFromFormat('H:i', $startTimeStr);
            $periodEnd = Carbon::createFromFormat('H:i', $endTimeStr);
            
            if ($nowTime->greaterThan($periodEnd)) {
                $status = 'completed';
            } elseif ($nowTime->between($periodStart, $periodEnd)) {
                $status = 'ongoing';
            }

            // Formatted time for display
            $formattedTime = format_time($startsAt) . ' - ' . format_time($endsAt);
            
            if ($nowTime->greaterThan($periodEnd)) {
                $status = 'completed';
            } elseif ($nowTime->between($periodStart, $periodEnd)) {
                $status = 'ongoing';
            }

            // Get students with attendance for this specific period
            $students = $class?->enrolledStudents->map(function ($student) use ($today, $period) {
                $attendance = \App\Models\StudentAttendance::where('student_id', $student->id)
                    ->where('period_id', $period->id)
                    ->whereDate('date', $today)
                    ->first();
                
                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Student',
                    'roll_no' => $student->student_identifier ?? $student->id,
                    'avatar' => strtoupper(substr($student->user?->name ?? 'S', 0, 1)),
                    'avatar_url' => null,
                    'attendance_status' => $attendance?->status ?? null,
                ];
            }) ?? collect();

            $totalStudents = $students->count();
            
            // Attendance summary
            $attendanceCounts = $students->groupBy('attendance_status');
            $present = $attendanceCounts->get('present', collect())->count();
            $absent = $attendanceCounts->get('absent', collect())->count();
            $leave = $attendanceCounts->get('leave', collect())->count();
            $late = $attendanceCounts->get('late', collect())->count();
            $isTaken = $students->whereNotNull('attendance_status')->count() > 0;
            
            // Format attendance data - show "-" for counts when not taken
            $attendanceData = $isTaken ? [
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'late' => $late,
                'total' => $totalStudents,
                'is_taken' => true
            ] : [
                'present' => '-',
                'absent' => '-',
                'leave' => '-',
                'late' => '-',
                'total' => $totalStudents,
                'is_taken' => false
            ];

            // Curriculum data
            $curriculumData = $this->getCurriculumData($period->subject, $classId);

            // Class remarks for today
            $classRemarks = \App\Models\ClassRemark::where('class_id', $classId)
                ->whereDate('created_at', $today)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'text' => $r->remark,
                    'type' => $r->type,
                    'date' => $r->created_at->format('Y-m-d'),
                ]);

            // Student remarks for today
            $studentRemarks = \App\Models\StudentRemark::where('class_id', $classId)
                ->whereDate('created_at', $today)
                ->with('student.user')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($r) => [
                    'id' => $r->id,
                    'student_id' => $r->student_id,
                    'student_name' => $r->student?->user?->name ?? 'Unknown',
                    'student_avatar' => strtoupper(substr($r->student?->user?->name ?? 'S', 0, 1)),
                    'text' => $r->remark,
                    'type' => $r->type,
                    'date' => $r->created_at->format('Y-m-d'),
                ]);

            // Homework data
            $homeworkData = $this->getHomeworkData($classId, $period->subject_id, $today, $status);

            $response = [
                'period_id' => $period->id,
                'class_id' => $classId,
                'grade' => ($class?->grade?->name ?? 'Grade ' . ($class?->grade?->level ?? '')) . '-' . substr($class?->name ?? '', -1),
                'subject' => $period->subject?->name ?? 'Unknown Subject',
                'subject_id' => $period->subject_id,
                'time' => $formattedTime,
                'date' => $today->format('Y-m-d'),
                'status' => $status,
                'period' => 'P' . $period->period_number,
                'room' => $class?->room?->name ?? $period->room?->name ?? 'Unknown Room',
                'total_students' => $totalStudents,
                'attendance' => $attendanceData,
                'curriculum' => $curriculumData,
                'class_remarks' => $classRemarks,
                'student_remarks' => $studentRemarks,
                'homework' => $homeworkData,
                'students' => $students->values(),
                'time_format' => get_time_format(),
            ];

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get curriculum data for a subject
     */
    private function getCurriculumData($subject, $classId): array
    {
        if (!$subject) {
            return [
                'current_chapter' => null,
                'total_chapters' => 0,
                'completed_chapters' => 0,
                'total_topics' => 0,
                'completed_topics' => 0,
                'progress_percentage' => 0,
                'chapters' => [],
            ];
        }

        $chapters = $subject->curriculumChapters()->with(['topics' => function ($q) use ($classId) {
            $q->with(['progress' => fn($q2) => $q2->where('class_id', $classId)]);
        }])->orderBy('order')->get();

        $totalTopics = 0;
        $completedTopics = 0;
        $completedChapters = 0;
        $currentChapter = null;

        $chaptersData = $chapters->map(function ($chapter) use ($classId, &$totalTopics, &$completedTopics, &$completedChapters, &$currentChapter) {
            $chapterTopics = $chapter->topics->count();
            $chapterCompleted = $chapter->topics->filter(fn($t) => $t->progress->where('class_id', $classId)->where('status', 'completed')->count() > 0)->count();
            
            $totalTopics += $chapterTopics;
            $completedTopics += $chapterCompleted;

            $chapterStatus = 'upcoming';
            if ($chapterCompleted === $chapterTopics && $chapterTopics > 0) {
                $chapterStatus = 'completed';
                $completedChapters++;
            } elseif ($chapterCompleted > 0) {
                $chapterStatus = 'current';
                if (!$currentChapter) {
                    $currentChapter = $chapter->title;
                }
            }

            return [
                'id' => $chapter->id,
                'title' => $chapter->title,
                'topics' => $chapterTopics,
                'completed_topics' => $chapterCompleted,
                'status' => $chapterStatus,
                'subtopics' => $chapter->topics->map(fn($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'completed' => $t->progress->where('class_id', $classId)->where('status', 'completed')->count() > 0,
                ])->values(),
            ];
        });

        // If no current chapter found, use first incomplete
        if (!$currentChapter && $chaptersData->count() > 0) {
            $firstIncomplete = $chaptersData->firstWhere('status', '!=', 'completed');
            $currentChapter = $firstIncomplete['title'] ?? $chaptersData->first()['title'] ?? null;
        }

        $progressPercentage = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;

        return [
            'current_chapter' => $currentChapter,
            'total_chapters' => $chapters->count(),
            'completed_chapters' => $completedChapters,
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'progress_percentage' => $progressPercentage,
            'chapters' => $chaptersData->values(),
        ];
    }

    /**
     * Get homework data for a class
     */
    private function getHomeworkData($classId, $subjectId, $today, $status): array
    {
        $homework = \App\Models\Homework::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->with('submissions')
            ->get();

        $totalStudents = \App\Models\SchoolClass::find($classId)?->enrolledStudents?->count() ?? 0;

        if ($status === 'ongoing') {
            // For ongoing: pending_collection (due today) + assigned (future)
            $pendingCollection = $homework->filter(fn($h) => $h->due_date && $h->due_date->isToday())
                ->map(fn($h) => [
                    'id' => $h->id,
                    'title' => $h->title,
                    'due_date' => $h->due_date->format('Y-m-d'),
                    'is_due_today' => true,
                    'submitted' => $h->submissions->count(),
                    'total' => $totalStudents,
                ])->values();

            $assigned = $homework->filter(fn($h) => $h->due_date && $h->due_date->isAfter($today))
                ->map(fn($h) => [
                    'id' => $h->id,
                    'title' => $h->title,
                    'due_date' => $h->due_date->format('Y-m-d'),
                    'submitted' => $h->submissions->count(),
                    'total' => $totalStudents,
                ])->values();

            return [
                'pending_collection' => $pendingCollection,
                'assigned' => $assigned,
            ];
        } elseif ($status === 'upcoming') {
            // For upcoming: due_today + upcoming
            $dueToday = $homework->filter(fn($h) => $h->due_date && $h->due_date->isToday())
                ->map(fn($h) => [
                    'id' => $h->id,
                    'title' => $h->title,
                    'due_date' => $h->due_date->format('Y-m-d'),
                    'submitted' => $h->submissions->count(),
                    'total' => $totalStudents,
                ])->values();

            $upcoming = $homework->filter(fn($h) => $h->due_date && $h->due_date->isAfter($today))
                ->map(fn($h) => [
                    'id' => $h->id,
                    'title' => $h->title,
                    'due_date' => $h->due_date->format('Y-m-d'),
                    'submitted' => $h->submissions->count(),
                    'total' => $totalStudents,
                ])->values();

            return [
                'due_today' => $dueToday,
                'upcoming' => $upcoming,
            ];
        } else {
            // For completed: homework_assigned + homework_collected
            $homeworkAssigned = $homework->filter(fn($h) => $h->created_at->isToday())->first();
            $homeworkCollected = $homework->filter(fn($h) => $h->due_date && $h->due_date->isToday())
                ->map(fn($h) => [
                    'id' => $h->id,
                    'title' => $h->title,
                    'submitted' => $h->submissions->count(),
                    'not_submitted' => $totalStudents - $h->submissions->count(),
                    'total' => $totalStudents,
                ])->values();

            return [
                'homework_assigned' => $homeworkAssigned ? [
                    'id' => $homeworkAssigned->id,
                    'title' => $homeworkAssigned->title,
                    'due_date' => $homeworkAssigned->due_date?->format('Y-m-d'),
                ] : null,
                'homework_collected' => $homeworkCollected,
            ];
        }
    }

    /**
     * Get Weekly Schedule Grid (Dashboard)
     * GET /api/v1/teacher/schedule/weekly
     */
    public function weeklySchedule(): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            // Get all periods for this teacher
            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->with([
                    'timetable.schoolClass.grade',
                    'subject'
                ])
                ->get();

            // Define time slots in 24h format (for internal use)
            $times24h = ['08:00', '08:45', '09:30', '11:15', '12:00', '12:45', '13:30'];
            // Format times for output based on setting
            $times = array_map(fn($t) => format_time($t), $times24h);
            
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            $dayMapping = [
                'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 
                'thursday' => 'Thu', 'friday' => 'Fri'
            ];

            // Initialize schedule grid with formatted times
            $schedule = [];
            foreach ($days as $day) {
                $schedule[$day] = [];
                foreach ($times as $time) {
                    $schedule[$day][$time] = null;
                }
            }

            // Color palette for different classes
            $colors = [
                '#E53935', '#1976D2', '#7B1FA2', '#F57C00', 
                '#388E3C', '#D32F2F', '#1565C0', '#7B1FA2'
            ];
            $colorIndex = 0;
            $classColors = [];
            $legend = [];

            // Fill the schedule grid
            foreach ($periods as $period) {
                $dayOfWeek = $period->day_of_week; // This should be 'monday', 'tuesday', etc.
                $dayName = $dayMapping[$dayOfWeek] ?? null;
                
                if (!$dayName) continue;

                // Format time using global setting
                $startTime = format_time($period->starts_at);
                $grade = $period->timetable?->schoolClass?->grade?->name ?? 'Grade ' . ($period->timetable?->schoolClass?->grade?->level ?? 'Unknown');
                $subject = $period->subject?->name ?? 'Unknown';

                // Assign color to class if not already assigned
                if (!isset($classColors[$grade])) {
                    $classColors[$grade] = $colors[$colorIndex % count($colors)];
                    $legend[] = [
                        'key' => $grade,
                        'color' => $classColors[$grade]
                    ];
                    $colorIndex++;
                }

                if (in_array($startTime, $times)) {
                    $schedule[$dayName][$startTime] = [
                        'grade' => $grade,
                        'subject' => $subject,
                        'color' => $classColors[$grade]
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'times' => $times,
                    'days' => $days,
                    'schedule' => $schedule,
                    'legend' => $legend,
                    'time_format' => get_time_format()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weekly schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Full Timetable (Full Screen with Stats)
     * GET /api/v1/teacher/schedule/full
     */
    public function fullSchedule(): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $today = Carbon::today();
            $currentTime = Carbon::now();
            
            // Log for debugging
            \Log::info('Full Schedule Debug', [
                'today' => $today->format('Y-m-d'),
                'current_time' => $currentTime->format('H:i:s'),
                'day_name' => strtolower($today->format('l'))
            ]);

            // Get stats
            $todayClasses = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('day_of_week', strtolower($today->format('l'))) // Use full day name
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->count();

            $weeklyTotal = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->count();

            $freePeriods = max(0, 7 - $todayClasses); // Assuming 7 periods per day

            // Get all periods grouped by day
            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->with([
                    'timetable.schoolClass.grade',
                    'subject',
                    'room'
                ])
                ->get()
                ->groupBy('day_of_week'); // Group by the day_of_week field in periods table

            // Define days
            $days = [
                ['key' => 'monday', 'label' => 'Mon'],
                ['key' => 'tuesday', 'label' => 'Tue'],
                ['key' => 'wednesday', 'label' => 'Wed'],
                ['key' => 'thursday', 'label' => 'Thu'],
                ['key' => 'friday', 'label' => 'Fri'],
                ['key' => 'saturday', 'label' => 'Sat']
            ];

            $dayMapping = [
                'monday' => 'monday', 'tuesday' => 'tuesday', 'wednesday' => 'wednesday',
                'thursday' => 'thursday', 'friday' => 'friday', 'saturday' => 'saturday'
            ];

            // Color schemes for different grades
            $colorSchemes = [
                ['text' => '#B91C1C', 'border' => '#F87171', 'shadow' => '#EF4444'],
                ['text' => '#1E40AF', 'border' => '#60A5FA', 'shadow' => '#3B82F6'],
                ['text' => '#7C2D92', 'border' => '#A78BFA', 'shadow' => '#8B5CF6'],
                ['text' => '#D97706', 'border' => '#FBBF24', 'shadow' => '#F59E0B'],
                ['text' => '#059669', 'border' => '#34D399', 'shadow' => '#10B981'],
                ['text' => '#374151', 'border' => '#9CA3AF', 'shadow' => '#6B7280']
            ];

            $gradeColors = [];
            $colorIndex = 0;

            // Build timetable
            $timetable = [];
            foreach ($days as $day) {
                $dayKey = $day['key'];
                
                $dayPeriods = $periods->get($dayKey, collect());
                
                // Group by start time to avoid duplicates (teacher may teach same subject in multiple classes at same time)
                $uniquePeriods = $dayPeriods->groupBy(fn($p) => $p->starts_at->format('H:i'))
                    ->map(function ($periodsAtTime) use (&$gradeColors, &$colorIndex, $colorSchemes, $currentTime, $today) {
                        // Take the first period for this time slot
                        $period = $periodsAtTime->first();
                        
                        // Get all classes at this time
                        $classNames = $periodsAtTime->map(fn($p) => $p->timetable?->schoolClass?->name)->filter()->unique()->values();
                        $grade = $period->timetable?->schoolClass?->grade?->name ?? 'Grade ' . ($period->timetable?->schoolClass?->grade?->level ?? 'Unknown');
                        
                        // Assign color scheme to grade
                        if (!isset($gradeColors[$grade])) {
                            $gradeColors[$grade] = $colorSchemes[$colorIndex % count($colorSchemes)];
                            $colorIndex++;
                        }

                        // Determine status
                        $status = 'upcoming';
                        $periodDayName = $period->day_of_week;
                        $todayDayName = strtolower($today->format('l'));
                        
                        if ($periodDayName === $todayDayName) {
                            $nowTime = Carbon::createFromFormat('H:i', $currentTime->format('H:i'));
                            $periodStart = Carbon::createFromFormat('H:i', $period->starts_at->format('H:i'));
                            $periodEnd = Carbon::createFromFormat('H:i', $period->ends_at->format('H:i'));
                            
                            // Log for debugging
                            \Log::info('Period Status Check', [
                                'period_id' => $period->id,
                                'period_day' => $periodDayName,
                                'today_day' => $todayDayName,
                                'now_time' => $nowTime->format('H:i'),
                                'period_start' => $periodStart->format('H:i'),
                                'period_end' => $periodEnd->format('H:i'),
                                'is_after_end' => $nowTime->greaterThanOrEqualTo($periodEnd),
                                'is_after_start' => $nowTime->greaterThanOrEqualTo($periodStart),
                                'is_before_end' => $nowTime->lessThan($periodEnd)
                            ]);
                            
                            if ($nowTime->greaterThanOrEqualTo($periodEnd)) {
                                $status = 'completed';
                            } elseif ($nowTime->greaterThanOrEqualTo($periodStart) && $nowTime->lessThan($periodEnd)) {
                                $status = 'ongoing';
                            }
                        }

                        // Use green colors for ongoing status, otherwise use grade colors
                        $colors = $status === 'ongoing' 
                            ? ['text' => '#059669', 'border' => '#34D399', 'shadow' => '#10B981']
                            : $gradeColors[$grade];

                        return [
                            'id' => $period->id,
                            'start_time' => format_time($period->starts_at),
                            'end_time' => format_time($period->ends_at),
                            'grade' => $grade,
                            'class' => $classNames->count() > 1 ? $classNames->implode(', ') : ($period->timetable?->schoolClass?->name ?? ''),
                            'subject' => $period->subject?->name ?? 'Unknown Subject',
                            'room' => $period->room?->name ?? 'Unknown Room',
                            'status' => $status,
                            'colors' => $colors
                        ];
                    })
                    ->sortBy('start_time')
                    ->values()
                    ->toArray();
                
                $timetable[$dayKey] = $uniquePeriods;

                // Add free periods if needed (fill gaps in schedule)
                $this->addFreePeriods($timetable[$dayKey]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'today_classes' => $todayClasses,
                        'weekly_total' => $weeklyTotal,
                        'free_periods' => $freePeriods
                    ],
                    'days' => $days,
                    'timetable' => $timetable,
                    'time_format' => get_time_format()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch full schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add free periods to fill gaps in the schedule
     */
    private function addFreePeriods(&$daySchedule)
    {
        // Match the times from DemoTimetableSeeder (in 24h format for comparison)
        $standardTimes = [
            ['start' => '08:00', 'end' => '08:45'],
            ['start' => '08:45', 'end' => '09:30'],
            ['start' => '09:30', 'end' => '10:15'],
            ['start' => '11:15', 'end' => '12:00'],
            ['start' => '12:00', 'end' => '12:45'],
            ['start' => '12:45', 'end' => '13:30'],
            ['start' => '13:30', 'end' => '14:15']
        ];

        // Get occupied times - need to normalize to 24h format for comparison
        $occupiedTimes24h = collect($daySchedule)->map(function ($item) {
            $startTime = $item['start_time'];
            // Convert 12h format back to 24h for comparison if needed
            if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $startTime, $matches)) {
                $hour = (int)$matches[1];
                $minute = $matches[2];
                $period = strtoupper($matches[3]);
                if ($period === 'PM' && $hour !== 12) {
                    $hour += 12;
                } elseif ($period === 'AM' && $hour === 12) {
                    $hour = 0;
                }
                return sprintf('%02d:%s', $hour, $minute);
            }
            return $startTime; // Already in 24h format
        })->toArray();

        foreach ($standardTimes as $timeSlot) {
            if (!in_array($timeSlot['start'], $occupiedTimes24h)) {
                $daySchedule[] = [
                    'id' => null,
                    'start_time' => format_time($timeSlot['start']),
                    'end_time' => format_time($timeSlot['end']),
                    'grade' => '',
                    'subject' => '',
                    'room' => '',
                    'status' => 'free',
                    'colors' => null
                ];
            }
        }

        // Sort by start time (need to handle both 12h and 24h formats)
        usort($daySchedule, function ($a, $b) {
            // Convert to 24h format for proper sorting
            $timeA = $this->convertTo24h($a['start_time']);
            $timeB = $this->convertTo24h($b['start_time']);
            return strcmp($timeA, $timeB);
        });
    }

    /**
     * Convert time string to 24h format for sorting
     */
    private function convertTo24h(string $time): string
    {
        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time, $matches)) {
            $hour = (int)$matches[1];
            $minute = $matches[2];
            $period = strtoupper($matches[3]);
            if ($period === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'AM' && $hour === 12) {
                $hour = 0;
            }
            return sprintf('%02d:%s', $hour, $minute);
        }
        return $time; // Already in 24h format
    }

    /**
     * Helper: Get period and validate teacher access
     */
    private function getPeriodForTeacher($periodId, $teacherProfileId)
    {
        return Period::where('id', $periodId)
            ->where('teacher_profile_id', $teacherProfileId)
            ->with(['timetable.schoolClass.enrolledStudents', 'subject'])
            ->first();
    }

    /**
     * 5. Take Attendance (ONGOING only)
     * POST /api/v1/teacher/today-classes/{id}/attendance
     */
    public function takeAttendance(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'attendance' => 'required|array',
                'attendance.*.student_id' => 'required|uuid',
                'attendance.*.status' => 'required|in:present,absent,leave,late',
            ]);

            $today = Carbon::today();
            $classId = $period->timetable?->schoolClass?->id;
            $counts = ['present' => 0, 'absent' => 0, 'leave' => 0, 'late' => 0];

            foreach ($request->attendance as $record) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'period_id' => $period->id,
                        'date' => $today,
                    ],
                    [
                        'status' => $record['status'],
                        'marked_by' => $user->id,
                        'collect_time' => now(),
                        'period_number' => $period->period_number,
                    ]
                );
                $counts[$record['status']]++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully',
                'data' => [
                    'present' => $counts['present'],
                    'absent' => $counts['absent'],
                    'leave' => $counts['leave'],
                    'late' => $counts['late'],
                    'total' => array_sum($counts),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to save attendance', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 6. Update Curriculum Progress (ONGOING only)
     * PUT /api/v1/teacher/today-classes/{id}/curriculum
     */
    public function updateCurriculum(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'chapter_id' => 'required|uuid',
                'subtopic_id' => 'required|uuid',
                'completed' => 'required|boolean',
            ]);

            $classId = $period->timetable?->schoolClass?->id;

            \App\Models\CurriculumProgress::updateOrCreate(
                [
                    'topic_id' => $request->subtopic_id,
                    'class_id' => $classId,
                    'teacher_id' => $teacherProfile->id,
                ],
                [
                    'status' => $request->completed ? 'completed' : 'in_progress',
                    'started_at' => now(),
                    'completed_at' => $request->completed ? now() : null,
                ]
            );

            // Calculate updated progress
            $chapter = \App\Models\CurriculumChapter::with(['topics' => function ($q) use ($classId) {
                $q->with(['progress' => fn($q2) => $q2->where('class_id', $classId)]);
            }])->find($request->chapter_id);

            $completedTopics = $chapter?->topics->filter(fn($t) => 
                $t->progress->where('status', 'completed')->count() > 0
            )->count() ?? 0;
            $totalTopics = $chapter?->topics->count() ?? 0;

            // Calculate overall progress for subject
            $subject = $period->subject;
            $allChapters = $subject?->curriculumChapters()->with(['topics' => function ($q) use ($classId) {
                $q->with(['progress' => fn($q2) => $q2->where('class_id', $classId)]);
            }])->get();

            $totalAllTopics = $allChapters?->sum(fn($c) => $c->topics->count()) ?? 0;
            $completedAllTopics = $allChapters?->sum(fn($c) => 
                $c->topics->filter(fn($t) => $t->progress->where('status', 'completed')->count() > 0)->count()
            ) ?? 0;
            $overallProgress = $totalAllTopics > 0 ? round(($completedAllTopics / $totalAllTopics) * 100) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Curriculum updated successfully',
                'data' => [
                    'chapter_id' => $request->chapter_id,
                    'completed_topics' => $completedTopics,
                    'total_topics' => $totalTopics,
                    'overall_progress' => $overallProgress,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update curriculum', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 7. Add Class Remark (ONGOING only)
     * POST /api/v1/teacher/today-classes/{id}/class-remarks
     */
    public function addClassRemark(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'text' => 'required|string|max:1000',
                'type' => 'required|in:positive,concern,note',
            ]);

            $classId = $period->timetable?->schoolClass?->id;

            $remark = ClassRemark::create([
                'class_id' => $classId,
                'teacher_id' => $teacherProfile->id,
                'subject_id' => $period->subject_id,
                'period_id' => $period->id,
                'date' => Carbon::today(),
                'remark' => $request->text,
                'type' => $request->type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Class remark added successfully',
                'data' => [
                    'id' => $remark->id,
                    'text' => $remark->remark,
                    'type' => $remark->type,
                    'date' => $remark->created_at->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add class remark', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 8. Add Student Remark (ONGOING only)
     * POST /api/v1/teacher/today-classes/{id}/student-remarks
     */
    public function addStudentRemark(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'student_id' => 'required|uuid|exists:student_profiles,id',
                'text' => 'required|string|max:1000',
                'type' => 'required|in:positive,concern,note',
            ]);

            $classId = $period->timetable?->schoolClass?->id;
            $student = \App\Models\StudentProfile::with('user')->find($request->student_id);

            $remark = StudentRemark::create([
                'student_id' => $request->student_id,
                'class_id' => $classId,
                'teacher_id' => $teacherProfile->id,
                'subject_id' => $period->subject_id,
                'period_id' => $period->id,
                'date' => Carbon::today(),
                'remark' => $request->text,
                'type' => $request->type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student remark added successfully',
                'data' => [
                    'id' => $remark->id,
                    'student_id' => $request->student_id,
                    'student_name' => $student?->user?->name ?? 'Unknown',
                    'student_avatar' => strtoupper(substr($student?->user?->name ?? 'S', 0, 1)),
                    'text' => $remark->remark,
                    'type' => $remark->type,
                    'date' => $remark->created_at->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add student remark', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 9. Assign Homework (ONGOING only)
     * POST /api/v1/teacher/today-classes/{id}/homework
     */
    public function assignHomework(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'required|date|after_or_equal:today',
            ]);

            $classId = $period->timetable?->schoolClass?->id;

            $homework = Homework::create([
                'class_id' => $classId,
                'subject_id' => $period->subject_id,
                'teacher_id' => $teacherProfile->id,
                'title' => $request->title,
                'description' => $request->description,
                'assigned_date' => now()->toDateString(),
                'due_date' => $request->due_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Homework assigned successfully',
                'data' => [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'description' => $homework->description,
                    'assigned_date' => $homework->assigned_date->format('Y-m-d'),
                    'due_date' => $homework->due_date->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign homework', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * 10. Collect Homework (ONGOING only)
     * PUT /api/v1/teacher/today-classes/{id}/homework/{homework_id}/collect
     */
    public function collectHomework(Request $request, $id, $homeworkId): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $homework = Homework::find($homeworkId);
            if (!$homework) {
                return response()->json(['success' => false, 'message' => 'Homework not found'], 404);
            }

            $request->validate([
                'submissions' => 'required|array',
                'submissions.*.student_id' => 'required|uuid',
                'submissions.*.status' => 'required|in:submitted,not_submitted',
            ]);

            $counts = ['submitted' => 0, 'not_submitted' => 0];

            foreach ($request->submissions as $submission) {
                \App\Models\HomeworkSubmission::updateOrCreate(
                    [
                        'homework_id' => $homeworkId,
                        'student_id' => $submission['student_id'],
                    ],
                    [
                        'status' => $submission['status'],
                        'submitted_at' => $submission['status'] !== 'not_submitted' ? now() : null,
                    ]
                );
                $counts[$submission['status']]++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Homework collection updated',
                'data' => [
                    'homework_id' => $homeworkId,
                    'submitted' => $counts['submitted'],
                    'not_submitted' => $counts['not_submitted'],
                    'late' => $counts['late'],
                    'total' => array_sum($counts),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to collect homework', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Class Summary (ONGOING only)
     * GET /api/v1/teacher/today-classes/{id}/summary
     */
    public function getClassSummary($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $classId = $period->timetable?->schoolClass?->id;
            $today = Carbon::today();

            // Get attendance summary
            $studentIds = $period->timetable?->schoolClass?->enrolledStudents?->pluck('id') ?? collect();
            $attendance = StudentAttendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $today)
                ->get();

            $attendanceData = [
                'status' => $attendance->count() > 0 ? 'collected' : 'not_collected',
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'leave' => $attendance->where('status', 'leave')->count(),
                'total' => $studentIds->count(),
            ];

            // Get curriculum/topics summary
            $subject = $period->subject;
            $completedTopics = 0;
            $totalTopics = 0;
            $currentChapter = null;

            if ($subject) {
                $chapters = $subject->curriculumChapters()->with(['topics' => function ($q) use ($classId) {
                    $q->with(['progress' => fn($q2) => $q2->where('class_id', $classId)]);
                }])->orderBy('order')->get();

                foreach ($chapters as $chapter) {
                    $chapterTopics = $chapter->topics->count();
                    $chapterCompleted = $chapter->topics->filter(fn($t) => 
                        $t->progress->where('class_id', $classId)->where('status', 'completed')->count() > 0
                    )->count();
                    
                    $totalTopics += $chapterTopics;
                    $completedTopics += $chapterCompleted;

                    // Find current chapter (first incomplete)
                    if (!$currentChapter && $chapterCompleted < $chapterTopics) {
                        $currentChapter = $chapter->title;
                    }
                }

                // If all chapters completed, use last chapter
                if (!$currentChapter && $chapters->count() > 0) {
                    $currentChapter = $chapters->last()->title;
                }
            }

            $topicsData = [
                'completed' => $completedTopics,
                'total' => $totalTopics,
                'current_chapter' => $currentChapter,
            ];

            // Get remarks summary
            $classRemarks = ClassRemark::where('class_id', $classId)
                ->whereDate('created_at', $today)
                ->orderByDesc('created_at')
                ->get();

            $studentRemarks = StudentRemark::where('class_id', $classId)
                ->whereDate('created_at', $today)
                ->orderByDesc('created_at')
                ->get();

            $totalRemarks = $classRemarks->count() + $studentRemarks->count();
            $latestRemark = null;

            if ($classRemarks->count() > 0) {
                $latestRemark = $classRemarks->first()->remark;
            } elseif ($studentRemarks->count() > 0) {
                $latestRemark = $studentRemarks->first()->remark;
            }

            $remarksData = [
                'count' => $totalRemarks,
                'latest' => $latestRemark,
            ];

            // Get homework summary
            $homework = Homework::where('class_id', $classId)
                ->where('subject_id', $period->subject_id)
                ->get();

            $assignedToday = $homework->filter(fn($h) => $h->created_at->isToday())->count();
            $dueToday = $homework->filter(fn($h) => $h->due_date && $h->due_date->isToday())->count();

            $homeworkData = [
                'assigned' => $assignedToday,
                'due_today' => $dueToday,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendanceData,
                    'topics' => $topicsData,
                    'remarks' => $remarksData,
                    'homework' => $homeworkData,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 11. End Class (ONGOING only)
     * PUT /api/v1/teacher/today-classes/{id}/end
     */
    public function endClass(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $period = $this->getPeriodForTeacher($id, $teacherProfile->id);
            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class not found'], 404);
            }

            $request->validate([
                'notes' => 'nullable|string|max:1000',
            ]);

            $classId = $period->timetable?->schoolClass?->id;
            $today = Carbon::today();

            // Store class session end record (using class_remarks with special type)
            if ($request->notes) {
                ClassRemark::create([
                    'class_id' => $classId,
                    'teacher_id' => $teacherProfile->id,
                    'subject_id' => $period->subject_id,
                    'period_id' => $period->id,
                    'date' => $today,
                    'remark' => $request->notes,
                    'type' => 'note',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Class ended successfully',
                'data' => [
                    'period_id' => $period->id,
                    'status' => 'completed',
                    'ended_at' => now()->toIso8601String(),
                    'notes' => $request->notes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to end class', 'error' => $e->getMessage()], 500);
        }
    }
}
