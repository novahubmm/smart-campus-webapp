<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianHomeworkRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function __construct(
        private readonly GuardianHomeworkRepositoryInterface $homeworkRepository
    ) {}

    /**
     * Get Homework List
     * GET /api/v1/guardian/homework?student_id={id}&status={status}&subject_id={subject_id}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'status' => 'nullable|string|in:pending,completed,overdue',
            'subject_id' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $status = $request->input('status');
            $subjectId = $request->input('subject_id');
            $homework = $this->homeworkRepository->getHomework($student, $status, $subjectId);

            return ApiResponse::success($homework);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve homework: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Homework Detail
     * GET /api/v1/guardian/homework/{id}?student_id={student_id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $homework = $this->homeworkRepository->getHomeworkDetail($id, $student);

            return ApiResponse::success($homework);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve homework: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Homework Stats
     * GET /api/v1/guardian/homework/stats?student_id={id}
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $stats = $this->homeworkRepository->getHomeworkStats($student);

            return ApiResponse::success($stats);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve homework stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Homework Status
     * PUT /api/v1/guardian/homework/{id}/status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'status' => 'required|string|in:pending,completed',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $status = $request->input('status');
            $this->homeworkRepository->updateHomeworkStatus($id, $student, $status);

            return ApiResponse::success(null, 'Homework status updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update homework status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Submit Homework
     * POST /api/v1/guardian/homework/{id}/submit
     */
    public function submit(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max per file
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $notes = $request->input('notes');
            $photos = $request->file('photos', []);

            $result = $this->homeworkRepository->submitHomework($id, $student, $notes, $photos);

            return ApiResponse::success($result, 'Homework submitted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to submit homework: ' . $e->getMessage(), 500);
        }
    }

    private function getAuthorizedStudent(Request $request): ?StudentProfile
    {
        $studentId = $request->input('student_id');
        if (!$studentId) {
            return null;
        }

        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        return $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();
    }
}
