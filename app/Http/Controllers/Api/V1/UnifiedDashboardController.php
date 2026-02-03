<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\V1\Guardian\DashboardController as GuardianDashboardController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedDashboardController extends Controller
{
    public function __construct(
        private readonly TeacherDashboardController $teacherDashboard,
        private readonly GuardianDashboardController $guardianDashboard
    ) {}

    /**
     * Get dashboard data based on user role
     * GET /api/v1/dashboard
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherDashboard->stats($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianDashboard->dashboard($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get today's schedule/classes based on user role
     * GET /api/v1/dashboard/today
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherDashboard->todayClasses($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianDashboard->todaySchedule($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load today\'s data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get quick stats/summary based on user role
     * GET /api/v1/dashboard/stats
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherDashboard->stats($request);
            } elseif ($user->hasRole('guardian')) {
                // For guardians, we can create a quick stats endpoint
                return $this->getGuardianStats($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get guardian quick stats
     */
    private function getGuardianStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $guardianProfile = $user->guardianProfile;
        
        if (!$guardianProfile) {
            return ApiResponse::error('Guardian profile not found', 404);
        }

        $students = $guardianProfile->students;
        $studentIds = $students->pluck('id');

        // Calculate quick stats
        $stats = [
            'total_children' => $students->count(),
            'pending_homework' => \DB::table('homework_submissions')
                ->whereIn('student_id', $studentIds)
                ->where('status', 'pending')
                ->count(),
            'unread_announcements' => \DB::table('announcements')
                ->whereNotExists(function($query) use ($studentIds) {
                    $query->select(\DB::raw(1))
                        ->from('announcement_reads')
                        ->whereColumn('announcement_reads.announcement_id', 'announcements.id')
                        ->whereIn('announcement_reads.student_id', $studentIds);
                })
                ->count(),
            'upcoming_exams' => \DB::table('exams')
                ->join('exam_students', 'exams.id', '=', 'exam_students.exam_id')
                ->whereIn('exam_students.student_id', $studentIds)
                ->where('exams.start_date', '>=', now())
                ->where('exams.start_date', '<=', now()->addDays(30))
                ->count(),
        ];

        return ApiResponse::success($stats);
    }
}