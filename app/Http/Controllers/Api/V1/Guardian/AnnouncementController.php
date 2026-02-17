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
     * GET /api/v1/guardian/students/{student_id}/announcements?category={category}&is_read={boolean} (NEW)
     */
    public function index(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'category' => 'nullable|string',
            'is_read' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $guardianId = $request->user()->guardianProfile?->id;
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $category = $request->input('category');
            $isRead = $request->has('is_read') ? $request->boolean('is_read') : null;
            $isPinned = $request->has('is_pinned') ? $request->boolean('is_pinned') : null;
            
            $announcements = $this->announcementRepository->getAnnouncements($student, $category, $isRead, $isPinned, $guardianId);

            return ApiResponse::success($announcements);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve announcements: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Announcement Detail
     * GET /api/v1/guardian/announcements/{id}
     * GET /api/v1/guardian/students/{student_id}/announcements/{announcement_id} (NEW)
     */
    public function show(Request $request, ?string $student_id = null, ?string $announcement_id = null): JsonResponse
    {
        try {
            // Handle both old and new route formats
            // Old format: /announcements/{id} - first param is announcement_id
            // New format: /students/{student_id}/announcements/{announcement_id}
            $announcementId = $announcement_id ?? $student_id;
            
            $guardianId = $request->user()->guardianProfile?->id;
            
            $announcement = $this->announcementRepository->getAnnouncementDetail($announcementId, $guardianId);

            return ApiResponse::success($announcement);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('Announcement not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve announcement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Announcement as Read
     * POST /api/v1/guardian/announcements/{id}/read
     * POST /api/v1/guardian/students/{student_id}/announcements/{announcement_id}/read (NEW)
     */
    public function markAsRead(Request $request, ?string $student_id = null, ?string $announcement_id = null): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            // Handle both old and new route formats
            $announcementId = $announcement_id ?? $student_id;

            $result = $this->announcementRepository->markAsRead($announcementId, $guardianId);

            return ApiResponse::success($result, 'Announcement marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark announcement as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Announcement as Unread
     * PUT /api/v1/guardian/announcements/{id}/unread
     * PUT /api/v1/guardian/students/{student_id}/announcements/{announcement_id}/unread (NEW)
     */
    public function markAsUnread(Request $request, ?string $student_id = null, ?string $announcement_id = null): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            // Handle both old and new route formats
            $announcementId = $announcement_id ?? $student_id;

            $result = $this->announcementRepository->markAsUnread($announcementId, $guardianId);

            return ApiResponse::success($result, 'Announcement marked as unread');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark announcement as unread: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Pin Announcement
     * PUT /api/v1/guardian/announcements/{id}/pin
     * PUT /api/v1/guardian/students/{student_id}/announcements/{announcement_id}/pin (NEW)
     */
    public function pinAnnouncement(Request $request, ?string $student_id = null, ?string $announcement_id = null): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            // Handle both old and new route formats
            $announcementId = $announcement_id ?? $student_id;

            $result = $this->announcementRepository->pinAnnouncement($announcementId, $guardianId);

            return ApiResponse::success($result, 'Announcement pinned successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to pin announcement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unpin Announcement
     * PUT /api/v1/guardian/announcements/{id}/unpin
     * PUT /api/v1/guardian/students/{student_id}/announcements/{announcement_id}/unpin (NEW)
     */
    public function unpinAnnouncement(Request $request, ?string $student_id = null, ?string $announcement_id = null): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            // Handle both old and new route formats
            $announcementId = $announcement_id ?? $student_id;

            $result = $this->announcementRepository->unpinAnnouncement($announcementId, $guardianId);

            return ApiResponse::success($result, 'Announcement unpinned successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to unpin announcement: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Announcements by Calendar
     * GET /api/v1/guardian/announcements/calendar
     * GET /api/v1/guardian/students/{student_id}/announcements/calendar (NEW)
     */
    public function calendar(Request $request, ?string $student_id = null): JsonResponse
    {
        $request->validate([
            'student_id' => $student_id ? 'nullable|string' : 'required|string',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $student_id);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $guardianId = $request->user()->guardianProfile?->id;
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $year = $request->integer('year');
            $month = $request->integer('month');

            $result = $this->announcementRepository->getAnnouncementsByCalendar($student, $year, $month, $guardianId);

            return ApiResponse::success($result, 'Calendar announcements retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve calendar announcements: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark All Announcements as Read
     * POST /api/v1/guardian/announcements/mark-all-read
     * POST /api/v1/guardian/students/{student_id}/announcements/mark-all-read (NEW)
     */
    public function markAllAsRead(Request $request, ?string $student_id = null): JsonResponse
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
