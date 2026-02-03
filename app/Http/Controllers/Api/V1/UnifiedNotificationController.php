<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\Teacher\NotificationController as TeacherNotificationController;
use App\Http\Controllers\Api\V1\Guardian\NotificationController as GuardianNotificationController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedNotificationController extends Controller
{
    public function __construct(
        private readonly TeacherNotificationController $teacherNotifications,
        private readonly GuardianNotificationController $guardianNotifications
    ) {}

    /**
     * Get notifications based on user role
     * GET /api/v1/notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->index($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->index($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get unread notification count
     * GET /api/v1/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->unreadCount($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->unreadCount($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get unread count: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark notification as read
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->markAsRead($request, $id);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->markAsRead($request, $id);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark notification as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark all notifications as read
     * POST /api/v1/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->markAllAsRead($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->markAllAsRead($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark all notifications as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get notification settings
     * GET /api/v1/notifications/settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->getSettings($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->getSettings($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get notification settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update notification settings
     * PUT /api/v1/notifications/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasRole('teacher')) {
                return $this->teacherNotifications->updateSettings($request);
            } elseif ($user->hasRole('guardian')) {
                return $this->guardianNotifications->updateSettings($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update notification settings: ' . $e->getMessage(), 500);
        }
    }
}