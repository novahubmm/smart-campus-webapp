<?php

namespace App\Http\Controllers;

use App\DTOs\Timetable\TimetableFilterData;
use App\Http\Requests\Timetable\PublishTimetableRequest;
use App\Http\Requests\Timetable\StoreTimetableRequest;
use App\Http\Requests\Timetable\UpdateTimetableRequest;
use App\Models\Timetable;
use App\Services\TimetableService;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Notifications\PeriodSwitchRequested;
use App\Notifications\PeriodSwitchResponded;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly TimetableService $service) {}

    public function index(Request $request): View
    {
        $filter = TimetableFilterData::from($request->all());
        $timetables = $this->service->list($filter);

        $classes = SchoolClass::with(['grade', 'batch'])
            ->join('grades', 'classes.grade_id', '=', 'grades.id')
            ->orderBy('grades.level')
            ->orderBy('classes.name')
            ->select('classes.*')
            ->get();
        $teachers = TeacherProfile::with('user:id,name')->get();

        // Get timetable counts per class
        $timetableCounts = Timetable::select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $activeTimetables = Timetable::where('is_active', true)->get()->keyBy('class_id');

        $totals = [
            'all' => Timetable::count(),
            'active' => Timetable::where('is_active', true)->count(),
        ];

        $filters = [
            'batch_id' => $request->input('batch_id', ''),
            'grade_id' => $request->input('grade_id', ''),
            'class_id' => $request->input('class_id', ''),
            'teacher_profile_id' => $request->input('teacher_profile_id', ''),
        ];

        // Get global timetable settings
        $setting = Setting::first();
        $timetableSettings = [
            'time_format' => $setting?->timetable_time_format ?? '24h',
        ];

        return view('time-table.index', [
            'timetables' => $timetables,
            'classes' => $classes,
            'teachers' => $teachers,
            'filters' => $filters,
            'totals' => $totals,
            'timetableCounts' => $timetableCounts,
            'activeTimetables' => $activeTimetables,
            'timetableSettings' => $timetableSettings,
        ]);
    }

    /**
     * Show class timetable versions management
     */
    public function classVersions(SchoolClass $class): View
    {
        $class->load(['grade.subjects.teachers.user', 'batch', 'teacher.user']);
        
        $timetables = Timetable::where('class_id', $class->id)
            ->with(['creator', 'periods.subject', 'periods.teacher.user'])
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->get();

        $activeTimetable = $timetables->firstWhere('is_active', true);

        // Get subjects with their teachers for this grade
        $subjects = $class->grade?->subjects ?? collect();

        // Get only teachers who teach this class (have periods in the active timetable)
        $classTeacherIds = $activeTimetable 
            ? $activeTimetable->periods->pluck('teacher_profile_id')->filter()->unique()->toArray()
            : [];
        
        $teachers = TeacherProfile::with(['user:id,name', 'subjects:id,name'])
            ->whereIn('id', $classTeacherIds)
            ->get();

        // Get period switch requests for this class
        $switchRequests = \App\Models\PeriodSwitchRequest::whereHas('period', function ($q) use ($class) {
                $q->whereHas('timetable', function ($q2) use ($class) {
                    $q2->where('class_id', $class->id);
                });
            })
            ->with(['period.subject', 'period.timetable', 'fromTeacher.user', 'toTeacher.user'])
            ->orderByDesc('created_at')
            ->get();

        // Get global timetable settings
        $setting = Setting::first();
        $timeFormat = $setting?->timetable_time_format ?? '24h';

        return view('time-table.class-versions', [
            'class' => $class,
            'timetables' => $timetables,
            'activeTimetable' => $activeTimetable,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'switchRequests' => $switchRequests,
            'timeFormat' => $timeFormat,
        ]);
    }

    /**
     * Set a timetable version as active
     */
    public function setActive(Request $request, Timetable $timetable): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Deactivate all other timetables for this class
        Timetable::where('class_id', $timetable->class_id)
            ->where('id', '!=', $timetable->id)
            ->update(['is_active' => false]);

        // Activate this timetable
        $timetable->update(['is_active' => true]);
        
        $timetable->load('schoolClass');
        $this->logActivity('activate', 'Timetable', $timetable->id, 'Activated Timetable for ' . ($timetable->schoolClass?->name ?? 'Class'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Timetable set as active.')]);
        }

        return back()->with('status', __('Timetable set as active.'));
    }

    /**
     * Duplicate a timetable as a new version
     */
    public function duplicate(Timetable $timetable): RedirectResponse
    {
        $timetable->load('periods');

        // Get next version number for this class
        $nextVersion = Timetable::where('class_id', $timetable->class_id)->max('version') + 1;

        // Create new timetable
        $newTimetable = $timetable->replicate();
        $newTimetable->version = $nextVersion;
        $newTimetable->version_name = "Version {$nextVersion} (Copy)";
        $newTimetable->is_active = false;
        $newTimetable->save();

        // Duplicate periods
        foreach ($timetable->periods as $period) {
            $newPeriod = $period->replicate();
            $newPeriod->timetable_id = $newTimetable->id;
            $newPeriod->save();
        }
        
        $timetable->load('schoolClass');
        $this->logCreate('Timetable', $newTimetable->id, 'Duplicated Timetable for ' . ($timetable->schoolClass?->name ?? 'Class'));

        return back()->with('status', __('Timetable duplicated as new version.'));
    }

    /**
     * Update version name
     */
    public function updateVersionName(Request $request, Timetable $timetable): RedirectResponse
    {
        $validated = $request->validate([
            'version_name' => 'required|string|max:255',
        ]);

        $timetable->update($validated);

        return back()->with('status', __('Version name updated.'));
    }

    public function create(Request $request): View
    {
        // Get class_id from query string for auto-selection
        $selectedClassId = $request->query('class_id');
        
        return view('time-table.create', array_merge($this->formContext(), [
            'selectedClassId' => $selectedClassId,
        ]));
    }

    public function edit(Timetable $timetable): View|RedirectResponse
    {
        // Prevent editing active timetables
        if ($timetable->is_active) {
            return redirect()->route('time-table.index')
                ->with('error', __('Cannot edit active timetable. Deactivate it first.'));
        }

        $timetable->loadMissing(['schoolClass', 'grade', 'periods']);

        return view('time-table.edit', array_merge($this->formContext(), [
            'timetable' => $timetable,
        ]));
    }

    private function formContext(): array
    {
        $setting = Setting::first();
        $classes = SchoolClass::with(['grade', 'batch'])
            ->join('grades', 'classes.grade_id', '=', 'grades.id')
            ->orderBy('grades.level')
            ->orderBy('classes.name')
            ->select('classes.*')
            ->get();
        $teachers = TeacherProfile::with('user:id,name')->get();
        
        // Convert full day names to short names for JavaScript compatibility
        $weekDays = $setting?->week_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        // Keep full day format - no conversion needed
        $weekDays = collect($weekDays)->map(function ($day) {
            return strtolower($day); // Ensure lowercase: monday, tuesday, etc.
        })->toArray();

        $grades = Grade::with(['batch', 'subjects.teachers.user:id,name'])->orderBy('level')->get();

        $gradeSubjects = $grades->mapWithKeys(function ($grade) {
            return [
                $grade->id => $grade->subjects->map(function ($s) {
                    $firstTeacher = $s->teachers->first();
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'code' => $s->code,
                        'teacher_name' => $firstTeacher?->user?->name ?? '',
                        'teacher_profile_id' => $firstTeacher?->id ?? '',
                    ];
                })->values(),
            ];
        });

        $classSubjects = collect();
        if (Schema::hasTable('class_subject')) {
            $classSubjects = SchoolClass::with(['subjects.teachers.user:id,name'])->get()->mapWithKeys(function ($class) {
                return [
                    $class->id => $class->subjects->map(function ($s) {
                        $firstTeacher = $s->teachers->first();
                        return [
                            'id' => $s->id,
                            'name' => $s->name,
                            'teacher_name' => $firstTeacher?->user?->name ?? '',
                            'teacher_profile_id' => $firstTeacher?->id ?? '',
                        ];
                    })->values(),
                ];
            });
        }

        // Get all timetables grouped by class for reference (to show old versions)
        $allTimetablesByClass = Timetable::with(['periods.subject', 'periods.teacher.user'])
            ->orderBy('version', 'desc')
            ->get()
            ->groupBy('class_id')
            ->map(function ($timetables) {
                return $timetables->map(function ($t) {
                    // Format time fields to H:i format (handles Carbon objects and strings)
                    $formatTime = function ($time) {
                        if (!$time) return null;
                        // If it's a Carbon/DateTime object, format it
                        if ($time instanceof \DateTimeInterface) {
                            return $time->format('H:i');
                        }
                        // If it's a string
                        $timeStr = (string) $time;
                        if (strlen($timeStr) === 5) return $timeStr; // Already H:i
                        if (strlen($timeStr) >= 5) return substr($timeStr, 0, 5); // H:i:s -> H:i
                        return $timeStr;
                    };

                    // Convert day names to short format (monday -> mon, tuesday -> tue, etc.)
                    $toShortDay = function ($day) {
                        return strtolower(substr($day, 0, 3));
                    };

                    // Convert week_days to short format
                    $weekDays = $t->week_days;
                    if (is_array($weekDays)) {
                        $weekDays = array_map($toShortDay, $weekDays);
                    }

                    return [
                        'id' => $t->id,
                        'version' => $t->version,
                        'version_name' => $t->version_name,
                        'status' => $t->status,
                        'is_active' => $t->is_active,
                        'class_id' => $t->class_id,
                        'week_days' => $weekDays,
                        'school_start_time' => $formatTime($t->school_start_time),
                        'school_end_time' => $formatTime($t->school_end_time),
                        'minutes_per_period' => $t->minutes_per_period,
                        'break_duration' => $t->break_duration,
                        'periods' => $t->periods->map(function ($p) use ($formatTime, $toShortDay) {
                            $arr = $p->toArray();
                            $arr['starts_at'] = $formatTime($p->starts_at);
                            $arr['ends_at'] = $formatTime($p->ends_at);
                            $arr['day_of_week'] = $toShortDay($p->day_of_week);
                            $arr['subject_name'] = $p->subject?->name ?? '';
                            $arr['teacher_name'] = $p->teacher?->user?->name ?? '';
                            return $arr;
                        })->toArray(),
                    ];
                })->values();
            });

        return [
            'classes' => $classes,
            'grades' => $grades,
            'gradeSubjects' => $gradeSubjects,
            'classSubjects' => $classSubjects,
            'allTimetablesByClass' => $allTimetablesByClass,
            'teachers' => $teachers,
            'defaults' => [
                'number_of_periods_per_day' => $setting?->number_of_periods_per_day,
                'minute_per_period' => $setting?->minute_per_period,
                'break_duration' => $setting?->break_duration,
                'school_start_time' => $setting?->school_start_time ? substr($setting->school_start_time, 0, 5) : null,
                'school_end_time' => $setting?->school_end_time ? substr($setting->school_end_time, 0, 5) : null,
                'week_days' => $weekDays,
            ],
        ];
    }

    public function store(StoreTimetableRequest $request)
    {
        $data = $request->validated();

        // Support multiple timetables creation in one request
        if (isset($data['timetables']) && is_array($data['timetables'])) {
            $createdIds = [];
            foreach ($data['timetables'] as $payload) {
                $periods = $payload['periods'] ?? [];
                unset($payload['periods']);
                
                // Set version number
                $payload['version'] = Timetable::where('class_id', $payload['class_id'])->max('version') + 1 ?: 1;
                
                $timetable = $this->service->create($payload, $periods);
                $createdIds[] = $timetable->id;
                
                $class = SchoolClass::find($payload['class_id']);
                $this->logCreate('Timetable', $timetable->id, 'Timetable for ' . ($class?->name ?? 'Class'));
            }
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'ok', 
                    'message' => __('Timetables created.'),
                    'timetable_id' => $createdIds[0] ?? null,
                    'timetable_ids' => $createdIds,
                ]);
            }
        } else {
            $periods = $data['periods'] ?? [];
            unset($data['periods']);
            
            // Set version number
            $data['version'] = Timetable::where('class_id', $data['class_id'])->max('version') + 1 ?: 1;

            $timetable = $this->service->create($data, $periods);
            
            $class = SchoolClass::find($data['class_id']);
            $this->logCreate('Timetable', $timetable->id, 'Timetable for ' . ($class?->name ?? 'Class'));
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'ok', 
                    'message' => __('Timetable created.'),
                    'timetable_id' => $timetable->id,
                ]);
            }
        }

        return redirect()->route('time-table.index')->with('status', __('Timetable created.'));
    }

    public function update(UpdateTimetableRequest $request, Timetable $timetable): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Prevent editing active timetables
        if ($timetable->is_active) {
            if ($request->wantsJson()) {
                return response()->json(['error' => __('Cannot edit active timetable. Deactivate it first.')], 422);
            }
            return back()->with('error', __('Cannot edit active timetable. Deactivate it first.'));
        }
        
        $data = $request->validated();
        $periods = $data['periods'] ?? [];
        unset($data['periods']);

        $this->service->update($timetable, $data, $periods);
        
        $timetable->load('schoolClass');
        $this->logUpdate('Timetable', $timetable->id, 'Timetable for ' . ($timetable->schoolClass?->name ?? 'Class'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Timetable updated.'),
                'timetable_id' => $timetable->id,
            ]);
        }

        return redirect()->route('time-table.index')->with('status', __('Timetable updated.'));
    }

    public function activate(Request $request, Timetable $timetable): RedirectResponse
    {
        $this->service->activate($timetable);
        
        $timetable->load('schoolClass');
        $this->logActivity('activate', 'Timetable', $timetable->id, 'Activated Timetable for ' . ($timetable->schoolClass?->name ?? 'Class'));

        return back()->with('status', __('Timetable activated.'));
    }

    public function destroy(Request $request, Timetable $timetable): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Prevent deleting active timetables
        if ($timetable->is_active) {
            if ($request->wantsJson()) {
                return response()->json(['error' => __('Cannot delete active timetable. Deactivate it first.')], 422);
            }
            return back()->with('error', __('Cannot delete active timetable. Deactivate it first.'));
        }
        
        $timetable->load('schoolClass');
        $className = $timetable->schoolClass?->name ?? 'Class';
        $timetableId = $timetable->id;
        
        $timetable->delete();
        
        $this->logDelete('Timetable', $timetableId, 'Timetable for ' . $className);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'message' => __('Timetable removed.')]);
        }

        return back()->with('status', __('Timetable removed.'));
    }

    /**
     * Update a single period via AJAX
     */
    public function updatePeriod(Request $request, \App\Models\Period $period)
    {
        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_profile_id' => [
                'nullable',
                'exists:teacher_profiles,id',
                new \App\Rules\TeacherNotDoubleBooked(
                    $period->day_of_week,
                    $period->starts_at,
                    $period->ends_at,
                    $period->timetable_id,
                    $period->id
                ),
            ],
            'is_break' => 'boolean',
        ]);

        $period->update($validated);
        $period->load(['subject', 'teacher.user']);

        return response()->json([
            'success' => true,
            'period' => [
                'id' => $period->id,
                'subject_name' => $period->subject?->name ?? '-',
                'teacher_name' => $period->teacher?->user?->name ?? '-',
                'is_break' => $period->is_break,
            ],
        ]);
    }

    /**
     * Approve a period switch request
     */
    public function approveSwitchRequest(\App\Models\PeriodSwitchRequest $switchRequest): RedirectResponse
    {
        $switchRequest->load(['toTeacher.user', 'period.timetable.class']);
        
        $switchRequest->update(['status' => 'accepted']);
        
        // Send notification to the requester (to_teacher)
        $toTeacher = $switchRequest->toTeacher?->user;
        if ($toTeacher) {
            $className = $switchRequest->period?->timetable?->class?->name ?? 'Unknown Class';
            $responderName = auth()->user()->name;
            $toTeacher->notify(new PeriodSwitchResponded(
                $switchRequest,
                $responderName,
                $className,
                'accepted'
            ));
        }
        
        $this->logActivity('approve', 'PeriodSwitchRequest', $switchRequest->id, 'Approved period switch request');

        return back()->with('status', __('time_table.Switch request approved'));
    }

    /**
     * Reject a period switch request
     */
    public function rejectSwitchRequest(\App\Models\PeriodSwitchRequest $switchRequest): RedirectResponse
    {
        $switchRequest->load(['toTeacher.user', 'period.timetable.class']);
        
        $switchRequest->update(['status' => 'rejected']);
        
        // Send notification to the requester (to_teacher)
        $toTeacher = $switchRequest->toTeacher?->user;
        if ($toTeacher) {
            $className = $switchRequest->period?->timetable?->class?->name ?? 'Unknown Class';
            $responderName = auth()->user()->name;
            $toTeacher->notify(new PeriodSwitchResponded(
                $switchRequest,
                $responderName,
                $className,
                'rejected'
            ));
        }
        
        $this->logActivity('reject', 'PeriodSwitchRequest', $switchRequest->id, 'Rejected period switch request');

        return back()->with('status', __('time_table.Switch request rejected'));
    }

    /**
     * Store a new period switch request (from web portal)
     */
    public function storeSwitchRequest(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => 'required|uuid|exists:periods,id',
            'to_teacher_id' => 'required|uuid|exists:teacher_profiles,id',
            'to_subject_id' => 'required|uuid|exists:subjects,id',
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:500',
        ]);

        $period = \App\Models\Period::find($validated['period_id']);
        $subject = Subject::find($validated['to_subject_id']);

        // Prevent requesting switch for the same teacher (from and to teacher are the same)
        if ($period->teacher_profile_id === $validated['to_teacher_id']) {
            return back()->with('error', __('time_table.Cannot request switch for same teacher'));
        }

        $switchRequest = \App\Models\PeriodSwitchRequest::create([
            'period_id' => $validated['period_id'],
            'from_teacher_id' => $period->teacher_profile_id,
            'to_teacher_id' => $validated['to_teacher_id'],
            'date' => $validated['date'],
            'reason' => $validated['reason'],
            'to_subject' => $subject?->name,
            'status' => 'pending',
        ]);

        // Send notification to the original period owner (from_teacher)
        $fromTeacher = $period->teacher?->user;
        $toTeacherProfile = TeacherProfile::with('user')->find($validated['to_teacher_id']);
        if ($fromTeacher && $toTeacherProfile) {
            $fromTeacher->notify(new PeriodSwitchRequested(
                $switchRequest,
                $toTeacherProfile->user?->name ?? 'Unknown Teacher',
                $class->name,
                $subject?->name ?? 'Unknown Subject'
            ));
        }

        $this->logCreate('PeriodSwitchRequest', $switchRequest->id, 'Created period switch request for class ' . $class->name);

        return back()->with('status', __('time_table.Switch request created'));
    }

    /**
     * Update global timetable settings
     */
    public function updateGlobalSettings(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'timetable_time_format' => ['required', 'in:12h,24h'],
        ]);

        $setting = Setting::first();
        if ($setting) {
            $setting->update($validated);
        } else {
            Setting::create($validated);
        }

        $this->logActivity('update', 'Setting', $setting?->id ?? 'new', 'Updated timetable global settings');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => __('time_table.Settings updated successfully')]);
        }

        return back()->with('status', __('time_table.Settings updated successfully'));
    }
}
