<?php

namespace App\Http\Controllers;

use App\Services\TeacherAttendanceService;
use App\Models\TeacherProfile;
use App\Models\TeacherAttendance;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class TeacherAttendanceController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly TeacherAttendanceService $service) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teacher_profiles,id'],
            'date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'in:present,absent,late,excused,off,holiday'],
            'start_time' => ['nullable', 'string'],
            'end_time' => ['nullable', 'string'],
            'remark' => ['nullable', 'string'],
        ]);

        $attendance = TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $validated['teacher_id'],
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'remark' => $validated['remark'] ?? null,
                'marked_by' => auth()->id(),
            ]
        );

        $this->logActivity('create', 'TeacherAttendance', $attendance->id, "Saved teacher attendance for {$validated['date']}");

        return response()->json(['success' => true, 'data' => $attendance]);
    }

    public function index(): View
    {
        return view('attendance.teacher.index', [
            'today' => now()->toDateString(),
            'currentMonth' => now()->format('Y-m'),
            'currentYear' => now()->year,
            'initialTab' => request('tab', 'monthly'),
        ]);
    }

    public function daily(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $data = $this->service->dailyRegister($validated['date']);

        return response()->json(['data' => $data]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date'],
        ]);

        $data = $this->service->monthlySummary($validated['month']);

        return response()->json(['data' => $data]);
    }

    public function summer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
        ]);

        $data = $this->service->summerSummary((string) $validated['year']);

        return response()->json(['data' => $data]);
    }

    public function annual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
        ]);

        $data = $this->service->annualSummary((string) $validated['year']);

        return response()->json(['data' => $data]);
    }

    public function detail(TeacherProfile $teacher, Request $request)
    {
        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date'],
            'month' => ['nullable', 'date'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m-01');
        $start = $validated['start'] ?? Carbon::parse($month)->startOfMonth()->toDateString();
        $end = $validated['end'] ?? Carbon::parse($month)->endOfMonth()->toDateString();

        if ($request->expectsJson()) {
            $data = $this->service->teacherDetail($teacher->id, $start, $end);
            return response()->json($data);
        }

        return view('attendance.teacher.detail', [
            'teacher' => $teacher->load(['user', 'department']),
            'start' => $start,
            'end' => $end,
            'month' => Carbon::parse($start)->format('Y-m'),
        ]);
    }
}
