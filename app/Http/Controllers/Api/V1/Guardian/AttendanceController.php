<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianAttendanceRepositoryInterface;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly GuardianAttendanceRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Get Attendance Records
     * GET /api/v1/guardian/attendance?student_id={id}&month={month}&year={year}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $records = $this->attendanceRepository->getAttendanceRecords($student, $month, $year);

            return ApiResponse::success($records);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Attendance Summary
     * GET /api/v1/guardian/attendance/summary?student_id={id}&month={month}&year={year}
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $summary = $this->attendanceRepository->getAttendanceSummary($student, $month, $year);

            return ApiResponse::success($summary);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance summary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Attendance Calendar
     * GET /api/v1/guardian/attendance/calendar?student_id={id}&month={month}&year={year}
     */
    public function calendar(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $calendar = $this->attendanceRepository->getAttendanceCalendar($student, $month, $year);

            return ApiResponse::success($calendar);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance calendar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Attendance Stats
     * GET /api/v1/guardian/attendance/stats?student_id={id}
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

            $stats = $this->attendanceRepository->getAttendanceStats($student);

            return ApiResponse::success($stats);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to get authorized student
     */
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
