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
     * GET /api/v1/guardian/students/{student_id}/exams?subject_id={subject_id} (NEW)
     */
    public function index(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'subject_id' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
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
     * GET /api/v1/guardian/students/{student_id}/exams/{id}/results (NEW)
     */
    public function results(Request $request, string $id, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
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
     * GET /api/v1/guardian/students/{student_id}/subjects (NEW)
     */
    public function subjects(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
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
     * GET /api/v1/guardian/subjects/{subject_id}?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/subjects/{subject_id} (NEW)
     */
    public function subjectDetail(Request $request, ?string $studentId = null, ?string $subjectId = null): JsonResponse
    {
        // Handle both route patterns
        if ($studentId && !$subjectId) {
            // Old pattern: /subjects/{subject_id}?student_id={student_id}
            $subjectId = $studentId;
            $studentId = null;
        }

        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $subject = $this->examRepository->getSubjectDetail($subjectId, $student);

            return ApiResponse::success($subject);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Performance
     * GET /api/v1/guardian/subjects/{subject_id}/performance?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/performance (NEW)
     */
    public function subjectPerformance(Request $request, ?string $studentId = null, ?string $subjectId = null): JsonResponse
    {
        // Handle both route patterns
        if ($studentId && !$subjectId) {
            // Old pattern: /subjects/{subject_id}/performance?student_id={student_id}
            $subjectId = $studentId;
            $studentId = null;
        }

        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $performance = $this->examRepository->getSubjectPerformance($subjectId, $student);

            return ApiResponse::success($performance);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject performance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Schedule
     * GET /api/v1/guardian/subjects/{subject_id}/schedule?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/schedule (NEW)
     */
    public function subjectSchedule(Request $request, ?string $studentId = null, ?string $subjectId = null): JsonResponse
    {
        // Handle both route patterns
        if ($studentId && !$subjectId) {
            // Old pattern: /subjects/{subject_id}/schedule?student_id={student_id}
            $subjectId = $studentId;
            $studentId = null;
        }

        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $schedule = $this->examRepository->getSubjectSchedule($subjectId, $student);

            return ApiResponse::success($schedule);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Curriculum
     * GET /api/v1/guardian/subjects/{subject_id}/curriculum?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/curriculum (NEW)
     */
    public function subjectCurriculum(Request $request, ?string $studentId = null, ?string $subjectId = null): JsonResponse
    {
        // Handle both route patterns
        if ($studentId && !$subjectId) {
            // Old pattern: /subjects/{subject_id}/curriculum?student_id={student_id}
            $subjectId = $studentId;
            $studentId = null;
        }

        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $curriculum = $this->examRepository->getSubjectCurriculum($subjectId, $student);

            return ApiResponse::success($curriculum);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject curriculum: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Performance Trends
     * GET /api/v1/guardian/exams/performance-trends?student_id={student_id}&subject_id={subject_id}
     * GET /api/v1/guardian/students/{student_id}/exams/performance-trends?subject_id={subject_id} (NEW)
     */
    public function performanceTrends(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'subject_id' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $trends = $this->examRepository->getPerformanceTrends($student, $request->subject_id);

            return ApiResponse::success($trends);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve performance trends: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Upcoming Exams
     * GET /api/v1/guardian/exams/upcoming?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/exams/upcoming (NEW)
     */
    public function upcomingExams(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $exams = $this->examRepository->getUpcomingExams($student);

            return ApiResponse::success($exams);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve upcoming exams: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Past Exams
     * GET /api/v1/guardian/exams/past?student_id={student_id}&limit={limit}
     * GET /api/v1/guardian/students/{student_id}/exams/past?limit={limit} (NEW)
     */
    public function pastExams(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $limit = $request->input('limit', 10);
            $exams = $this->examRepository->getPastExams($student, $limit);

            return ApiResponse::success($exams);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve past exams: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Compare Exams
     * POST /api/v1/guardian/exams/compare
     * POST /api/v1/guardian/students/{student_id}/exams/compare (NEW)
     */
    public function compareExams(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'exam_ids' => 'required|array|min:2|max:5',
            'exam_ids.*' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $comparison = $this->examRepository->getExamComparison($student, $request->exam_ids);

            return ApiResponse::success($comparison);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to compare exams: ' . $e->getMessage(), 500);
        }
    }

    private function getAuthorizedStudent(Request $request, ?string $studentId = null): ?StudentProfile
    {
        // Use URL parameter if provided, otherwise fall back to query parameter
        $studentId = $studentId ?? $request->input('student_id');
        
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
