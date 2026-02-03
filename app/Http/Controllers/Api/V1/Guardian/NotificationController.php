<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianNotificationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly GuardianNotificationRepositoryInterface $notificationRepository
    ) {}

    /**
     * Get Notifications
     * GET /api/v1/guardian/notifications?category={category}&is_read={boolean}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'nullable|string',
            'is_read' => 'nullable|boolean',
        ]);

        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $category = $request->input('category');
            $isRead = $request->has('is_read') ? $request->boolean('is_read') : null;

            $notifications = $this->notificationRepository->getNotifications($guardianId, $category, $isRead);

            return ApiResponse::success($notifications);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Unread Count
     * GET /api/v1/guardian/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $count = $this->notificationRepository->getUnreadCount($guardianId);

            return ApiResponse::success(['unread_count' => $count]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve unread count: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Notification as Read
     * POST /api/v1/guardian/notifications/{id}/read
     */
    public function markAsRead(string $id): JsonResponse
    {
        try {
            $this->notificationRepository->markAsRead($id);

            return ApiResponse::success(null, 'Notification marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark notification as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark All Notifications as Read
     * POST /api/v1/guardian/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $this->notificationRepository->markAllAsRead($guardianId);

            return ApiResponse::success(null, 'All notifications marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark notifications as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Notification Settings
     * GET /api/v1/guardian/notifications/settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $settings = $this->notificationRepository->getSettings($guardianId);

            return ApiResponse::success($settings);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve notification settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Notification Settings
     * PUT /api/v1/guardian/notifications/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'push_enabled' => 'nullable|boolean',
            'email_enabled' => 'nullable|boolean',
            'categories' => 'nullable|array',
        ]);

        try {
            $guardianId = $request->user()->guardianProfile?->id;
            
            if (!$guardianId) {
                return ApiResponse::error('Guardian profile not found', 404);
            }

            $settings = $this->notificationRepository->updateSettings($guardianId, $request->all());

            return ApiResponse::success($settings, 'Notification settings updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update notification settings: ' . $e->getMessage(), 500);
        }
    }
}
