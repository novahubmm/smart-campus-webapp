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

            $curriculum = $this->curriculumRepository->getCurriculum($student);

            return ApiResponse::success($curriculum);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve curriculum: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Subject Curriculum
     * GET /api/v1/guardian/curriculum/subjects/{id}?student_id={student_id}
     */
    public function subjectCurriculum(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $curriculum = $this->curriculumRepository->getSubjectCurriculum($id, $student);

            return ApiResponse::success($curriculum);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve subject curriculum: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Chapters
     * GET /api/v1/guardian/curriculum/chapters?subject_id={subject_id}
     */
    public function chapters(Request $request): JsonResponse
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
     */
    public function chapterDetail(string $id): JsonResponse
    {
        try {
            $chapter = $this->curriculumRepository->getChapterDetail($id);

            return ApiResponse::success($chapter);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve chapter: ' . $e->getMessage(), 500);
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
