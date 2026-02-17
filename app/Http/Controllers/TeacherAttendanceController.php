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
            'status' => ['nullable', 'string', 'in:present,absent,late,excused,off,holiday,leave,half_day,half-day'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'remark' => ['nullable', 'string'],
        ]);

        $teacher = TeacherProfile::query()
            ->select(['id', 'user_id'])
            ->findOrFail($validated['teacher_id']);

        $attendance = TeacherAttendance::query()
            ->where('teacher_id', $teacher->user_id)
            ->whereDate('date', $validated['date'])
            ->first();

        if (! $attendance) {
            $attendance = new TeacherAttendance();
            $attendance->id = TeacherAttendance::generateId($validated['date']);
            $attendance->teacher_id = $teacher->user_id;
            $attendance->date = $validated['date'];
        }

        $status = $this->normalizeStatusForStorage($validated['status'] ?? $attendance->status);
        $startTime = $validated['start_time'] ?? null;
        $endTime = $validated['end_time'] ?? null;

        $checkInTime = $startTime ? Carbon::createFromFormat('H:i', $startTime)->format('H:i:s') : null;
        $checkOutTime = $endTime ? Carbon::createFromFormat('H:i', $endTime)->format('H:i:s') : null;

        $attendance->day_of_week = Carbon::parse($validated['date'])->format('l');
        $attendance->status = $status ?: 'present';
        $attendance->check_in_time = $checkInTime;
        $attendance->check_out_time = $checkOutTime;
        $attendance->check_in_timestamp = $checkInTime ? Carbon::parse($validated['date'] . ' ' . $checkInTime) : null;
        $attendance->check_out_timestamp = $checkOutTime ? Carbon::parse($validated['date'] . ' ' . $checkOutTime) : null;
        $attendance->working_hours_decimal = TeacherAttendance::calculateWorkingHours($checkInTime, $checkOutTime) ?: null;
        $attendance->remarks = $validated['remark'] ?? $attendance->remarks;
        $attendance->save();

        $this->logActivity('create', 'TeacherAttendance', $attendance->id, "Saved teacher attendance for {$validated['date']}");

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'teacher_id' => $teacher->id,
                'date' => Carbon::parse($attendance->date)->toDateString(),
                'status' => $this->normalizeStatusForUi($attendance->status),
                'start_time' => $attendance->check_in_time ? substr($attendance->check_in_time, 0, 5) : null,
                'end_time' => $attendance->check_out_time ? substr($attendance->check_out_time, 0, 5) : null,
                'remark' => $attendance->remarks,
            ],
        ]);
    }

    private function normalizeStatusForStorage(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return match ($status) {
            'excused', 'off', 'holiday' => 'leave',
            'late', 'half_day', 'half-day' => 'half_day',
            default => $status,
        };
    }

    private function normalizeStatusForUi(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        return match ($status) {
            'leave' => 'excused',
            'half_day' => 'late',
            default => $status,
        };
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
