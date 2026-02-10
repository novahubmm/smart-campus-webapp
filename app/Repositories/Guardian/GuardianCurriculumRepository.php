<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianCurriculumRepositoryInterface;
use App\Models\CurriculumChapter;
use App\Models\CurriculumProgress;
use App\Models\GradeSubject;
use App\Models\StudentProfile;
use App\Models\Subject;

class GuardianCurriculumRepository implements GuardianCurriculumRepositoryInterface
{
    public function getCurriculum(StudentProfile $student): array
    {
        $gradeSubjects = GradeSubject::where('grade_id', $student->grade_id)
            ->with('subject')
            ->get();

        $subjects = $gradeSubjects->map(function ($gs) use ($student) {
            $subject = $gs->subject;
            
            // Get curriculum progress
            $chapters = CurriculumChapter::where('subject_id', $subject->id)
                ->where('grade_id', $student->grade_id)
                ->get();

            $completedChapters = CurriculumProgress::where('student_id', $student->id)
                ->whereIn('chapter_id', $chapters->pluck('id'))
                ->where('status', 'completed')
                ->count();

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon ?? 'book',
                'total_chapters' => $chapters->count(),
                'completed_chapters' => $completedChapters,
            ];
        });

        return [
            'grade' => $student->grade?->name ?? 'N/A',
            'subjects' => $subjects->toArray(),
        ];
    }

    public function getSubjectCurriculum(string $subjectId, StudentProfile $student): array
    {
        $subject = Subject::findOrFail($subjectId);
        
        $gradeSubject = GradeSubject::where('grade_id', $student->grade_id)
            ->where('subject_id', $subjectId)
            ->with('teacher.user')
            ->first();
        
        // Get curriculum chapters for this subject
        $chapters = CurriculumChapter::where('subject_id', $subjectId)
            ->where(function ($query) use ($student) {
                $query->where('grade_id', $student->grade_id)
                      ->orWhereNull('grade_id');
            })
            ->with(['topics' => function ($query) use ($student) {
                $query->with(['progress' => function ($q) use ($student) {
                    $q->where('class_id', $student->class_id);
                }])->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        $totalTopics = 0;
        $completedTopics = 0;
        $completedChapters = 0;

        $curriculumData = $chapters->map(function ($chapter, $index) use ($student, &$totalTopics, &$completedTopics, &$completedChapters) {
            $topics = $chapter->topics->map(function ($topic) {
                $progress = $topic->progress->first();
                $status = $progress?->status ?? 'not_started';
                
                // Map status to match mobile app expectations
                if ($status === 'not_started' || !$progress) {
                    $status = 'upcoming';
                }
                
                return [
                    'id' => (string) $topic->id,
                    'name' => $topic->title,
                    'order' => $topic->order,
                    'status' => $status,
                    'duration' => '3 hours', // Default duration
                ];
            });

            $chapterTotalTopics = $topics->count();
            $chapterCompletedTopics = $topics->where('status', 'completed')->count();
            $chapterInProgressTopics = $topics->where('status', 'in_progress')->count();
            $chapterCurrentTopic = $topics->where('status', 'current')->first();
            
            $totalTopics += $chapterTotalTopics;
            $completedTopics += $chapterCompletedTopics;
            
            // Determine chapter status
            $chapterStatus = 'not_started';
            $currentTopicName = null;
            
            if ($chapterCompletedTopics === $chapterTotalTopics && $chapterTotalTopics > 0) {
                $chapterStatus = 'completed';
                $completedChapters++;
            } elseif ($chapterCompletedTopics > 0 || $chapterInProgressTopics > 0 || $chapterCurrentTopic) {
                $chapterStatus = 'in_progress';
                $currentTopicName = $chapterCurrentTopic['name'] ?? null;
            }
            
            $progressPercentage = $chapterTotalTopics > 0 
                ? round(($chapterCompletedTopics / $chapterTotalTopics) * 100, 1)
                : 0;

            // Get related items (exams and homework)
            $relatedItems = [];
            
            // Get exams
            $exams = \App\Models\ExamMark::where('student_id', $student->id)
                ->where('subject_id', $chapter->subject_id)
                ->with('exam')
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();
            
            foreach ($exams as $examMark) {
                if ($examMark->exam) {
                    $totalMarks = $examMark->exam->total_marks ?? 100;
                    $relatedItems[] = [
                        'type' => 'exam',
                        'id' => $examMark->exam->id,
                        'title' => $examMark->exam->name,
                        'score' => $examMark->marks_obtained . '/' . $totalMarks,
                        'date' => $examMark->created_at->format('Y-m-d'),
                    ];
                }
            }
            
            // Get homework
            $homework = \App\Models\Homework::where('subject_id', $chapter->subject_id)
                ->where('class_id', $student->class_id)
                ->orderBy('due_date', 'desc')
                ->limit(2)
                ->get();
            
            foreach ($homework as $hw) {
                $relatedItems[] = [
                    'type' => 'homework',
                    'id' => $hw->id,
                    'title' => $hw->title,
                    'due_date' => $hw->due_date?->format('Y-m-d'),
                ];
            }

            $chapterData = [
                'id' => (string) $chapter->id,
                'number' => $chapter->order,
                'title' => $chapter->title,
                'description' => 'Chapter ' . $chapter->order . ' content',
                'total_topics' => $chapterTotalTopics,
                'completed_topics' => $chapterCompletedTopics,
                'progress_percentage' => $progressPercentage,
                'status' => $chapterStatus,
                'topics' => $topics->values()->toArray(),
                'related_items' => $relatedItems,
            ];
            
            if ($currentTopicName) {
                $chapterData['current_topic'] = $currentTopicName;
            }
            
            return $chapterData;
        });

        $totalChapters = $curriculumData->count();
        $overallProgress = $totalChapters > 0 
            ? round(($completedChapters / $totalChapters) * 100, 1)
            : 0;

        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'icon' => $subject->icon ?? '📚',
            'teacher' => $gradeSubject?->teacher?->user?->name ?? 'N/A',
            'total_chapters' => $totalChapters,
            'completed_chapters' => $completedChapters,
            'progress_percentage' => $overallProgress,
            'total_topics' => $totalTopics,
            'completed_topics' => $completedTopics,
            'chapters' => $curriculumData->values()->toArray(),
        ];
    }

    public function getChapters(string $subjectId): array
    {
        $chapters = CurriculumChapter::where('subject_id', $subjectId)
            ->orderBy('chapter_number')
            ->get();

        return $chapters->map(function ($chapter) {
            return [
                'id' => $chapter->id,
                'chapter_number' => $chapter->chapter_number,
                'title' => $chapter->title,
                'description' => $chapter->description,
            ];
        })->toArray();
    }

    public function getChapterDetail(string $chapterId): array
    {
        $chapter = CurriculumChapter::with(['subject', 'topics'])->findOrFail($chapterId);

        return [
            'id' => $chapter->id,
            'chapter_number' => $chapter->chapter_number,
            'title' => $chapter->title,
            'description' => $chapter->description,
            'subject' => [
                'id' => $chapter->subject?->id,
                'name' => $chapter->subject?->name,
            ],
            'topics' => $chapter->topics->map(function ($topic) {
                return [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'description' => $topic->description,
                    'order' => $topic->order,
                ];
            })->toArray(),
        ];
    }
}
