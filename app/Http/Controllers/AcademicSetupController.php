<?php

namespace App\Http\Controllers;

use App\DTOs\Academic\BatchData;
use App\DTOs\Academic\GradeData;
use App\DTOs\Academic\RoomData;
use App\DTOs\Academic\SubjectData;
use App\Helpers\ApiResponse;
use App\Services\AcademicService;
use App\Models\Setting;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AcademicSetupController extends Controller
{
    use LogsActivity;

    public function __construct(
        private readonly AcademicService $academicService
    ) {}

    /**
     * Show the academic setup page
     */
    public function index()
    {
        $setting = \App\Models\Setting::first();
        if ($setting && $setting->setup_completed_academic) {
            return redirect()->route('dashboard');
        }
        $pageTitle = 'Smart Campus - Academic Setup';
        $pageIcon = 'fas fa-magic';
        $pageHeading = 'Academic Setup';
        $activePage = 'academic-setup';

        $steps = [
            ['label' => __('Batch'), 'icon' => 'fas fa-pencil-alt'],
            ['label' => __('Grades & Classes'), 'icon' => 'fas fa-layer-group'],
            ['label' => __('Rooms'), 'icon' => 'fas fa-door-open'],
            ['label' => __('Subjects'), 'icon' => 'fas fa-book'],
            ['label' => __('Review & Complete'), 'icon' => 'fas fa-check-circle']
        ];

        $translations = [
            'batchCreated' => __('Batch created successfully!'),
            'gradeCreated' => __('Grade created successfully!'),
            'roomCreated' => __('Room created successfully!'),
            'subjectCreated' => __('Subject created successfully!'),
            'gradeDeleted' => __('Grade deleted successfully!'),
            'roomDeleted' => __('Room deleted successfully!'),
            'subjectDeleted' => __('Subject deleted successfully!'),
            'confirmDeleteGrade' => __('Are you sure you want to delete this grade?'),
            'confirmDeleteRoom' => __('Are you sure you want to delete this room?'),
            'confirmDeleteSubject' => __('Are you sure you want to delete this subject?'),
            'confirmCompleteSetup' => __('Are you sure you want to complete the academic setup?'),
            'classLabel' => __('Class')
        ];

        return view('academic.academic-setup', compact(
            'pageTitle',
            'pageIcon',
            'pageHeading',
            'activePage',
            'steps',
            'translations'
        ));
    }

    /**
     * Setup batch
     */
    public function setupBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $batch = $this->academicService->setupBatch(BatchData::from($validated));

        $this->logCreate('Batch', $batch->id, $batch->name);

        return ApiResponse::success($batch, 'Batch created successfully');
    }

    /**
     * Setup grade with classes
     */
    public function setupGrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer|min:0',
            'classes' => 'required|array|min:1',
            'classes.*.name' => 'required|string|max:255',
            'classes.*.capacity' => 'nullable|integer|min:1',
        ]);

        $grade = $this->academicService->setupGrade(GradeData::from($validated));

        $this->logCreate('Grade', $grade->id, $grade->name);

        return ApiResponse::success($grade, 'Grade and classes created successfully');
    }

    /**
     * Setup room
     */
    public function setupRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'string|in:classroom,lab,hall',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $room = $this->academicService->setupRoom(RoomData::from($validated));

        $this->logCreate('Room', $room->id, $room->name);

        return ApiResponse::success($room, 'Room created successfully');
    }

    /**
     * Setup subject
     */
    public function setupSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_elective' => 'boolean',
        ]);

        $subject = $this->academicService->setupSubject(SubjectData::from($validated));

        $this->logCreate('Subject', $subject->id, $subject->name);

        return ApiResponse::success($subject, 'Subject created successfully');
    }

    /**
     * Get grades with details
     */
    public function getGrades(): JsonResponse
    {
        $grades = $this->academicService->getGradesWithDetails();

        return ApiResponse::success($grades);
    }

    /**
     * Get rooms
     */
    public function getRooms(): JsonResponse
    {
        $rooms = $this->academicService->getRooms();

        return ApiResponse::success($rooms);
    }

    /**
     * Get subjects
     */
    public function getSubjects(): JsonResponse
    {
        $subjects = $this->academicService->getSubjects();

        return ApiResponse::success($subjects);
    }

    /**
     * Get batches
     */
    public function getBatches(): JsonResponse
    {
        $batches = $this->academicService->getBatches();

        return ApiResponse::success($batches);
    }

    /**
     * Delete grade
     */
    public function deleteGrade(Request $request, string $gradeId): JsonResponse
    {
        $grade = \App\Models\Grade::find($gradeId);
        $gradeName = $grade?->name;

        $deleted = $this->academicService->deleteGrade($gradeId);

        if (!$deleted) {
            return ApiResponse::error('Grade not found', 404);
        }

        $this->logDelete('Grade', $gradeId, $gradeName);

        return ApiResponse::success(null, 'Grade deleted successfully');
    }

    /**
     * Delete room
     */
    public function deleteRoom(Request $request, string $roomId): JsonResponse
    {
        $room = \App\Models\Room::find($roomId);
        $roomName = $room?->name;

        $deleted = $this->academicService->deleteRoom($roomId);

        if (!$deleted) {
            return ApiResponse::error('Room not found', 404);
        }

        $this->logDelete('Room', $roomId, $roomName);

        return ApiResponse::success(null, 'Room deleted successfully');
    }

    /**
     * Delete subject
     */
    public function deleteSubject(Request $request, string $subjectId): JsonResponse
    {
        $subject = \App\Models\Subject::find($subjectId);
        $subjectName = $subject?->name;

        $deleted = $this->academicService->deleteSubject($subjectId);

        if (!$deleted) {
            return ApiResponse::error('Subject not found', 404);
        }

        $this->logDelete('Subject', $subjectId, $subjectName);

        return ApiResponse::success(null, 'Subject deleted successfully');
    }

    /**
     * Attach subject to grade
     */
    public function attachSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grade_id' => 'required|uuid|exists:grades,id',
            'subject_id' => 'required|uuid|exists:subjects,id',
        ]);

        $this->academicService->attachSubjectToGrade(
            $validated['grade_id'],
            $validated['subject_id']
        );

        $grade = \App\Models\Grade::find($validated['grade_id']);
        $subject = \App\Models\Subject::find($validated['subject_id']);
        $this->logCreate('Subject-Grade Assignment', $validated['subject_id'], ($subject?->name ?? 'Subject') . ' to ' . ($grade?->name ?? 'Grade'));

        return ApiResponse::success(null, 'Subject attached to grade successfully');
    }

    /**
     * Detach subject from grade
     */
    public function detachSubject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grade_id' => 'required|uuid|exists:grades,id',
            'subject_id' => 'required|uuid|exists:subjects,id',
        ]);

        $grade = \App\Models\Grade::find($validated['grade_id']);
        $subject = \App\Models\Subject::find($validated['subject_id']);

        $this->academicService->detachSubjectFromGrade(
            $validated['grade_id'],
            $validated['subject_id']
        );

        $this->logDelete('Subject-Grade Assignment', $validated['subject_id'], ($subject?->name ?? 'Subject') . ' from ' . ($grade?->name ?? 'Grade'));

        return ApiResponse::success(null, 'Subject detached from grade successfully');
    }

    /**
     * Complete setup
     */
    public function completeSetup(): RedirectResponse
    {
        $data = request()->all();
        $this->academicService->completeSetup($data);
        $setting = Setting::firstOrCreate([]);
        $setting->update(['setup_completed_academic' => true]);

        $this->logActivity('setup_complete', 'AcademicSetup', null, 'Completed Academic Setup');

        return redirect()
            ->route('setup.overview')
            ->with('success', __('Academic setup completed'))
            ->with('setup_completed_academic', true);
    }
}
