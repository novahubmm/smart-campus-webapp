<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianAnnouncementRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly GuardianAnnouncementRepositoryInterface $announcementRepository
    ) {}

    /**
     * Get Announcements List
     * GET /api/v1/guardian/announcements?student_id={id}&category={category}&is_read={boolean}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'category' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $category = $request->input('category');
            $isRead = $request->has('is_read') ? $request->boolean('is_read') : null;
            
            $announcements = $this->announcementRepository->getAnnouncements($student, $category, $isRead);

            return ApiResponse::success($announcements);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve announcements: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Announcement Detail
     * GET /api/v1/guardian/announcements/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $announcement = $this->announcementRepository->getAnnouncementDetail($id);

            return ApiResponse::success($announcement);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve announcement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Announcement as Read
     * POST /api/v1/guardian/announcements/{id}/read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $this->announcementRepository->markAsRead($id, $guardianId);

            return ApiResponse::success(null, 'Announcement marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark announcement as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark All Announcements as Read
     * POST /api/v1/guardian/announcements/mark-all-read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $this->announcementRepository->markAllAsRead($guardianId);

            return ApiResponse::success(null, 'All announcements marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark announcements as read: ' . $e->getMessage(), 500);
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
