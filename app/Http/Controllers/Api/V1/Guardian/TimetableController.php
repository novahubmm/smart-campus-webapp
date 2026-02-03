<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianTimetableRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function __construct(
        private readonly GuardianTimetableRepositoryInterface $timetableRepository
    ) {}

    /**
     * Get Full Timetable
     * GET /api/v1/guardian/timetable?student_id={id}
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

            $timetable = $this->timetableRepository->getFullTimetable($student);

            return ApiResponse::success($timetable);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve timetable: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Day Timetable
     * GET /api/v1/guardian/timetable/day?student_id={id}&day={day}
     */
    public function day(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $day = $request->input('day');
            $timetable = $this->timetableRepository->getDayTimetable($student, $day);

            return ApiResponse::success($timetable);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve timetable: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Class Info
     * GET /api/v1/guardian/class-info?student_id={student_id}
     * GET /api/v1/guardian/classes/{id}?student_id={student_id}
     */
    public function classInfo(Request $request, ?string $id = null): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $classInfo = $this->timetableRepository->getClassInfo($student);

            return ApiResponse::success($classInfo);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve class info: ' . $e->getMessage(), 500);
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
