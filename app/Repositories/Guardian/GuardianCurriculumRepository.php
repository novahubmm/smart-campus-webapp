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

        $chapters = CurriculumChapter::where('subject_id', $subjectId)
            ->where('grade_id', $student->grade_id)
            ->orderBy('chapter_number')
            ->get();

        $chaptersData = $chapters->map(function ($chapter) use ($student) {
            $progress = CurriculumProgress::where('student_id', $student->id)
                ->where('chapter_id', $chapter->id)
                ->first();

            return [
                'id' => $chapter->id,
                'chapter_number' => $chapter->chapter_number,
                'title' => $chapter->title,
                'description' => $chapter->description,
                'status' => $progress?->status ?? 'not_started',
                'completion_date' => $progress?->completed_at?->format('Y-m-d'),
            ];
        });

        return [
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
                'icon' => $subject->icon ?? 'book',
            ],
            'chapters' => $chaptersData->toArray(),
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
