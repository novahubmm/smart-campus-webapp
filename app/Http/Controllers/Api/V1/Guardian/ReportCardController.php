<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianReportCardRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportCardController extends Controller
{
    public function __construct(
        private readonly GuardianReportCardRepositoryInterface $reportCardRepository
    ) {}

    /**
     * Get Report Cards List
     * GET /api/v1/guardian/report-cards?student_id={id}
     * GET /api/v1/guardian/students/{student_id}/report-cards (NEW)
     */
    public function index(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $reportCards = $this->reportCardRepository->getReportCards($student);

            return ApiResponse::success($reportCards);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve report cards: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Report Card Detail
     * GET /api/v1/guardian/report-cards/{id}?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/report-cards/{id} (NEW)
     */
    public function show(Request $request, string $id, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $reportCard = $this->reportCardRepository->getReportCardDetail($id, $student);

            return ApiResponse::success($reportCard);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve report card: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Latest Report Card
     * GET /api/v1/guardian/report-cards/latest?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/report-cards/latest (NEW)
     */
    public function latest(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $reportCard = $this->reportCardRepository->getLatestReportCard($student);

            return ApiResponse::success($reportCard);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve latest report card: ' . $e->getMessage(), 500);
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
