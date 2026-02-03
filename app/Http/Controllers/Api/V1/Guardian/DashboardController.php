<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianDashboardRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GuardianDashboardRepositoryInterface $dashboardRepository
    ) {}

    /**
     * Get Home Dashboard
     * GET /api/v1/guardian/home/dashboard?student_id={id}
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $data = $this->dashboardRepository->getDashboardData($student);

            return ApiResponse::success($data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Today's Schedule
     * GET /api/v1/guardian/today-schedule?student_id={id}
     */
    public function todaySchedule(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $schedule = $this->dashboardRepository->getTodaySchedule($student);

            return ApiResponse::success($schedule);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Upcoming Homework
     * GET /api/v1/guardian/upcoming-homework?student_id={id}&limit={limit}
     */
    public function upcomingHomework(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $limit = $request->input('limit', 5);
            $homework = $this->dashboardRepository->getUpcomingHomework($student, $limit);

            return ApiResponse::success($homework);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve homework: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Recent Announcements
     * GET /api/v1/guardian/announcements/recent?student_id={id}
     */
    public function recentAnnouncements(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $limit = $request->input('limit', 5);
            $announcements = $this->dashboardRepository->getRecentAnnouncements($student, $limit);

            return ApiResponse::success($announcements);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve announcements: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Fee Reminder
     * GET /api/v1/guardian/fee-reminder?student_id={id}
     */
    public function feeReminder(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $feeReminder = $this->dashboardRepository->getFeeReminder($student);

            return ApiResponse::success($feeReminder);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve fee reminder: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Current Live Class
     * GET /api/v1/guardian/dashboard/current-class?student_id={id}
     */
    public function currentClass(Request $request): JsonResponse
    {
        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $currentClass = $this->dashboardRepository->getCurrentClass($student);

            if (!$currentClass) {
                return ApiResponse::success([
                    'is_active' => false,
                    'class' => null,
                    'next_class' => $this->dashboardRepository->getNextClass($student),
                ], 'No active class at the moment');
            }

            return ApiResponse::success([
                'is_active' => true,
                'class' => $currentClass,
            ], 'Current class retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve current class: ' . $e->getMessage(), 500);
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

        // Check if the student belongs to this guardian
        $student = $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();

        return $student;
    }
}
