<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Get all subjects for the teacher
     * GET /api/v1/teacher/subjects
     */
    public function index(Request $request): JsonResponse
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

            // Get subjects from periods (what teacher actually teaches)
            $subjects = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->where('is_break', false)
                ->with(['subject', 'timetable.schoolClass.grade'])
                ->get()
                ->groupBy('subject_id')
                ->map(function ($periods) {
                    $firstPeriod = $periods->first();
                    $subject = $firstPeriod->subject;
                    
                    // Get unique classes for this subject
                    $classes = $periods->map(fn($p) => [
                        'id' => $p->timetable?->schoolClass?->id,
                        'name' => $p->timetable?->schoolClass?->name,
                        'grade' => $p->timetable?->schoolClass?->grade?->name,
                    ])->unique('id')->values();

                    // Calculate periods_per_timetable (total periods across all timetables)
                    $periodsPerTimetable = $periods->count();

                    return [
                        'id' => $subject?->id,
                        'name' => $subject?->name ?? 'Unknown Subject',
                        'code' => $subject?->code,
                        'periods_per_timetable' => $periodsPerTimetable,
                        'icon' => $subject?->icon ?? '📚',
                        'icon_color' => $subject?->icon_color ?? '#6B7280',
                        'progress_color' => $subject?->progress_color ?? '#6B7280',
                        'classes' => $classes,
                        'classes_count' => $classes->count(),
                    ];
                })
                ->filter(fn($s) => $s['id'] !== null)
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'subjects' => $subjects,
                    'total' => $subjects->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject detail
     * GET /api/v1/teacher/subjects/{id}
     */
    public function show(Request $request, string $id): JsonResponse
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

            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Verify teacher teaches this subject
            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('subject_id', $id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->with(['timetable.schoolClass.grade', 'timetable.schoolClass.enrolledStudents'])
                ->get();

            if ($periods->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not teach this subject'
                ], 403);
            }

            // Get classes for this subject
            $classes = $periods->map(fn($p) => [
                'id' => $p->timetable?->schoolClass?->id,
                'name' => $p->timetable?->schoolClass?->name,
                'grade' => $p->timetable?->schoolClass?->grade?->name,
                'student_count' => $p->timetable?->schoolClass?->enrolledStudents?->count() ?? 0,
            ])->unique('id')->values();

            // Calculate periods_per_timetable
            $periodsPerTimetable = $periods->count();

            // Load curriculum for progress calculation
            $subject->load('curriculumChapters.topics');
            $totalChapters = $subject->curriculumChapters->count();
            $totalTopics = $subject->curriculumChapters->sum(fn($ch) => $ch->topics->count());
            // TODO: Calculate completed chapters/topics when tracking is implemented
            $completedChapters = 0;
            $completedTopics = 0;
            $overallProgress = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'periods_per_timetable' => $periodsPerTimetable,
                    'icon' => $subject->icon ?? '📚',
                    'icon_color' => $subject->icon_color ?? '#6B7280',
                    'progress_color' => $subject->progress_color ?? '#6B7280',
                    'classes' => $classes,
                    'classes_count' => $classes->count(),
                    'total_students' => $classes->sum('student_count'),
                    'curriculum_progress' => [
                        'total_chapters' => $totalChapters,
                        'completed_chapters' => $completedChapters,
                        'total_topics' => $totalTopics,
                        'completed_topics' => $completedTopics,
                        'overall_progress' => $overallProgress,
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subject curriculum
     * GET /api/v1/teacher/subjects/{id}/curriculum
     */
    public function curriculum(Request $request, string $id): JsonResponse
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

            $subject = Subject::with(['curriculumChapters.topics'])->find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Verify teacher teaches this subject
            $hasAccess = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('subject_id', $id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not teach this subject'
                ], 403);
            }

            $chapters = $subject->curriculumChapters->map(function ($chapter) {
                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'description' => $chapter->description,
                    'order' => $chapter->order,
                    'topics' => $chapter->topics->map(fn($topic) => [
                        'id' => $topic->id,
                        'title' => $topic->title,
                        'description' => $topic->description,
                        'order' => $topic->order,
                        'duration_minutes' => $topic->duration_minutes,
                    ])->sortBy('order')->values(),
                    'topics_count' => $chapter->topics->count(),
                ];
            })->sortBy('order')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'subject' => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'icon' => $subject->icon ?? '📚',
                        'progress_color' => $subject->progress_color ?? '#6B7280',
                    ],
                    'chapters' => $chapters,
                    'total_chapters' => $chapters->count(),
                    'total_topics' => $chapters->sum('topics_count'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch curriculum',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teaching periods for a subject
     * GET /api/v1/teacher/subjects/{id}/teaching-periods
     */
    public function teachingPeriods(Request $request, string $id): JsonResponse
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

            $request->validate([
                'class_id' => 'required|uuid|exists:classes,id',
                'month' => 'nullable|integer|min:1|max:12',
                'year' => 'nullable|integer|min:2020|max:2100',
            ]);

            $classId = $request->input('class_id');
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $subject = Subject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ], 404);
            }

            // Get periods for this subject, class, and teacher
            $periods = Period::where('teacher_profile_id', $teacherProfile->id)
                ->where('subject_id', $id)
                ->where('is_break', false)
                ->whereHas('timetable', function ($q) use ($classId) {
                    $q->where('is_active', true)
                      ->where('class_id', $classId);
                })
                ->with(['timetable.schoolClass.grade'])
                ->get();

            if ($periods->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No teaching periods found for this subject and class'
                ], 404);
            }

            $schoolClass = $periods->first()->timetable->schoolClass;

            // Generate actual dates for the month based on timetable pattern
            $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
            $today = now()->startOfDay();

            $teachingPeriods = collect();

            // Map day_of_week to Carbon day constants (1=Monday, 7=Sunday)
            foreach ($periods as $period) {
                $dayOfWeek = $period->day_of_week; // 1=Monday, 7=Sunday
                
                // Find all dates in the month that match this day of week
                $currentDate = $startOfMonth->copy();
                
                // Adjust to first occurrence of this day in the month
                while ($currentDate->dayOfWeekIso !== $dayOfWeek && $currentDate->lte($endOfMonth)) {
                    $currentDate->addDay();
                }

                // Add all occurrences of this day in the month
                while ($currentDate->lte($endOfMonth)) {
                    $periodDate = $currentDate->copy();
                    $isCompleted = $periodDate->lt($today);

                    $teachingPeriods->push([
                        'id' => $period->id . '_' . $periodDate->format('Y-m-d'),
                        'date' => $periodDate->format('Y-m-d'),
                        'date_formatted' => $periodDate->format('M j, Y'),
                        'period' => $period->period_number,
                        'period_label' => 'Period ' . $period->period_number,
                        'start_time' => format_time($period->starts_at),
                        'end_time' => format_time($period->ends_at),
                        'status' => $isCompleted ? 'completed' : 'upcoming',
                    ]);

                    $currentDate->addWeek();
                }
            }

            // Sort by date descending (most recent first)
            $sortedPeriods = $teachingPeriods->sortByDesc('date')->values();

            // Calculate statistics
            $completed = $sortedPeriods->where('status', 'completed')->count();
            $upcoming = $sortedPeriods->where('status', 'upcoming')->count();

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'subject' => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'icon' => $subject->icon ?? '📚',
                        'progress_color' => $subject->progress_color ?? '#6B7280',
                    ],
                    'class' => [
                        'id' => $schoolClass->id,
                        'name' => $schoolClass->name,
                        'grade' => $schoolClass->grade?->name,
                    ],
                    'month' => (int) $month,
                    'year' => (int) $year,
                    'statistics' => [
                        'total' => $sortedPeriods->count(),
                        'completed' => $completed,
                        'upcoming' => $upcoming,
                    ],
                    'periods' => $sortedPeriods,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teaching periods',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
