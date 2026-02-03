<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CurriculumChapter;
use App\Models\CurriculumProgress;
use App\Models\CurriculumTopic;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumApiController extends Controller
{
    /**
     * Get curriculum for a subject
     */
    public function getSubjectCurriculum(Request $request, string $subjectId)
    {
        $subject = Subject::with(['subjectType', 'grades'])->findOrFail($subjectId);

        $gradeId = $request->query('grade_id');

        $query = $subject->curriculumChapters()->with('topics');

        if ($gradeId) {
            $query->where(function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId)->orWhereNull('grade_id');
            });
        }

        $chapters = $query->orderBy('order')->get();

        return ApiResponse::success([
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'type' => $subject->subjectType?->name,
            ],
            'chapters' => $chapters->map(function ($chapter) {
                return [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'order' => $chapter->order,
                    'topics_count' => $chapter->topics->count(),
                    'topics' => $chapter->topics->map(fn($topic) => [
                        'id' => $topic->id,
                        'title' => $topic->title,
                        'order' => $topic->order,
                    ]),
                ];
            }),
            'total_chapters' => $chapters->count(),
            'total_topics' => $chapters->sum(fn($c) => $c->topics->count()),
        ]);
    }

    /**
     * Get curriculum progress for a class
     */
    public function getClassProgress(Request $request, string $classId)
    {
        $class = SchoolClass::with(['grade.subjects.curriculumChapters.topics'])->findOrFail($classId);

        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return ApiResponse::error('Teacher profile not found', 403);
        }

        $subjectId = $request->query('subject_id');
        $subjects = $class->grade->subjects;

        if ($subjectId) {
            $subjects = $subjects->where('id', $subjectId);
        }

        $progressData = [];

        foreach ($subjects as $subject) {
            $chapters = $subject->curriculumChapters()
                ->where(function ($q) use ($class) {
                    $q->where('grade_id', $class->grade_id)->orWhereNull('grade_id');
                })
                ->with('topics.progress')
                ->orderBy('order')
                ->get();

            $subjectProgress = [
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                ],
                'chapters' => [],
                'stats' => [
                    'total_topics' => 0,
                    'completed' => 0,
                    'in_progress' => 0,
                    'not_started' => 0,
                ],
            ];

            foreach ($chapters as $chapter) {
                $chapterData = [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'order' => $chapter->order,
                    'topics' => [],
                ];

                foreach ($chapter->topics as $topic) {
                    $progress = $topic->progress
                        ->where('class_id', $classId)
                        ->where('teacher_id', $teacherProfile->id)
                        ->first();

                    $status = $progress?->status ?? 'not_started';

                    $chapterData['topics'][] = [
                        'id' => $topic->id,
                        'title' => $topic->title,
                        'order' => $topic->order,
                        'status' => $status,
                        'started_at' => $progress?->started_at?->format('Y-m-d'),
                        'completed_at' => $progress?->completed_at?->format('Y-m-d'),
                        'notes' => $progress?->notes,
                    ];

                    $subjectProgress['stats']['total_topics']++;
                    $subjectProgress['stats'][$status]++;
                }

                $subjectProgress['chapters'][] = $chapterData;
            }

            $total = $subjectProgress['stats']['total_topics'];
            $subjectProgress['stats']['completion_percentage'] = $total > 0
                ? round(($subjectProgress['stats']['completed'] / $total) * 100, 1)
                : 0;

            $progressData[] = $subjectProgress;
        }

        return ApiResponse::success([
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'grade' => $class->grade->level,
            ],
            'curriculum_progress' => $progressData,
        ]);
    }

    /**
     * Update topic progress
     */
    public function updateProgress(Request $request, string $topicId)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:not_started,in_progress,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return ApiResponse::error('Teacher profile not found', 403);
        }

        $topic = CurriculumTopic::findOrFail($topicId);

        $existingProgress = CurriculumProgress::where('topic_id', $topicId)
            ->where('class_id', $validated['class_id'])
            ->where('teacher_id', $teacherProfile->id)
            ->first();

        $progress = CurriculumProgress::updateOrCreate(
            [
                'topic_id' => $topicId,
                'class_id' => $validated['class_id'],
                'teacher_id' => $teacherProfile->id,
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'started_at' => $validated['status'] !== 'not_started'
                    ? ($existingProgress?->started_at ?? now())
                    : null,
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
            ]
        );

        return ApiResponse::success([
            'message' => 'Progress updated successfully',
            'progress' => [
                'topic_id' => $progress->topic_id,
                'status' => $progress->status,
                'started_at' => $progress->started_at?->format('Y-m-d'),
                'completed_at' => $progress->completed_at?->format('Y-m-d'),
                'notes' => $progress->notes,
            ],
        ]);
    }

    /**
     * Get teacher's assigned subjects with curriculum
     */
    public function getTeacherSubjects(Request $request)
    {
        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return ApiResponse::error('Teacher profile not found', 403);
        }

        $subjects = $teacherProfile->subjects()
            ->with(['subjectType', 'grades', 'curriculumChapters.topics'])
            ->get();

        return ApiResponse::success([
            'subjects' => $subjects->map(function ($subject) {
                $totalTopics = $subject->curriculumChapters->sum(fn($c) => $c->topics->count());

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'type' => $subject->subjectType?->name,
                    'grades' => $subject->grades->pluck('level'),
                    'curriculum_stats' => [
                        'total_chapters' => $subject->curriculumChapters->count(),
                        'total_topics' => $totalTopics,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Get teacher's classes with curriculum progress summary
     */
    public function getTeacherClassesProgress(Request $request)
    {
        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return ApiResponse::error('Teacher profile not found', 403);
        }

        $classes = SchoolClass::where('teacher_id', $teacherProfile->id)
            ->with(['grade.subjects.curriculumChapters.topics'])
            ->get();

        $classesData = [];

        foreach ($classes as $class) {
            $classProgress = [
                'id' => $class->id,
                'name' => $class->name,
                'grade' => $class->grade->level,
                'subjects' => [],
            ];

            foreach ($class->grade->subjects as $subject) {
                $totalTopics = 0;
                $completedTopics = 0;

                foreach ($subject->curriculumChapters as $chapter) {
                    foreach ($chapter->topics as $topic) {
                        $totalTopics++;
                        $progress = CurriculumProgress::where('topic_id', $topic->id)
                            ->where('class_id', $class->id)
                            ->where('teacher_id', $teacherProfile->id)
                            ->where('status', 'completed')
                            ->exists();

                        if ($progress) {
                            $completedTopics++;
                        }
                    }
                }

                $classProgress['subjects'][] = [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'total_topics' => $totalTopics,
                    'completed_topics' => $completedTopics,
                    'progress_percentage' => $totalTopics > 0
                        ? round(($completedTopics / $totalTopics) * 100, 1)
                        : 0,
                ];
            }

            $classesData[] = $classProgress;
        }

        return ApiResponse::success([
            'classes' => $classesData,
        ]);
    }
}
