<?php

namespace App\Repositories;

use App\DTOs\Attendance\StudentAttendanceData;
use App\DTOs\Attendance\StudentAttendanceFilterData;
use App\Helpers\GradeHelper;
use App\Helpers\SectionHelper;
use App\Interfaces\StudentAttendanceRepositoryInterface;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Timetable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentAttendanceRepository implements StudentAttendanceRepositoryInterface
{
    public function getClassDailySummary(Carbon $date, ?string $classId = null, ?string $gradeId = null): Collection
    {
        $classes = SchoolClass::query()
            ->with('grade')
            ->leftJoin('grades', 'classes.grade_id', '=', 'grades.id')
            ->when($classId, fn($q) => $q->where('id', $classId))
            ->when($gradeId, fn($q) => $q->where('grade_id', $gradeId))
            ->orderByRaw('CASE WHEN grades.level IS NULL THEN 999 ELSE grades.level END')
            ->orderBy('classes.name')
            ->select('classes.*')
            ->get();

        if ($classes->isEmpty()) {
            return collect();
        }

        $classIds = $classes->pluck('id');
        $dayKey = strtolower($date->englishDayOfWeek);
        $shortDayKey = strtolower(substr($date->englishDayOfWeek, 0, 3));

        $timetables = Timetable::with(['periods' => function ($q) use ($dayKey, $shortDayKey) {
            $q->where(function ($query) use ($dayKey, $shortDayKey) {
                $query->where('day_of_week', $dayKey)
                    ->orWhere('day_of_week', $shortDayKey);
            })->where('is_break', false)->orderBy('period_number');
        }])->whereIn('class_id', $classIds)->latest('updated_at')->get()->keyBy('class_id');

        $studentsCounts = StudentProfile::selectRaw('class_id, count(*) as total')
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $attendance = StudentAttendance::select(
            'student_attendance.student_id',
            'student_attendance.period_id',
            'student_attendance.period_number',
            'student_attendance.status',
            'student_attendance.updated_at',
            'sp.class_id'
        )
            ->join('student_profiles as sp', 'sp.id', '=', 'student_attendance.student_id')
            ->whereDate('student_attendance.date', $date)
            ->whereIn('sp.class_id', $classIds)
            ->get();

        $attendanceByPeriodId = [];
        $attendanceByPeriodNumber = [];
        $totalsByClass = [];
        $latestByClass = [];

        foreach ($attendance as $row) {
            if ($row->period_id) {
                $attendanceByPeriodId[$row->class_id][$row->period_id][$row->status] = ($attendanceByPeriodId[$row->class_id][$row->period_id][$row->status] ?? 0) + 1;
            }

            if (!is_null($row->period_number)) {
                $attendanceByPeriodNumber[$row->class_id][$row->period_number][$row->status] = ($attendanceByPeriodNumber[$row->class_id][$row->period_number][$row->status] ?? 0) + 1;
            }

            $totalsByClass[$row->class_id][$row->status] = ($totalsByClass[$row->class_id][$row->status] ?? 0) + 1;
            $latestByClass[$row->class_id] = isset($latestByClass[$row->class_id])
                ? $row->updated_at->max($latestByClass[$row->class_id])
                : $row->updated_at;
        }

        return $classes->map(function (SchoolClass $class) use ($studentsCounts, $timetables, $attendanceByPeriodId, $attendanceByPeriodNumber, $latestByClass, $totalsByClass) {
            $totalStudents = (int) ($studentsCounts[$class->id] ?? 0);
            $periods = $timetables[$class->id]->periods ?? collect();
            $classTotals = $totalsByClass[$class->id] ?? [];

            // If there are periods in timetable, show period-based summary
            if ($periods->isNotEmpty()) {
                $periodSummaries = $periods->map(function (Period $period) use ($attendanceByPeriodId, $attendanceByPeriodNumber, $class, $totalStudents) {
                    $countsByPeriodId = $attendanceByPeriodId[$class->id][$period->id] ?? [];
                    $countsByPeriodNumber = $attendanceByPeriodNumber[$class->id][$period->period_number] ?? [];
                    $counts = !empty($countsByPeriodId) ? $countsByPeriodId : $countsByPeriodNumber;
                    $present = $counts['present'] ?? 0;
                    $totalMarked = array_sum($counts);
                    $presentPct = $totalStudents > 0 ? round(($present / max($totalStudents, 1)) * 100) : 0;

                    return [
                        'period_id' => $period->id,
                        'period_number' => $period->period_number,
                        'present_pct' => $presentPct,
                        'totals' => [
                            'present' => $present,
                            'absent' => $counts['absent'] ?? 0,
                            'late' => $counts['late'] ?? 0,
                            'excused' => $counts['excused'] ?? 0,
                            'leave' => $counts['leave'] ?? 0,
                            'marked' => $totalMarked,
                        ],
                    ];
                })->values();

                $totalPresent = collect($periodSummaries)->sum(fn($p) => $p['totals']['present']);
                $totalMarked = collect($periodSummaries)->sum(fn($p) => $p['totals']['marked']);
                $totalPossible = $periodSummaries->count() * max($totalStudents, 1);
                $overallPct = $totalPossible ? round(($totalPresent / $totalPossible) * 100) : 0;
            } else {
                // No periods in timetable - use attendance without period or class-level totals
                $periodSummaries = collect();
                $present = $classTotals['present'] ?? 0;
                $totalMarked = array_sum($classTotals);
                $overallPct = $totalStudents > 0 ? round(($present / $totalStudents) * 100) : 0;
            }

            return [
                'class_id' => $class->id,
                'label' => SectionHelper::formatFullClassName($class->name, $class->grade?->level),
                'total_students' => $totalStudents,
                'periods' => $periodSummaries,
                'overall_pct' => $overallPct,
                'latest_change' => $latestByClass[$class->id] ?? null,
                'totals' => [
                    'present' => $classTotals['present'] ?? 0,
                    'absent' => $classTotals['absent'] ?? 0,
                    'late' => $classTotals['late'] ?? 0,
                    'excused' => $classTotals['excused'] ?? 0,
                    'leave' => $classTotals['leave'] ?? 0,
                    'marked' => array_sum($classTotals),
                ],
            ];
        });
    }

    public function getStudentsWithMonthlyStat(StudentAttendanceFilterData $filter): Collection
    {
        $month = $filter->month ? Carbon::parse($filter->month) : now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $students = StudentProfile::with(['grade', 'classModel', 'user'])
            ->when($filter->class_id, fn($q) => $q->where('class_id', $filter->class_id))
            ->when($filter->grade_id, fn($q) => $q->where('grade_id', $filter->grade_id))
            ->when($filter->search, function ($q) use ($filter) {
                $q->where(function ($query) use ($filter) {
                    $query->where('student_identifier', 'like', '%' . $filter->search . '%')
                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', '%' . $filter->search . '%'));
                });
            })
            ->orderBy('student_identifier')
            ->get();

        $attendance = StudentAttendance::selectRaw('student_id, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count, COUNT(*) as total_count')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return $students->map(function (StudentProfile $student) use ($attendance) {
            $stat = $attendance[$student->id] ?? null;
            $total = $stat?->total_count ?? 0;
            $present = $stat?->present_count ?? 0;
            $percent = $total > 0 ? round(($present / $total) * 100, 1) : null;

            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'identifier' => $student->student_identifier,
                'name' => $student->user?->name ?? '—',
                'grade' => $student->grade?->level ? GradeHelper::getLocalizedName($student->grade->level) : '—',
                'class' => $student->classModel ? SectionHelper::formatFullClassName($student->classModel->name, $student->grade?->level) : '—',
                'attendance_percent' => $percent,
            ];
        });
    }

    public function getRegisterData(string $classId, Carbon $date): array
    {
        $class = SchoolClass::with('grade')->findOrFail($classId);
        $dayKey = strtolower($date->englishDayOfWeek);
        $shortDayKey = strtolower(substr($date->englishDayOfWeek, 0, 3));

        $timetable = Timetable::with(['periods' => function ($q) use ($dayKey, $shortDayKey) {
            $q->where(function ($query) use ($dayKey, $shortDayKey) {
                $query->where('day_of_week', $dayKey)
                    ->orWhere('day_of_week', $shortDayKey);
            })->orderBy('period_number');
        }, 'periods.subject', 'periods.teacher', 'periods.room'])->where('class_id', $classId)->latest('updated_at')->first();

        $periods = $timetable?->periods ?? collect();

        $students = StudentProfile::with('user')
            ->where('class_id', $classId)
            ->orderBy('student_identifier')
            ->get();

        $attendance = StudentAttendance::whereDate('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->when($periods->isNotEmpty(), fn($q) => $q->whereIn('period_id', $periods->pluck('id')))
            ->get()
            ->groupBy(fn($row) => $row->student_id . '|' . $row->period_id);

        $attendanceMap = [];
        foreach ($attendance as $key => $rows) {
            $first = $rows->first();
            $attendanceMap[$key] = [
                'status' => $first->status,
                'remark' => $first->remark,
                'period_id' => $first->period_id,
                'student_id' => $first->student_id,
            ];
        }

        return [
            'class' => [
                'id' => $class->id,
                'label' => SectionHelper::formatFullClassName($class->name, $class->grade?->level),
            ],
            'date' => $date->toDateString(),
            'periods' => $periods->map(fn($p) => [
                'id' => $p->id,
                'period_number' => $p->period_number,
                'subject_id' => $p->subject_id,
                'subject_name' => $p->subject?->name,
                'teacher_name' => $p->teacher?->user?->name,
                'room_name' => $p->room?->name,
                'starts_at' => $p->starts_at,
                'ends_at' => $p->ends_at,
                'is_break' => (bool) $p->is_break,
            ])->values(),
            'students' => $students->map(fn($s) => [
                'id' => $s->id,
                'student_id' => $s->student_id,
                'identifier' => $s->student_identifier,
                'name' => $s->user?->name ?? '—',
            ])->values(),
            'attendance' => $attendanceMap,
        ];
    }

    public function getClassDetailData(string $classId, Carbon $date): array
    {
        $class = SchoolClass::with('grade')->findOrFail($classId);
        $dayKey = strtolower($date->englishDayOfWeek);
        $shortDayKey = strtolower(substr($date->englishDayOfWeek, 0, 3));

        $timetable = Timetable::with(['periods' => function ($q) use ($dayKey, $shortDayKey) {
            $q->where(function ($query) use ($dayKey, $shortDayKey) {
                $query->where('day_of_week', $dayKey)
                    ->orWhere('day_of_week', $shortDayKey);
            })->where('is_break', false)->orderBy('period_number');
        }, 'periods.subject', 'periods.teacher', 'periods.room'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->first();

        $periods = $timetable?->periods ?? collect();

        $students = StudentProfile::with('user')
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('student_identifier')
            ->get();

        // Get all attendance records for this date and class (including those without period_id)
        $attendanceRecords = StudentAttendance::with('markedByUser')
            ->whereDate('date', $date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        // Separate attendance with and without period_id
        $attendanceWithPeriod = $attendanceRecords->whereNotNull('period_id');
        $attendanceWithoutPeriod = $attendanceRecords->whereNull('period_id');

        // Group by both period ID and period number for compatibility across collection modes
        $attendanceByPeriodId = $attendanceWithPeriod->groupBy('period_id');
        $attendanceByPeriodNumber = $attendanceRecords
            ->whereNotNull('period_number')
            ->groupBy('period_number');

        // Calculate today's percentage for each student
        $studentTodayPct = [];
        foreach ($students as $student) {
            $studentRecords = $attendanceRecords->where('student_id', $student->id);
            $totalRecords = $studentRecords->count();
            $presentRecords = $studentRecords->where('status', 'present')->count();
            $studentTodayPct[$student->id] = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 0;
        }

        // If there are periods in timetable, show period-based view
        if ($periods->isNotEmpty()) {
            return [
                'periods' => $periods->map(function ($period) use ($attendanceByPeriodId, $attendanceByPeriodNumber, $students, $studentTodayPct) {
                    $periodAttendance = $attendanceByPeriodId->get($period->id, collect());

                    if ($periodAttendance->isEmpty()) {
                        $periodAttendance = $attendanceByPeriodNumber->get($period->period_number, collect());
                    }

                    $presentCount = $periodAttendance->where('status', 'present')->count();
                    $absentCount = $periodAttendance->where('status', 'absent')->count();
                    $leaveCount = $periodAttendance->whereIn('status', ['excused', 'leave'])->count();
                    $totalCount = $students->count();
                    $presentPct = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;

                    // Get collect time from first record
                    $firstRecord = $periodAttendance->first();
                    $collectTime = $firstRecord?->collect_time;
                    $collectedByName = $firstRecord?->markedByUser?->name;

                    // Format time properly using global setting
                    $startsAt = format_time($period->starts_at);
                    $endsAt = format_time($period->ends_at);

                    return [
                        'id' => $period->id,
                        'period_number' => $period->period_number,
                        'subject_name' => $period->subject?->name,
                        'teacher_name' => $period->teacher?->user?->name,
                        'room_name' => $period->room?->name,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'present_pct' => $presentPct,
                        'present_count' => $presentCount,
                        'absent_count' => $absentCount,
                        'leave_count' => $leaveCount,
                        'collect_time' => $collectTime ? \Carbon\Carbon::parse($collectTime)->format('H:i') : null,
                        'collected_by_name' => $collectedByName,
                        'students' => $students->map(function ($student) use ($periodAttendance, $studentTodayPct) {
                            $record = $periodAttendance->firstWhere('student_id', $student->id);
                            return [
                                'id' => $student->id,
                                'student_id' => $student->student_id,
                                'identifier' => $student->student_identifier,
                                'name' => $student->user?->name ?? '—',
                                'status' => $record?->status ?? '—',
                                'remark' => $record?->remark,
                                'today_pct' => $studentTodayPct[$student->id] ?? 0,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        }

        // No periods in timetable - show attendance without period as a single "Daily Attendance" entry
        $presentCount = $attendanceWithoutPeriod->where('status', 'present')->count();
        $absentCount = $attendanceWithoutPeriod->where('status', 'absent')->count();
        $leaveCount = $attendanceWithoutPeriod->whereIn('status', ['excused', 'leave'])->count();
        $totalCount = $students->count();
        $presentPct = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;

        $firstRecord = $attendanceWithoutPeriod->first();
        $collectTime = $firstRecord?->collect_time;
        $collectedByName = $firstRecord?->markedByUser?->name;

        return [
            'periods' => [
                [
                    'id' => 'daily',
                    'period_number' => 1,
                    'subject_name' => 'Daily Attendance',
                    'teacher_name' => null,
                    'room_name' => null,
                    'starts_at' => format_time('08:00'),
                    'ends_at' => format_time('15:00'),
                    'present_pct' => $presentPct,
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'leave_count' => $leaveCount,
                    'collect_time' => $collectTime ? \Carbon\Carbon::parse($collectTime)->format('H:i') : null,
                    'collected_by_name' => $collectedByName,
                    'students' => $students->map(function ($student) use ($attendanceWithoutPeriod, $studentTodayPct) {
                        $record = $attendanceWithoutPeriod->firstWhere('student_id', $student->id);
                        return [
                            'id' => $student->id,
                            'student_id' => $student->student_id,
                            'identifier' => $student->student_identifier,
                            'name' => $student->user?->name ?? '—',
                            'status' => $record?->status ?? '—',
                            'remark' => $record?->remark,
                            'today_pct' => $studentTodayPct[$student->id] ?? 0,
                        ];
                    })->values(),
                ],
            ],
        ];
    }

    public function getStudentDetailData(string $studentId, Carbon $startDate, Carbon $endDate): array
    {
        $student = StudentProfile::with(['user', 'grade', 'classModel'])->findOrFail($studentId);

        $attendance = StudentAttendance::with(['period.subject', 'period.teacher.user', 'period.room'])
            ->where('student_id', $studentId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();

        $summaryCounts = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'total' => 0,
        ];

        $groupedByDate = $attendance->groupBy(fn($row) => $row->date);

        $daily = $groupedByDate->map(function ($items, $date) {
            $dayLabel = Carbon::parse($date)->format('D');
            $periods = $items->sortBy(fn($r) => $r->period?->period_number ?? 0)->map(function ($row) {
                return [
                    'period_number' => $row->period?->period_number,
                    'subject_name' => $row->period?->subject?->name,
                    'teacher_name' => $row->period?->teacher?->user?->name,
                    'room_name' => $row->period?->room?->name,
                    'status' => $row->status,
                    'remark' => $row->remark,
                    'starts_at' => $row->period?->starts_at,
                    'ends_at' => $row->period?->ends_at,
                ];
            })->values();

            return [
                'date' => $date,
                'day' => $dayLabel,
                'periods' => $periods,
            ];
        })->values();

        foreach ($attendance as $row) {
            $summaryCounts[$row->status] = ($summaryCounts[$row->status] ?? 0) + 1;
            $summaryCounts['total']++;
        }

        $attendancePct = $summaryCounts['total'] > 0
            ? round(($summaryCounts['present'] / $summaryCounts['total']) * 100, 1)
            : 0;

        return [
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'identifier' => $student->student_identifier,
                'name' => $student->user?->name ?? '—',
                'grade' => $student->grade?->level ? GradeHelper::getLocalizedName($student->grade->level) : '—',
                'class' => $student->classModel ? SectionHelper::formatFullClassName($student->classModel->name, $student->grade?->level) : '—',
            ],
            'summary' => [
                'attendance_pct' => $attendancePct,
                'present' => $summaryCounts['present'],
                'absent' => $summaryCounts['absent'],
                'late' => $summaryCounts['late'],
                'excused' => $summaryCounts['excused'],
                'total' => $summaryCounts['total'],
                'total_days' => $groupedByDate->count(),
            ],
            'daily' => $daily,
            'range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }

    public function saveRegister(Carbon $date, string $classId, array $rows, ?string $markedBy): void
    {
        $collectTime = now()->format('H:i:s');
        $dateString = $date->toDateString();
        
        \DB::transaction(function () use ($rows, $dateString, $markedBy, $collectTime) {
            foreach ($rows as $row) {
                $data = StudentAttendanceData::from(array_merge($row, [
                    'date' => $dateString,
                    'marked_by' => $markedBy,
                    'collect_time' => $collectTime,
                ]));

                // Try to find existing record using whereDate for proper date comparison
                $attendance = StudentAttendance::where('student_id', $data->student_id)
                    ->whereDate('date', $dateString)
                    ->where('period_id', $data->period_id)
                    ->first();

                if ($attendance) {
                    // Update existing
                    $attendance->update([
                        'status' => $data->status,
                        'remark' => $data->remark,
                        'marked_by' => $data->marked_by,
                        'collect_time' => $collectTime,
                    ]);
                } else {
                    // Create new - let Laravel generate the UUID
                    $attendance = new StudentAttendance();
                    $attendance->student_id = $data->student_id;
                    $attendance->date = $dateString;
                    $attendance->period_id = $data->period_id;
                    $attendance->status = $data->status;
                    $attendance->remark = $data->remark;
                    $attendance->marked_by = $data->marked_by;
                    $attendance->collect_time = $collectTime;
                    $attendance->save();
                }
            }
        });
    }
}
