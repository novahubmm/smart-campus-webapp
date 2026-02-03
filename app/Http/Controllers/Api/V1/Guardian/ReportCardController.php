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
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
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

            $reportCard = $this->reportCardRepository->getReportCardDetail($id, $student);

            return ApiResponse::success($reportCard);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve report card: ' . $e->getMessage(), 500);
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
