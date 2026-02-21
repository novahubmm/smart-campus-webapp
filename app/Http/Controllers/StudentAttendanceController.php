<?php

namespace App\Http\Controllers;

use App\DTOs\Attendance\StudentAttendanceFilterData;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Timetable;
use App\Services\StudentAttendanceService;
use App\Traits\LogsActivity;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class StudentAttendanceController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly StudentAttendanceService $service) {}

    public function index(): View
    {
        $classes = SchoolClass::with('grade')
            ->join('grades', 'classes.grade_id', '=', 'grades.id')
            ->orderBy('grades.level')
            ->orderBy('classes.name')
            ->select('classes.*')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'label' => \App\Helpers\SectionHelper::formatFullClassName($c->name, $c->grade?->level),
                'grade_id' => $c->grade_id,
                'grade_label' => $c->grade?->level !== null ? \App\Helpers\GradeHelper::getLocalizedName($c->grade->level) : null,
            ]);

        $grades = $classes
            ->filter(fn($c) => $c['grade_id'])
            ->map(fn($c) => ['id' => $c['grade_id'], 'label' => $c['grade_label']])
            ->unique('id')
            ->values();

        return view('attendance.student.index', [
            'classes' => $classes,
            'grades' => $grades,
            'today' => now()->toDateString(),
            'initialTab' => request('tab', 'class'),
        ]);
    }

    public function classDetail(SchoolClass $class, Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $dateObj = Carbon::parse($date);
        $dayKey = strtolower($dateObj->englishDayOfWeek); // Full day format: monday, tuesday, etc.

        // Get timetable periods for this class and day
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        $timetablePeriods = [];
        if ($timetable) {
            $allPeriods = Period::with(['subject'])
                ->where('timetable_id', $timetable->id)
                ->where('is_break', false)
                ->where('day_of_week', $dayKey) // Only check full day format
                ->orderBy('period_number')
                ->get();
            
            // Get period IDs that have attendance collected for this date
            $periodsWithAttendance = StudentAttendance::whereIn('period_id', $allPeriods->pluck('id'))
                ->whereDate('date', $date)
                ->groupBy('period_id')
                ->pluck('period_id')
                ->toArray();
            
            $timetablePeriods = $allPeriods->map(function ($p) use ($periodsWithAttendance) {
                return [
                    'id' => $p->id,
                    'period_number' => $p->period_number,
                    'subject_name' => $p->subject?->name,
                    'starts_at' => format_time($p->starts_at),
                    'ends_at' => format_time($p->ends_at),
                    'has_attendance' => in_array($p->id, $periodsWithAttendance),
                ];
            })->values()->toArray();
        }

        if ($request->expectsJson()) {
            $data = $this->service->classDetailData($class->id, $date);
            $data['timetable_periods'] = $timetablePeriods;
            return response()->json($data);
        }

        return view('attendance.student.class-detail', [
            'class' => $class->load('grade'),
            'date' => $date,
            'timetablePeriods' => $timetablePeriods,
            'hasTimetable' => $timetable !== null,
        ]);
    }

    public function studentDetail(StudentProfile $student, Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date'],
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
        ]);

        $month = $validated['month'] ?? now()->toDateString();
        $start = $validated['start'] ?? Carbon::parse($month)->startOfMonth()->toDateString();
        $end = $validated['end'] ?? Carbon::parse($month)->endOfMonth()->toDateString();

        if ($request->expectsJson()) {
            $data = $this->service->studentDetailData($student->id, $start, $end);
            return response()->json($data);
        }

        return view('attendance.student.student-detail', [
            'student' => $student->load(['user', 'grade', 'classModel']),
            'start' => $start,
            'end' => $end,
            'month' => Carbon::parse($month)->format('Y-m'),
        ]);
    }

    public function classSummary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'class_id' => ['nullable', 'uuid'],
            'grade_id' => ['nullable', 'uuid'],
        ]);

        $data = $this->service->classSummary($validated['date'], $validated['class_id'] ?? null, $validated['grade_id'] ?? null);

        return response()->json(['data' => $data]);
    }

    public function students(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['nullable', 'uuid'],
            'grade_id' => ['nullable', 'uuid'],
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['present', 'absent', 'leave'])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date'],
        ]);

        $filter = StudentAttendanceFilterData::from($validated);

        $data = $this->service->students($filter);

        return response()->json(['data' => $data]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'uuid'],
            'date' => ['required', 'date'],
        ]);

        $data = $this->service->registerData($validated['class_id'], $validated['date']);

        return response()->json($data);
    }

    public function storeRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'uuid'],
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'uuid'],
            'records.*.period_id' => ['nullable', 'uuid'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'leave'])],
            'records.*.remark' => ['nullable', 'string'],
        ]);

        $records = collect($validated['records'])->map(fn($row) => [
            'student_id' => $row['student_id'],
            'period_id' => $row['period_id'] ?? null,
            'status' => $row['status'],
            'remark' => $row['remark'] ?? null,
        ])->values()->all();

        $this->service->saveRegister(
            $validated['class_id'],
            $validated['date'],
            $records,
            auth()->id()
        );

        $this->logActivity('create', 'StudentAttendance', $validated['class_id'], 'Saved attendance register for ' . $validated['date']);

        return response()->json(['success' => true]);
    }

    public function create(Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $today = now()->toDateString();
        $selectedDate = $validated['date'] ?? $today;

        $user = auth()->user();
        $isTeacher = $user->hasRole('teacher');

        // For teachers, show periods instead of classes
        $teacherPeriods = collect();
        
        if ($isTeacher) {
            $teacherProfile = $user->teacherProfile;
            
            if ($teacherProfile) {
                $selectedDateObj = Carbon::parse($selectedDate);
                $dayOfWeek = strtolower($selectedDateObj->format('l'));

                // Get periods for the selected date
                $teacherPeriods = Period::where('teacher_profile_id', $teacherProfile->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_break', false)
                    ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                    ->with(['timetable.schoolClass.grade', 'timetable.schoolClass.students', 'subject'])
                    ->orderBy('period_number')
                    ->get()
                    ->map(function ($period) use ($selectedDate) {
                        $class = $period->timetable?->schoolClass;
                        $studentIds = $class?->students?->pluck('id') ?? collect();
                        
                        // Get attendance for this period
                        $attendance = StudentAttendance::whereDate('date', $selectedDate)
                            ->where('period_number', $period->period_number)
                            ->whereIn('student_id', $studentIds)
                            ->get();
                        
                        $hasAttendance = $attendance->isNotEmpty();
                        
                        return [
                            'period_id' => $period->id,
                            'period_number' => $period->period_number,
                            'class_id' => $class?->id,
                            'class_name' => $class?->name,
                            'grade_level' => $class?->grade?->level,
                            'subject' => $period->subject?->name ?? '—',
                            'start_time' => format_time($period->starts_at),
                            'end_time' => format_time($period->ends_at),
                            'students_count' => $studentIds->count(),
                            'has_attendance' => $hasAttendance,
                            'present' => $hasAttendance ? $attendance->where('status', 'present')->count() : 0,
                            'absent' => $hasAttendance ? $attendance->where('status', 'absent')->count() : 0,
                            'leave' => $hasAttendance ? $attendance->where('status', 'leave')->count() : 0,
                        ];
                    });

                // Get class IDs for stats
                $classIds = $teacherPeriods->pluck('class_id')->unique()->filter();
                $classes = SchoolClass::with(['grade', 'students'])
                    ->whereIn('id', $classIds)
                    ->orderBy('name')
                    ->get();
            } else {
                $classes = collect();
            }
        } else {
            // For admin/staff, show all classes
            $classes = SchoolClass::with(['grade', 'students'])->orderBy('name')->get();
        }
        
        // Group classes by grade
        $groupedByGrade = $classes->groupBy(fn($c) => $c->grade?->level ?? 0)->sortKeys();
        
        // Get attendance stats for selected date - only Period 1 data
        $dateAttendance = StudentAttendance::whereDate('date', $selectedDate)
            ->where('period_number', 1)
            ->get();
        
        // If teacher, filter stats to only their classes
        if ($isTeacher && $classes->isNotEmpty()) {
            $classStudentIds = StudentProfile::whereIn('class_id', $classes->pluck('id'))->pluck('id');
            $dateAttendance = $dateAttendance->whereIn('student_id', $classStudentIds);
            $totalStudents = $classStudentIds->count();
        } else {
            $totalStudents = $isTeacher ? 0 : StudentProfile::count();
        }
        
        $stats = [
            'present' => $dateAttendance->where('status', 'present')->count(),
            'absent' => $dateAttendance->where('status', 'absent')->count(),
            'leave' => $dateAttendance->where('status', 'leave')->count(),
            'total' => $totalStudents,
        ];

        // Get per-class attendance stats for selected date - only Period 1
        $classAttendanceQuery = StudentAttendance::whereDate('date', $selectedDate)
            ->where('period_number', 1)
            ->join('student_profiles', 'student_attendance.student_id', '=', 'student_profiles.id')
            ->selectRaw('student_profiles.class_id, student_attendance.status, COUNT(*) as count')
            ->groupBy('student_profiles.class_id', 'student_attendance.status');

        // Filter to teacher's classes if applicable
        if ($isTeacher && $classes->isNotEmpty()) {
            $classAttendanceQuery->whereIn('student_profiles.class_id', $classes->pluck('id'));
        }

        $classAttendance = $classAttendanceQuery->get();

        // Build per-class stats
        $classAttendanceCounts = [];
        foreach ($classAttendance as $record) {
            $classId = $record->class_id;
            if (!isset($classAttendanceCounts[$classId])) {
                $classAttendanceCounts[$classId] = ['present' => 0, 'absent' => 0, 'leave' => 0];
            }
            $classAttendanceCounts[$classId][$record->status] = $record->count;
        }

        return view('attendance.student.create', [
            'classes' => $classes,
            'groupedByGrade' => $groupedByGrade,
            'stats' => $stats,
            'today' => $today,
            'selectedDate' => $selectedDate,
            'classAttendanceCounts' => $classAttendanceCounts,
            'isTeacher' => $isTeacher,
            'teacherPeriods' => $teacherPeriods,
        ]);
    }

    public function collectClass(SchoolClass $class, Request $request): View
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $today = now()->toDateString();
        $selectedDate = $validated['date'] ?? $today;

        $class->load(['grade', 'students.user']);
        
        // Get active timetable for this class
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();
        
        $periods = [];
        if ($timetable) {
            // Get unique periods (not breaks) from the timetable
            $periods = Period::where('timetable_id', $timetable->id)
                ->where('is_break', false)
                ->select('period_number', 'starts_at', 'ends_at')
                ->distinct()
                ->orderBy('period_number')
                ->get()
                ->map(function ($p) {
                    return [
                        'number' => $p->period_number,
                        'label' => 'Period ' . $p->period_number,
                        'time' => format_time($p->starts_at) . ' - ' . format_time($p->ends_at),
                    ];
                });
        }
        
        // Get which periods already have attendance for selected date
        $studentIds = StudentProfile::where('class_id', $class->id)->pluck('id');
        $periodsWithAttendance = [];
        
        if ($studentIds->isNotEmpty()) {
            $periodsWithAttendance = StudentAttendance::whereDate('date', $selectedDate)
                ->whereIn('student_id', $studentIds)
                ->distinct()
                ->pluck('period_number')
                ->filter()
                ->values()
                ->toArray();
        }
        
        return view('attendance.student.collect-class', [
            'class' => $class,
            'periods' => $periods,
            'hasTimetable' => $timetable !== null,
            'today' => $today,
            'selectedDate' => $selectedDate,
            'periodsWithAttendance' => $periodsWithAttendance,
        ]);
    }

    public function collectClassStudents(SchoolClass $class, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'period_number' => ['required', 'integer', 'min:1'],
        ]);

        $date = $validated['date'];
        $periodNumber = $validated['period_number'];

        // Get students for this class
        $students = StudentProfile::with('user')
            ->where('class_id', $class->id)
            ->orderBy('student_identifier')
            ->get();

        // Get existing attendance for this date and period
        $existingAttendance = StudentAttendance::whereDate('date', $date)
            ->where('period_number', $periodNumber)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // Map students with their attendance status
        $studentsData = $students->map(function ($student) use ($existingAttendance) {
            $attendance = $existingAttendance->get($student->id);
            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'identifier' => $student->student_identifier,
                'name' => $student->user?->name ?? '—',
                'status' => $attendance?->status ?? null,
                'remark' => $attendance?->remark ?? null,
            ];
        })->values();

        return response()->json([
            'students' => $studentsData,
            'has_attendance' => $existingAttendance->isNotEmpty(),
        ]);
    }

    public function collectClassPeriodStatus(SchoolClass $class, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = $validated['date'];
        $studentIds = StudentProfile::where('class_id', $class->id)->pluck('id');
        
        $periodsWithAttendance = [];
        if ($studentIds->isNotEmpty()) {
            $periodsWithAttendance = StudentAttendance::whereDate('date', $date)
                ->whereIn('student_id', $studentIds)
                ->distinct()
                ->pluck('period_number')
                ->filter()
                ->values()
                ->toArray();
        }

        return response()->json([
            'periods_with_attendance' => $periodsWithAttendance,
        ]);
    }

    public function storeClassAttendance(SchoolClass $class, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'period_number' => ['required', 'integer', 'min:1'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'uuid'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'leave'])],
            'records.*.remark' => ['nullable', 'string'],
        ]);

        $date = $validated['date'];
        $periodNumber = $validated['period_number'];
        $collectTime = now()->format('H:i:s');

        // Find the period_id based on class, date, and period_number
        $dateObj = \Carbon\Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l')); // Full day name (monday, tuesday, etc.)
        
        $timetable = Timetable::where('class_id', $class->id)
            ->where('is_active', true)
            ->first();
        
        $periodId = null;
        if ($timetable) {
            $period = Period::where('timetable_id', $timetable->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('period_number', $periodNumber)
                ->first();
            
            if ($period) {
                $periodId = $period->id;
            }
        }

        // Prepare data for bulk upsert
        $upsertData = [];
        foreach ($validated['records'] as $record) {
            $upsertData[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'student_id' => $record['student_id'],
                'date' => $date,
                'period_number' => $periodNumber,
                'period_id' => $periodId, // Now properly set
                'status' => $record['status'],
                'remark' => $record['remark'] ?? null,
                'marked_by' => auth()->id(),
                'collect_time' => $collectTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Single query instead of N queries
        StudentAttendance::upsert(
            $upsertData,
            ['student_id', 'date', 'period_number'],
            ['period_id', 'status', 'remark', 'marked_by', 'collect_time', 'updated_at']
        );

        $this->logActivity('create', 'StudentAttendance', $class->id, "Saved class attendance for period {$periodNumber} on {$date}");

        return response()->json(['success' => true]);
    }

    public function schedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'uuid'],
        ]);

        $today = now();
        $todayDate = $today->toDateString();
        $dayOfWeek = strtolower($today->format('l')); // e.g., 'tuesday' - keep full format

        $timetable = Timetable::where('class_id', $validated['class_id'])
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
            })
            ->first();

        if (!$timetable) {
            return response()->json(['data' => []]);
        }

        // Filter periods for today only (using full day format)
        $periodsList = Period::with(['subject', 'teacher.user', 'room'])
            ->where('timetable_id', $timetable->id)
            ->where('is_break', false)
            ->where('day_of_week', $dayOfWeek) // Only check full day format
            ->orderBy('starts_at')
            ->get();

        // Check which periods have attendance collected today
        $collectedPeriodIds = StudentAttendance::whereIn('period_id', $periodsList->pluck('id'))
            ->whereDate('date', $todayDate)
            ->distinct('period_id')
            ->pluck('period_id')
            ->toArray();

        $periods = $periodsList->map(fn($p) => [
            'id' => $p->id,
            'day' => ucfirst($dayOfWeek), // Use full day format
            'time' => format_time($p->starts_at) . ' - ' . format_time($p->ends_at),
            'subject' => $p->subject?->name ?? '—',
            'teacher' => $p->teacher?->user?->name ?? '—',
            'room' => $p->room?->name ?? '—',
            'collected' => in_array($p->id, $collectedPeriodIds),
        ]);

        return response()->json(['data' => $periods]);
    }

    public function collect(Period $period, Request $request)
    {
        $period->load(['timetable.schoolClass.grade', 'subject', 'teacher.user', 'room']);

        $classId = $period->timetable?->class_id;
        $date = now()->toDateString();

        if ($request->expectsJson()) {
            // Get students for this class
            $students = StudentProfile::with('user')
                ->where('class_id', $classId)
                ->orderBy('student_identifier')
                ->get();

            // Get existing attendance for this period and date
            $existingAttendance = StudentAttendance::where('period_id', $period->id)
                ->whereDate('date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');

            // Map students with their attendance status
            $studentsData = $students->map(function ($student) use ($existingAttendance) {
                $attendance = $existingAttendance->get($student->id);
                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'identifier' => $student->student_identifier,
                    'name' => $student->user?->name ?? '—',
                    'status' => $attendance?->status ?? null,
                    'remark' => $attendance?->remark ?? null,
                ];
            })->values();

            return response()->json([
                'period' => [
                    'id' => $period->id,
                    'class' => $period->timetable?->schoolClass ? \App\Helpers\SectionHelper::formatFullClassName($period->timetable->schoolClass->name, $period->timetable->schoolClass->grade?->level) : '',
                    'subject' => $period->subject?->name ?? '—',
                    'time' => format_time($period->starts_at) . ' - ' . format_time($period->ends_at),
                    'room' => $period->room?->name ?? '—',
                    'teacher' => $period->teacher?->user?->name ?? '—',
                    'date' => $date,
                ],
                'students' => $studentsData,
            ]);
        }

        return view('attendance.student.collect', [
            'period' => $period,
            'date' => $date,
        ]);
    }

    public function storeCollect(Period $period, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'uuid'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'leave'])],
            'records.*.remark' => ['nullable', 'string'],
        ]);

        $classId = $period->timetable?->class_id;
        $date = now()->toDateString();

        $records = collect($validated['records'])->map(fn($row) => [
            'student_id' => $row['student_id'],
            'period_id' => $period->id,
            'status' => $row['status'],
            'remark' => $row['remark'] ?? null,
        ])->values()->all();

        $this->service->saveRegister($classId, $date, $records, auth()->id());

        $this->logActivity('create', 'StudentAttendance', $period->id, "Collected period attendance for {$date}");

        return response()->json(['success' => true]);
    }
}
