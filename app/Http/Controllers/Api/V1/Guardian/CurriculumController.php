<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianCurriculumRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function __construct(
        private readonly GuardianCurriculumRepositoryInterface $curriculumRepository
    ) {}

    /**
     * Get Curriculum Overview
     * GET /api/v1/guardian/curriculum?student_id={id}
     * GET /api/v1/guardian/students/{student_id}/curriculum (NEW)
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

            $curriculum = $this->curriculumRepository->getCurriculum($student);

            return ApiResponse::success($curriculum);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve curriculum: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Curriculum
     * GET /api/v1/guardian/curriculum/subjects/{id}?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/curriculum/subjects/{subject_id} (NEW)
     */
    public function subjectCurriculum(Request $request, ?string $studentId = null, ?string $subjectId = null): JsonResponse
    {
        // Handle both route patterns
        // Old: /curriculum/subjects/{id}?student_id={student_id} -> $studentId has subject_id, $subjectId is null
        // New: /students/{student_id}/curriculum/subjects/{subject_id} -> both params populated
        
        if ($studentId && !$subjectId) {
            // Old pattern: first param is actually subject_id
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

            $curriculum = $this->curriculumRepository->getSubjectCurriculum($subjectId, $student);

            return ApiResponse::success($curriculum);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject curriculum: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Chapters
     * GET /api/v1/guardian/curriculum/chapters?subject_id={subject_id}
     * GET /api/v1/guardian/students/{student_id}/curriculum/chapters?subject_id={subject_id} (NEW)
     */
    public function chapters(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|string',
        ]);

        try {
            $subjectId = $request->input('subject_id');
            $chapters = $this->curriculumRepository->getChapters($subjectId);

            return ApiResponse::success($chapters);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve chapters: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Chapter Detail
     * GET /api/v1/guardian/curriculum/chapters/{id}
     * GET /api/v1/guardian/students/{student_id}/curriculum/chapters/{id} (NEW)
     */
    public function chapterDetail(string $id, ?string $studentId = null): JsonResponse
    {
        try {
            $chapter = $this->curriculumRepository->getChapterDetail($id);

            return ApiResponse::success($chapter);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve chapter: ' . $e->getMessage(), 500);
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
