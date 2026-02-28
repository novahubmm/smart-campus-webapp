<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\Period;
use App\Models\TeacherProfile;
use App\Models\Timetable;
use App\Models\Homework;
use App\Models\CurriculumChapter;
use App\Models\CurriculumProgress;
use App\Models\ClassRemark;
use App\Models\StudentRemark;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OngoingClassController extends Controller
{
    /**
     * Virtual Campus View - Show all ongoing classes
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $gradeId = $request->get('grade_id');
        $selectedDate = Carbon::parse($date);
        $dayOfWeek = strtolower($selectedDate->format('l')); // Use full day name (monday, tuesday, etc.)

        $grades = Grade::orderBy('level')->get();

        // Get all classes with their active timetables
        $classesQuery = SchoolClass::with(['grade', 'teacher.user', 'room'])
            ->whereHas('grade', function ($q) {
                $q->whereHas('batch', fn($b) => $b->where('status', true));
            });

        if ($gradeId) {
            $classesQuery->where('grade_id', $gradeId);
        }

        $classes = $classesQuery->get();

        // Get current time info
        $currentTime = now()->format('H:i');
        $currentPeriodNumber = $this->getCurrentPeriodNumber($currentTime);

        // Build campus view data
        $campusData = [];
        foreach ($classes as $class) {
            $timetable = Timetable::where('class_id', $class->id)
                ->where('is_active', true)
                ->first();

            $periods = collect();
            if ($timetable) {
                $periods = Period::where('timetable_id', $timetable->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->orderBy('period_number')
                    ->with(['subject', 'teacher.user', 'room'])
                    ->get();
            }

            // Find current/ongoing period
            $currentPeriod = null;
            $periodStatus = 'upcoming';

            // Only calculate status for today
            if ($selectedDate->isToday() && $periods->count() > 0) {
                foreach ($periods as $period) {
                    $startTime = Carbon::parse($period->starts_at)->format('H:i');
                    $endTime = Carbon::parse($period->ends_at)->format('H:i');

                    if ($currentTime >= $startTime && $currentTime < $endTime) {
                        $currentPeriod = $period;
                        $periodStatus = 'ongoing';
                        break;
                    } elseif ($currentTime >= $endTime) {
                        $currentPeriod = $period;
                        $periodStatus = 'completed';
                    }
                }
            }

            $campusData[] = [
                'class' => $class,
                'timetable' => $timetable,
                'periods' => $periods,
                'current_period' => $currentPeriod,
                'period_status' => $periodStatus,
                'student_count' => $class->enrolledStudents()->count(),
            ];
        }

        return view('academic.ongoing-class', compact(
            'campusData',
            'grades',
            'selectedDate',
            'currentTime',
            'currentPeriodNumber',
            'gradeId'
        ));
    }

    /**
     * Get class detail with periods, curriculum, homework
     */
    public function classDetail(Request $request, SchoolClass $class)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $periodId = $request->get('period_id'); // Get selected period from URL
        $selectedDate = Carbon::parse($date);
        $dayOfWeek = strtolower($selectedDate->format('l')); // Use full day name

        $class->load(['grade', 'teacher.user', 'room', 'enrolledStudents.user']);

        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        $periods = [];
        if ($timetable) {
            $periods = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('period_number')
                ->with(['subject', 'teacher.user', 'room'])
                ->get();
        }

        // Get homework for this class
        $homework = Homework::where('class_id', $class->id)
            ->with(['subject', 'teacher.user'])
            ->orderBy('due_date', 'desc')
            ->limit(10)
            ->get();

        // Get curriculum progress for subjects in this class
        $subjects = $class->grade->subjects ?? collect();
        $curriculumData = [];

        foreach ($subjects as $subject) {
            $chapters = CurriculumChapter::where('subject_id', $subject->id)
                ->where(function ($q) use ($class) {
                    $q->where('grade_id', $class->grade_id)
                        ->orWhereNull('grade_id');
                })
                ->with(['topics.progress' => function ($q) use ($class) {
                    $q->where('class_id', $class->id);
                }])
                ->orderBy('order')
                ->get();

            $totalTopics = 0;
            $completedTopics = 0;

            foreach ($chapters as $chapter) {
                foreach ($chapter->topics as $topic) {
                    $totalTopics++;
                    if ($topic->progress->where('status', 'completed')->count() > 0) {
                        $completedTopics++;
                    }
                }
            }

            $curriculumData[] = [
                'subject' => $subject,
                'chapters' => $chapters,
                'total_topics' => $totalTopics,
                'completed_topics' => $completedTopics,
                'progress_percent' => $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0,
            ];
        }

        // Data for edit modal
        $grades = Grade::orderBy('level')->get();
        $rooms = Room::orderBy('name')->get();
        $teachers = TeacherProfile::with('user')
            ->where('status', 'active')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();
        
        // Students
        $students = $class->enrolledStudents;
        $totalStudents = $students->count();
        $totalTeachers = $teachers->count();
        
        // Build timetable data for the view
        $timetableWeekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $timetablePeriods = [];
        $timetableEntries = [];
        $timetablePeriodLabels = [];
        $uniqueTeachers = collect();
        
        if ($timetable) {
            $allPeriods = Period::where('timetable_id', $timetable->id)
                ->with(['subject', 'teacher.user', 'room'])
                ->orderBy('period_number')
                ->get();
            
            // Get unique period numbers
            $timetablePeriods = $allPeriods->pluck('period_number')->unique()->sort()->values()->toArray();
            
            // Build entries and labels
            foreach ($allPeriods as $period) {
                $day = $period->day_of_week;
                $periodNumber = $period->period_number;
                
                $timetableEntries[$day][$periodNumber] = [
                    'subject' => $period->subject?->name ?? ($period->is_break ? 'Break' : '-'),
                    'teacher' => $period->teacher?->user?->name ?? '-',
                    'room' => $period->room?->name ?? '-',
                    'is_break' => $period->is_break,
                ];
                
                if ($period->teacher) {
                    $uniqueTeachers->put($period->teacher->id, $period->teacher);
                }
                
                $start = $period->starts_at;
                $end = $period->ends_at;
                if ($start && $end && empty($timetablePeriodLabels[$periodNumber])) {
                    $startLabel = $start instanceof Carbon ? $start->format('H:i') : substr((string) $start, 0, 5);
                    $endLabel = $end instanceof Carbon ? $end->format('H:i') : substr((string) $end, 0, 5);
                    $timetablePeriodLabels[$periodNumber] = "{$startLabel} - {$endLabel}";
                }
            }
        }
        
        // Pagination
        $teachersPage = max(1, (int) $request->get('teachers_page', 1));
        $studentsPage = max(1, (int) $request->get('students_page', 1));
        $perPage = 10;
        
        $teachersPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $uniqueTeachers->forPage($teachersPage, $perPage)->values(),
            $uniqueTeachers->count(),
            $perPage,
            $teachersPage,
            ['path' => $request->url(), 'pageName' => 'teachers_page', 'query' => $request->query()]
        );
        
        $studentsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $students->forPage($studentsPage, $perPage)->values(),
            $students->count(),
            $perPage,
            $studentsPage,
            ['path' => $request->url(), 'pageName' => 'students_page', 'query' => $request->query()]
        );

        // Activity Summary for the selected date
        $activityData = $this->getActivitySummary($class, $selectedDate, $periodId);
        
        // Find selected period info for Activity Summary title
        $selectedPeriodInfo = null;
        if ($periodId && $activityData['timetable_periods']->count() > 0) {
            $selectedPeriodInfo = $activityData['timetable_periods']->firstWhere('id', $periodId);
        }

        return view('academic.class-detail', compact(
            'class',
            'timetable',
            'periods',
            'homework',
            'curriculumData',
            'selectedDate',
            'grades',
            'rooms',
            'teachers',
            'students',
            'totalStudents',
            'totalTeachers',
            'teachersPaginated',
            'studentsPaginated',
            'timetableEntries',
            'timetableWeekDays',
            'timetablePeriods',
            'timetablePeriodLabels',
            'activityData',
            'periodId',
            'selectedPeriodInfo'
        ));
    }

    /**
     * Get activity summary for a class on a specific date
     */
    private function getActivitySummary(SchoolClass $class, Carbon $date, $periodId = null): array
    {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = strtolower($date->format('l')); // Use 'l' for full day name (monday, tuesday, etc.)

        // Get timetable periods for this day
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        $timetablePeriods = collect();
        if ($timetable) {
            $allPeriods = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('period_number')
                ->with(['subject', 'teacher.user'])
                ->get();
            
            // Get period IDs that have attendance collected for this date
            $periodsWithAttendance = StudentAttendance::whereIn('period_id', $allPeriods->pluck('id'))
                ->whereDate('date', $dateStr)
                ->groupBy('period_id')
                ->pluck('period_id')
                ->toArray();
            
            $timetablePeriods = $allPeriods->map(function ($period) use ($periodsWithAttendance) {
                return [
                    'id' => $period->id,
                    'period_number' => $period->period_number,
                    'subject_name' => $period->subject?->name ?? ($period->is_break ? 'Break' : '—'),
                    'subject_id' => $period->subject_id,
                    'teacher_name' => $period->teacher?->user?->name ?? '—',
                    'starts_at' => $period->starts_at instanceof Carbon ? $period->starts_at->format('H:i') : substr((string) $period->starts_at, 0, 5),
                    'ends_at' => $period->ends_at instanceof Carbon ? $period->ends_at->format('H:i') : substr((string) $period->ends_at, 0, 5),
                    'is_break' => $period->is_break,
                    'has_attendance' => in_array($period->id, $periodsWithAttendance),
                ];
            });
        }

        // Attendance summary
        $totalStudents = $class->enrolledStudents->count();
        
        // Get periods for this class on the selected date
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        $periodIds = [];
        if ($timetable) {
            // If a specific period is selected, only get that period
            if ($periodId) {
                $periodIds = [$periodId];
            } else {
                // Otherwise get all periods for the day
                $periodIds = Period::where('timetable_id', $timetable->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->pluck('id')
                    ->toArray();
            }
        }

        // Get attendance records for these periods on the selected date
        $attendanceQuery = StudentAttendance::whereIn('period_id', $periodIds)
            ->whereDate('date', $dateStr);
        
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $absentCount = (clone $attendanceQuery)->where('status', 'absent')->count();
        $leaveCount = (clone $attendanceQuery)->where('status', 'leave')->count();
        $lateCount = (clone $attendanceQuery)->where('status', 'late')->count();

        // Get detailed attendance data for each student
        $attendanceRecords = StudentAttendance::whereIn('period_id', $periodIds)
            ->whereDate('date', $dateStr)
            ->with(['student.user', 'period.subject', 'markedByUser'])
            ->get()
            ->groupBy('student_id');

        // Build detailed attendance data
        $detailedAttendance = [];
        foreach ($class->enrolledStudents as $student) {
            $studentAttendance = $attendanceRecords->get($student->id, collect());
            
            $detailedAttendance[] = [
                'student_id' => $student->id,
                'student_name' => $student->user?->name ?? 'Unknown',
                'student_identifier' => $student->student_identifier,
                'attendance_records' => $studentAttendance->map(function ($record) {
                    return [
                        'period_id' => $record->period_id,
                        'period_number' => $record->period?->period_number,
                        'subject_name' => $record->period?->subject?->name ?? 'Unknown',
                        'status' => $record->status,
                        'remark' => $record->remark,
                        'collect_time' => $record->collect_time?->format('H:i'),
                        'marked_by' => $record->markedByUser?->name ?? 'System',
                    ];
                })->values()->toArray(),
                'overall_status' => $this->calculateOverallStatus($studentAttendance),
            ];
        }

        // Class remarks - include period relationship
        $classRemarks = ClassRemark::where('class_id', $class->id)
            ->whereDate('date', $dateStr)
            ->with(['teacher.user', 'subject', 'period'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Student remarks - include period relationship
        $studentRemarks = StudentRemark::where('class_id', $class->id)
            ->whereDate('date', $dateStr)
            ->with(['student.user', 'teacher.user', 'subject', 'period'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Curriculum progress updated on this date - make period-specific if period_id provided
        $curriculumUpdates = CurriculumProgress::where('class_id', $class->id)
            ->whereDate('updated_at', $dateStr);
        
        // If we have a specific period, filter by subject
        if ($periodId && $timetablePeriods->count() > 0) {
            $selectedPeriodData = $timetablePeriods->firstWhere('id', $periodId);
            if ($selectedPeriodData && $selectedPeriodData['subject_id']) {
                $curriculumUpdates->whereHas('topic.chapter', function($q) use ($selectedPeriodData) {
                    $q->where('subject_id', $selectedPeriodData['subject_id']);
                });
            }
        }
        
        $curriculumUpdates = $curriculumUpdates->with(['topic.chapter.subject'])->get();

        // Homework assigned on this date - make period-specific if period_id provided
        $homeworkAssigned = Homework::where('class_id', $class->id)
            ->whereDate('assigned_date', $dateStr);
            
        // If we have a specific period, filter by subject
        if ($periodId && $timetablePeriods->count() > 0) {
            $selectedPeriodData = $timetablePeriods->firstWhere('id', $periodId);
            if ($selectedPeriodData && $selectedPeriodData['subject_id']) {
                $homeworkAssigned->where('subject_id', $selectedPeriodData['subject_id']);
            }
        }
        
        $homeworkAssigned = $homeworkAssigned->with(['subject', 'teacher.user'])->get();

        // Prepare JS-ready data for curriculum updates
        $curriculumUpdatesJs = $curriculumUpdates->map(function($progress) {
            return [
                'id' => $progress->id,
                'topic_title' => $progress->topic?->title ?? '—',
                'subject_name' => $progress->topic?->chapter?->subject?->name ?? '—',
                'subject_id' => $progress->topic?->chapter?->subject?->id ?? null,
            ];
        })->values();

        // Prepare JS-ready data for homework
        $homeworkAssignedJs = $homeworkAssigned->map(function($hw) {
            return [
                'id' => $hw->id,
                'title' => $hw->title,
                'subject_name' => $hw->subject?->name ?? '—',
                'subject_id' => $hw->subject_id,
                'due_date' => $hw->due_date?->format('M d') ?? '—',
            ];
        })->values();

        // Find current/active period (only for today)
        $currentPeriodId = null;
        $isToday = $date->isToday();
        if ($isToday && $timetablePeriods->count() > 0) {
            $currentTime = now()->format('H:i');
            foreach ($timetablePeriods as $period) {
                if ($currentTime >= $period['starts_at'] && $currentTime < $period['ends_at']) {
                    $currentPeriodId = $period['id'];
                    break;
                }
            }
        }

        return [
            'date' => $dateStr,
            'timetable_periods' => $timetablePeriods,
            'current_period_id' => $currentPeriodId,
            'is_today' => $isToday,
            'attendance' => [
                'total' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'leave' => $leaveCount,
                'late' => $lateCount,
                'collected' => ($presentCount + $absentCount + $leaveCount + $lateCount) > 0,
                'detailed' => $detailedAttendance,
            ],
            'class_remarks' => $classRemarks,
            'student_remarks' => $studentRemarks,
            'curriculum_updates' => $curriculumUpdates,
            'curriculum_updates_js' => $curriculumUpdatesJs,
            'homework_assigned' => $homeworkAssigned,
            'homework_assigned_js' => $homeworkAssignedJs,
            'students' => $class->enrolledStudents->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Unknown',
                    'student_identifier' => $student->student_identifier,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Quick view modal for class - curriculum & homework
     */
    public function quickView(SchoolClass $class)
    {
        $class->load(['grade', 'teacher.user', 'room']);
        $dayOfWeek = strtolower(now()->format('l')); // Use full day name
        $currentTime = now()->format('H:i');

        // Get today's periods
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        $periods = collect();
        $currentPeriod = null;

        if ($timetable) {
            $periods = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('period_number')
                ->with(['subject', 'teacher.user'])
                ->get();

            foreach ($periods as $period) {
                $startTime = Carbon::parse($period->starts_at)->format('H:i');
                $endTime = Carbon::parse($period->ends_at)->format('H:i');
                if ($currentTime >= $startTime && $currentTime < $endTime) {
                    $currentPeriod = $period;
                    break;
                }
            }
        }

        // Get curriculum progress
        $subjects = $class->grade->subjects ?? collect();
        $curriculumData = [];

        foreach ($subjects as $subject) {
            $chapters = CurriculumChapter::where('subject_id', $subject->id)
                ->where(function ($q) use ($class) {
                    $q->where('grade_id', $class->grade_id)->orWhereNull('grade_id');
                })
                ->withCount('topics')
                ->get();

            $totalTopics = $chapters->sum('topics_count');
            $completedTopics = CurriculumProgress::whereIn('topic_id', function ($q) use ($chapters) {
                $q->select('id')->from('curriculum_topics')
                    ->whereIn('chapter_id', $chapters->pluck('id'));
            })->where('class_id', $class->id)
                ->where('status', 'completed')
                ->count();

            if ($totalTopics > 0) {
                $curriculumData[] = [
                    'subject' => $subject,
                    'total' => $totalTopics,
                    'completed' => $completedTopics,
                    'percent' => round(($completedTopics / $totalTopics) * 100),
                ];
            }
        }

        // Get recent homework
        $homework = Homework::where('class_id', $class->id)
            ->with(['subject'])
            ->orderBy('due_date', 'desc')
            ->limit(5)
            ->get();

        return view('academic.partials.class-quick-view', compact(
            'class',
            'periods',
            'currentPeriod',
            'currentTime',
            'curriculumData',
            'homework'
        ));
    }

    /**
     * Get period detail with homework history
     */
    public function periodDetail(Request $request, Period $period)
    {
        $period->load(['subject', 'teacher.user', 'room', 'timetable.schoolClass']);

        $class = $period->timetable->schoolClass;

        // Get homework for this period/subject
        $homework = Homework::where('class_id', $class->id)
            ->where('subject_id', $period->subject_id)
            ->with(['submissions', 'teacher.user'])
            ->orderBy('assigned_date', 'desc')
            ->paginate(10);

        // Get curriculum progress for this subject
        $chapters = CurriculumChapter::where('subject_id', $period->subject_id)
            ->where(function ($q) use ($class) {
                $q->where('grade_id', $class->grade_id)
                    ->orWhereNull('grade_id');
            })
            ->with(['topics.progress' => function ($q) use ($class) {
                $q->where('class_id', $class->id);
            }])
            ->orderBy('order')
            ->get();

        return view('academic.period-detail', compact('period', 'class', 'homework', 'chapters'));
    }

    /**
     * API: Get ongoing classes data for real-time updates
     */
    public function getOngoingData(Request $request)
    {
        $gradeId = $request->get('grade_id');
        $dayOfWeek = strtolower(now()->format('l')); // Use full day name
        $currentTime = now()->format('H:i');

        $classesQuery = SchoolClass::with(['grade', 'teacher.user']);

        if ($gradeId) {
            $classesQuery->where('grade_id', $gradeId);
        }

        $classes = $classesQuery->get();
        $ongoingClasses = [];

        foreach ($classes as $class) {
            $timetable = Timetable::where('class_id', $class->id)
                ->where('is_active', true)
                ->first();

            if (!$timetable) continue;

            $currentPeriod = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->whereRaw("TIME(starts_at) <= ?", [$currentTime])
                ->whereRaw("TIME(ends_at) > ?", [$currentTime])
                ->with(['subject', 'teacher.user'])
                ->first();

            if ($currentPeriod) {
                $ongoingClasses[] = [
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'grade' => $class->grade->level ?? '',
                    'subject' => $currentPeriod->subject->name ?? 'Break',
                    'teacher' => $currentPeriod->teacher->user->name ?? '-',
                    'period_number' => $currentPeriod->period_number,
                    'starts_at' => Carbon::parse($currentPeriod->starts_at)->format('H:i'),
                    'ends_at' => Carbon::parse($currentPeriod->ends_at)->format('H:i'),
                    'is_break' => $currentPeriod->is_break,
                ];
            }
        }

        return response()->json([
            'ongoing_classes' => $ongoingClasses,
            'current_time' => $currentTime,
            'total_ongoing' => count($ongoingClasses),
        ]);
    }

    private function getCurrentPeriodNumber(string $currentTime): int
    {
        // Assuming 8 periods starting at 8:00 AM with 45 min each
        $startHour = 8;
        $periodMinutes = 45;
        $breakAfter = 4;

        $currentMinutes = (int) substr($currentTime, 0, 2) * 60 + (int) substr($currentTime, 3, 2);
        $startMinutes = $startHour * 60;

        if ($currentMinutes < $startMinutes) return 0;

        $elapsed = $currentMinutes - $startMinutes;
        $period = (int) floor($elapsed / $periodMinutes) + 1;

        return min($period, 8);
    }

    /**
     * Calculate overall attendance status for a student based on their attendance records
     */
    private function calculateOverallStatus($attendanceRecords): string
    {
        if ($attendanceRecords->isEmpty()) {
            return 'not_marked';
        }

        // Count different statuses
        $statusCounts = $attendanceRecords->countBy('status');
        
        // If any absent, overall is absent
        if ($statusCounts->get('absent', 0) > 0) {
            return 'absent';
        }
        
        // If any late, overall is late
        if ($statusCounts->get('late', 0) > 0) {
            return 'late';
        }
        
        // If any leave, overall is leave
        if ($statusCounts->get('leave', 0) > 0) {
            return 'leave';
        }
        
        // If all present, overall is present
        if ($statusCounts->get('present', 0) > 0) {
            return 'present';
        }
        
        return 'not_marked';
    }
}
