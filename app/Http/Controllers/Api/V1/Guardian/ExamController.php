<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianExamRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        private readonly GuardianExamRepositoryInterface $examRepository
    ) {}

    /**
     * Get Exams List
     * GET /api/v1/guardian/exams?student_id={id}&subject_id={subject_id}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'subject_id' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $subjectId = $request->input('subject_id');
            $exams = $this->examRepository->getExams($student, $subjectId);

            return ApiResponse::success($exams);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve exams: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Exam Detail
     * GET /api/v1/guardian/exams/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $exam = $this->examRepository->getExamDetail($id);

            return ApiResponse::success($exam);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve exam: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Exam Results
     * GET /api/v1/guardian/exams/{id}/results?student_id={student_id}
     */
    public function results(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $results = $this->examRepository->getExamResults($id, $student);

            return ApiResponse::success($results);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve exam results: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subjects List
     * GET /api/v1/guardian/subjects?student_id={id}
     */
    public function subjects(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $subjects = $this->examRepository->getSubjects($student);

            return ApiResponse::success($subjects);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subjects: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Detail
     * GET /api/v1/guardian/subjects/{id}?student_id={student_id}
     */
    public function subjectDetail(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $subject = $this->examRepository->getSubjectDetail($id, $student);

            return ApiResponse::success($subject);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Performance
     * GET /api/v1/guardian/subjects/{id}/performance?student_id={student_id}
     */
    public function subjectPerformance(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $performance = $this->examRepository->getSubjectPerformance($id, $student);

            return ApiResponse::success($performance);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject performance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Schedule
     * GET /api/v1/guardian/subjects/{id}/schedule?student_id={student_id}
     */
    public function subjectSchedule(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $schedule = $this->examRepository->getSubjectSchedule($id, $student);

            return ApiResponse::success($schedule);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject schedule: ' . $e->getMessage(), 500);
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
