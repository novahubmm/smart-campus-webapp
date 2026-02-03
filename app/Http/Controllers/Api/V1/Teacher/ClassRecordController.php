<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\TeacherProfile;
use App\Models\StudentAttendance;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\ClassRemark;
use App\Models\StudentRemark;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use App\Models\CurriculumProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClassRecordController extends Controller
{
    /**
     * Get class records for a specific week
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date|date_format:Y-m-d',
                'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get periods for the teacher within the date range where classes were held
            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('is_break', false)
                ->whereHas('timetable', function ($query) use ($startDate, $endDate) {
                    $query->where('is_active', true);
                })
                ->whereHas('attendances', function ($query) use ($startDate, $endDate) {
                    // Only include periods where attendance was taken (indicating class was held)
                    $query->whereBetween('date', [$startDate, $endDate]);
                })
                ->with([
                    'timetable.schoolClass.grade',
                    'subject',
                    'attendances' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('date', [$startDate, $endDate])
                              ->with('student.user');
                    }
                ])
                ->get();

            $records = [];
            $totalPresent = 0;
            $totalAbsent = 0;
            $totalLeave = 0;

            foreach ($periods as $period) {
                $timetable = $period->timetable;
                $schoolClass = $timetable->schoolClass;
                $grade = $schoolClass->grade;

                // Get attendances for this period within the date range
                $attendances = $period->attendances->whereBetween('date', [$startDate, $endDate]);
                
                // Group by date to create separate records for each day
                $attendancesByDate = $attendances->groupBy('date');
                
                foreach ($attendancesByDate as $date => $dayAttendances) {
                    $present = $dayAttendances->where('status', 'present')->count();
                    $absent = $dayAttendances->where('status', 'absent')->count();
                    $leave = $dayAttendances->whereIn('status', ['excused', 'late'])->count();
                    $total = $dayAttendances->count();

                    $totalPresent += $present;
                    $totalAbsent += $absent;
                    $totalLeave += $leave;

                    // Get grade color
                    $gradeColors = [
                        '7' => '#16A34A', // green
                        '8' => '#DC2626', // red
                        '9' => '#2563EB', // blue
                        '10' => '#7C3AED', // purple
                        '11' => '#EA580C', // orange
                        '12' => '#0891B2', // cyan
                    ];
                    $gradeColor = $gradeColors[$grade->level] ?? '#6B7280';

                    $dateCarbon = Carbon::parse($date);

                    $records[] = [
                        'id' => $period->id . '_' . $dateCarbon->format('Y-m-d'), // Unique ID for each day
                        'grade' => $schoolClass->name,
                        'class_id' => $schoolClass->id,
                        'period' => 'P' . $period->period_number,
                        'subject' => $period->subject->name,
                        'date' => $dateCarbon->format('M j, Y'),
                        'date_raw' => $dateCarbon->format('Y-m-d'),
                        'time' => format_time($period->starts_at) . ' - ' . format_time($period->ends_at),
                        'chapter' => $period->notes ?? 'No chapter specified',
                        'present' => $present,
                        'absent' => $absent,
                        'leave' => $leave,
                        'total' => $total,
                        'grade_color' => $gradeColor,
                    ];
                }
            }

            // Sort records by date (newest first)
            $records = collect($records)->sortByDesc('date_raw')->values()->all();

            $totalStudents = $totalPresent + $totalAbsent + $totalLeave;
            $averageAttendance = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'week_start' => $startDate->format('Y-m-d'),
                    'week_end' => $endDate->format('Y-m-d'),
                    'records' => $records,
                    'summary' => [
                        'total_classes' => count($records),
                        'total_present' => $totalPresent,
                        'total_absent' => $totalAbsent,
                        'total_leave' => $totalLeave,
                        'average_attendance' => $averageAttendance,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed information for a specific class record
     * 
     * @param string $recordId
     * @return JsonResponse
     */
    public function show(string $recordId): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
            }

            // Parse the record ID (format: period_id_date)
            // The record ID is in format: uuid_YYYY-MM-DD
            // We need to find the last underscore and split there
            $lastUnderscorePos = strrpos($recordId, '_');
            if ($lastUnderscorePos === false) {
                return response()->json(['success' => false, 'message' => 'Invalid record ID format'], 400);
            }
            
            $periodId = substr($recordId, 0, $lastUnderscorePos);
            $date = substr($recordId, $lastUnderscorePos + 1);

            $period = Period::where('id', $periodId)
                ->where('teacher_profile_id', $teacherProfile->id)
                ->where('is_break', false)
                ->with([
                    'timetable.schoolClass.grade',
                    'timetable.schoolClass.teacher.user',
                    'subject',
                    'attendances' => function ($query) use ($date) {
                        $query->whereDate('date', $date)->with(['student.user', 'student' => function($q) {
                            $q->select('id', 'user_id', 'photo_path', 'roll_number');
                        }]);
                    }
                ])
                ->first();

            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Class record not found'], 404);
            }

            $timetable = $period->timetable;
            $schoolClass = $timetable->schoolClass;
            $grade = $schoolClass->grade;

            // Get attendance data for the specific date
            $attendances = $period->attendances;
            $presentStudents = [];
            $absentStudents = [];
            $leaveStudents = [];

            foreach ($attendances as $attendance) {
                $student = $attendance->student;
                $studentData = [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'roll_no' => $student->roll_number,
                    'avatar' => $student->photo_path 
                        ? url('storage/' . $student->photo_path) 
                        : 'http://10.73.128.219:8088/storage/student_default_profile.jpg',
                ];

                switch ($attendance->status) {
                    case 'present':
                        $presentStudents[] = $studentData;
                        break;
                    case 'absent':
                        $absentStudents[] = $studentData;
                        break;
                    case 'excused':
                    case 'late':
                        $leaveStudents[] = $studentData;
                        break;
                }
            }

            // Get grade color
            $gradeColors = [
                '7' => '#16A34A', // green
                '8' => '#DC2626', // red
                '9' => '#2563EB', // blue
                '10' => '#7C3AED', // purple
                '11' => '#EA580C', // orange
                '12' => '#0891B2', // cyan
            ];
            $gradeColor = $gradeColors[$grade->level] ?? '#6B7280';

            $dateCarbon = Carbon::parse($date);

            // Get curriculum data
            $curriculum = $this->getCurriculumData($period->subject, $schoolClass->id);

            // Get class remarks for this date
            $classRemarksData = ClassRemark::where('class_id', $schoolClass->id)
                ->where('subject_id', $period->subject_id)
                ->where('period_id', $period->id)
                ->whereDate('date', $date)
                ->get()
                ->map(function ($remark) {
                    return [
                        'id' => $remark->id,
                        'text' => $remark->remark,
                        'type' => $remark->type ?? 'note',
                        'date' => Carbon::parse($remark->date)->format('M j, Y'),
                    ];
                })
                ->values()
                ->toArray();

            // Get student remarks for this date
            $studentRemarksData = StudentRemark::where('class_id', $schoolClass->id)
                ->where('subject_id', $period->subject_id)
                ->where('period_id', $period->id)
                ->whereDate('date', $date)
                ->with(['student.user', 'student' => function($q) {
                    $q->select('id', 'user_id', 'photo_path');
                }])
                ->get()
                ->map(function ($remark) {
                    $student = $remark->student;
                    return [
                        'id' => $remark->id,
                        'student_id' => $student->id,
                        'student_name' => $student->user->name,
                        'student_avatar' => $student->photo_path 
                            ? url('storage/' . $student->photo_path) 
                            : 'http://10.73.128.219:8088/storage/student_default_profile.jpg',
                        'text' => $remark->remark,
                        'type' => $remark->type ?? 'note',
                        'date' => Carbon::parse($remark->date)->format('M j, Y'),
                    ];
                })
                ->values()
                ->toArray();

            // Get homework data
            $totalStudents = count($attendances);
            
            // Homework assigned on this date
            $assignedHomework = Homework::where('class_id', $schoolClass->id)
                ->where('subject_id', $period->subject_id)
                ->whereDate('assigned_date', $date)
                ->get()
                ->map(function ($hw) {
                    return [
                        'id' => $hw->id,
                        'title' => $hw->title,
                        'description' => $hw->description,
                        'due_date' => $hw->due_date ? Carbon::parse($hw->due_date)->format('M j, Y') : null,
                    ];
                })
                ->values()
                ->toArray();

            // Homework collected/due on this date
            $collectedHomework = Homework::where('class_id', $schoolClass->id)
                ->where('subject_id', $period->subject_id)
                ->whereDate('due_date', $date)
                ->with('submissions')
                ->get()
                ->map(function ($hw) use ($totalStudents) {
                    $submitted = $hw->submissions()->whereIn('status', ['submitted', 'graded'])->count();
                    return [
                        'id' => $hw->id,
                        'title' => $hw->title,
                        'submitted' => $submitted,
                        'not_submitted' => $totalStudents - $submitted,
                        'total' => $totalStudents,
                    ];
                })
                ->values()
                ->toArray();

            $homework = [
                'assigned' => $assignedHomework,
                'collected' => $collectedHomework,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'record' => [
                        'id' => $recordId,
                        'grade' => $schoolClass->name,
                        'class_id' => $schoolClass->id,
                        'period' => 'P' . $period->period_number,
                        'subject' => $period->subject->name,
                        'date' => $dateCarbon->format('M j, Y'),
                        'date_raw' => $dateCarbon->format('Y-m-d'),
                        'time' => format_time($period->starts_at) . ' - ' . format_time($period->ends_at),
                        'room' => $period->room?->name ?? $schoolClass->room?->name ?? 'Not assigned',
                        'grade_color' => $gradeColor,
                    ],
                    'attendance' => [
                        'present' => count($presentStudents),
                        'absent' => count($absentStudents),
                        'leave' => count($leaveStudents),
                        'late' => 0,
                        'total' => count($attendances),
                        'is_taken' => count($attendances) > 0,
                        'present_students' => $presentStudents,
                        'absent_students' => $absentStudents,
                        'leave_students' => $leaveStudents,
                    ],
                    'curriculum' => $curriculum,
                    'class_remarks' => $classRemarksData,
                    'student_remarks' => $studentRemarksData,
                    'homework' => $homework,
                    'class_info' => [
                        'id' => $schoolClass->id,
                        'section' => $grade->name . ' ' . $schoolClass->name,
                        'room' => $period->room?->name ?? $schoolClass->room?->name ?? 'Not assigned',
                        'class_teacher' => $schoolClass->teacher ? $schoolClass->teacher->user->name : 'Not assigned',
                        'total_students' => count($attendances),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch class record details',
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
}