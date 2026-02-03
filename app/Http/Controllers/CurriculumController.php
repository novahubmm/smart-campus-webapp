<?php

namespace App\Http\Controllers;

use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use App\Models\CurriculumProgress;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumController extends Controller
{
    /**
     * Display curriculum overview
     */
    public function index(Request $request)
    {
        $gradeId = $request->get('grade_id');
        $subjectId = $request->get('subject_id');

        $grades = Grade::orderBy('level')->get();
        $subjects = Subject::orderBy('name')->get();

        // Get curriculum data
        $curriculumQuery = CurriculumChapter::with(['subject', 'grade', 'topics.progress'])
            ->orderBy('order');

        if ($subjectId) {
            $curriculumQuery->where('subject_id', $subjectId);
        }

        if ($gradeId) {
            $curriculumQuery->where(function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId)->orWhereNull('grade_id');
            });
        }

        $chapters = $curriculumQuery->get();

        // Group by subject
        $curriculumBySubject = $chapters->groupBy('subject_id');

        // Calculate stats
        $totalChapters = $chapters->count();
        $totalTopics = $chapters->sum(fn($c) => $c->topics->count());

        // Get classes for progress tracking
        $classes = SchoolClass::with('grade')->orderBy('name')->get();

        return view('academic.curriculum', compact(
            'grades',
            'subjects',
            'curriculumBySubject',
            'totalChapters',
            'totalTopics',
            'classes',
            'gradeId',
            'subjectId'
        ));
    }

    /**
     * Store/Update entire curriculum for a subject (bulk save)
     */
    public function saveCurriculum(Request $request, string $subjectId)
    {
        $subject = Subject::findOrFail($subjectId);

        $validated = $request->validate([
            'chapters' => 'required|array|min:1',
            'chapters.*.id' => 'nullable|string',
            'chapters.*.title' => 'required|string|max:255',
            'chapters.*.topics' => 'nullable|array',
            'chapters.*.topics.*.id' => 'nullable|string',
            'chapters.*.topics.*.title' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($subject, $validated) {
            $existingChapterIds = [];
            $existingTopicIds = [];

            foreach ($validated['chapters'] as $chapterOrder => $chapterData) {
                // Create or update chapter
                if (!empty($chapterData['id']) && $chapterData['id'] !== 'new') {
                    $chapter = CurriculumChapter::find($chapterData['id']);
                    if ($chapter && $chapter->subject_id === $subject->id) {
                        $chapter->update([
                            'title' => $chapterData['title'],
                            'order' => $chapterOrder + 1,
                        ]);
                    }
                } else {
                    $chapter = CurriculumChapter::create([
                        'subject_id' => $subject->id,
                        'title' => $chapterData['title'],
                        'order' => $chapterOrder + 1,
                    ]);
                }

                $existingChapterIds[] = $chapter->id;

                // Handle topics
                if (!empty($chapterData['topics'])) {
                    foreach ($chapterData['topics'] as $topicOrder => $topicData) {
                        if (!empty($topicData['id']) && $topicData['id'] !== 'new') {
                            $topic = CurriculumTopic::find($topicData['id']);
                            if ($topic && $topic->chapter_id === $chapter->id) {
                                $topic->update([
                                    'title' => $topicData['title'],
                                    'order' => $topicOrder + 1,
                                ]);
                                $existingTopicIds[] = $topic->id;
                            }
                        } else {
                            $topic = CurriculumTopic::create([
                                'chapter_id' => $chapter->id,
                                'title' => $topicData['title'],
                                'order' => $topicOrder + 1,
                            ]);
                            $existingTopicIds[] = $topic->id;
                        }
                    }
                }

                // Delete removed topics from this chapter
                CurriculumTopic::where('chapter_id', $chapter->id)
                    ->whereNotIn('id', $existingTopicIds)
                    ->delete();
            }

            // Delete removed chapters
            CurriculumChapter::where('subject_id', $subject->id)
                ->whereNotIn('id', $existingChapterIds)
                ->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Curriculum saved successfully'),
            ]);
        }

        return back()->with('success', __('Curriculum saved successfully'));
    }

    /**
     * Delete a chapter
     */
    public function destroyChapter(Request $request, string $chapterId)
    {
        $chapter = CurriculumChapter::findOrFail($chapterId);
        $chapter->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Chapter deleted successfully'),
            ]);
        }

        return back()->with('success', __('Chapter deleted successfully'));
    }

    /**
     * Delete a topic
     */
    public function destroyTopic(Request $request, string $topicId)
    {
        $topic = CurriculumTopic::findOrFail($topicId);
        $topic->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Topic deleted successfully'),
            ]);
        }

        return back()->with('success', __('Topic deleted successfully'));
    }
}
