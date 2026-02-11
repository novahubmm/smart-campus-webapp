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
     * GET /api/v1/guardian/timetable?student_id={id}&week_start_date={date}
     * GET /api/v1/guardian/students/{student_id}/timetable?week_start_date={date} (NEW)
     */
    public function index(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'week_start_date' => 'nullable|date',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $weekStartDate = $request->input('week_start_date');
            $timetable = $this->timetableRepository->getFullTimetable($student, $weekStartDate);

            return ApiResponse::success($timetable);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve timetable: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Day Timetable
     * GET /api/v1/guardian/timetable/day?student_id={id}&day={day}
     * GET /api/v1/guardian/students/{student_id}/timetable/day?day={day} (NEW)
     */
    public function day(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'day' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
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
     * GET /api/v1/guardian/students/{student_id}/class-info (NEW)
     */
    public function classInfo(Request $request, ?string $studentId = null): JsonResponse
    {
        // If studentId is in URL, don't require it in query params
        if (!$studentId) {
            $request->validate([
                'student_id' => 'required|string',
            ]);
        }

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $classInfo = $this->timetableRepository->getClassInfo($student);

            return ApiResponse::success($classInfo);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve class info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Detailed Class Info
     * GET /api/v1/guardian/class-details?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/class-details (NEW)
     */
    public function detailedClassInfo(Request $request, ?string $studentId = null): JsonResponse
    {
        // If studentId is in URL, don't require it in query params
        if (!$studentId) {
            $request->validate([
                'student_id' => 'required|string',
            ]);
        }

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $classInfo = $this->timetableRepository->getDetailedClassInfo($student);

            return ApiResponse::success($classInfo);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve detailed class info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Class Teachers
     * GET /api/v1/guardian/class-teachers?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/class-teachers (NEW)
     */
    public function classTeachers(Request $request, ?string $studentId = null): JsonResponse
    {
        // If studentId is in URL, don't require it in query params
        if (!$studentId) {
            $request->validate([
                'student_id' => 'required|string',
            ]);
        }

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $teachers = $this->timetableRepository->getClassTeachers($student);

            return ApiResponse::success($teachers);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve class teachers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Class Statistics
     * GET /api/v1/guardian/class-statistics?student_id={student_id}
     * GET /api/v1/guardian/students/{student_id}/class-statistics (NEW)
     */
    public function classStatistics(Request $request, ?string $studentId = null): JsonResponse
    {
        // If studentId is in URL, don't require it in query params
        if (!$studentId) {
            $request->validate([
                'student_id' => 'required|string',
            ]);
        }

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $statistics = $this->timetableRepository->getClassStatistics($student);

            return ApiResponse::success($statistics);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve class statistics: ' . $e->getMessage(), 500);
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
