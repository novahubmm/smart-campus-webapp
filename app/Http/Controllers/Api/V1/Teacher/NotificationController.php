<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Create Notification (Admin/System use)
     * POST /api/v1/teacher/notifications
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:announcement,leave,attendance,homework,system,grade,schedule',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'data' => 'nullable|array',
                'user_id' => 'nullable|integer', // If not provided, send to current user
            ]);

            $userId = $validated['user_id'] ?? $request->user()->id;
            $user = \App\Models\User::find($userId);

            if (!$user) {
                return ApiResponse::notFound('User not found');
            }

            // Create notification
            $notification = Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\' . ucfirst($validated['type']) . 'Notification',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => array_merge($validated['data'] ?? [], [
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                ]),
                'read_at' => null,
            ]);

            return ApiResponse::success([
                'id' => $notification->id,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'created_at' => $notification->created_at->toISOString(),
            ], 'Notification created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get All Notifications
     * GET /api/v1/teacher/notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);
            $type = $request->input('type', 'all');
            $isRead = $request->input('is_read');
            $unreadOnly = $request->input('unread_only');

            $query = Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->orderByDesc('created_at');

            if ($type !== 'all') {
                $query->where('type', 'like', "%{$type}%");
            }

            // Handle both is_read and unread_only parameters for compatibility
            if ($isRead === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($isRead === 'unread') {
                $query->whereNull('read_at');
            } elseif ($unreadOnly === 'true' || $unreadOnly === true) {
                $query->whereNull('read_at');
            }

            $notifications = $query->paginate($perPage);

            $notificationsData = $notifications->map(function ($notification) {
                $data = $notification->data ?? [];
                return [
                    'id' => $notification->id,
                    'type' => $this->getNotificationType($notification->type),
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'data' => $data,
                    'is_read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

            // Get unread count by type
            $unreadByType = $this->getUnreadCountByType($user);

            return ApiResponse::success([
                'notifications' => $notificationsData->toArray(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'total_pages' => $notifications->lastPage(),
                    'has_more' => $notifications->hasMorePages(),
                ],
                'summary' => [
                    'total_unread' => array_sum($unreadByType),
                    'by_type' => $unreadByType,
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Unread Count
     * GET /api/v1/teacher/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $unreadByType = $this->getUnreadCountByType($user);

            return ApiResponse::success([
                'unread_count' => array_sum($unreadByType),
                'by_type' => $unreadByType,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve unread count: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark Notification as Read
     * POST /api/v1/teacher/notifications/{id}/read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $notification = Notification::where('id', $id)
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return ApiResponse::notFound('Notification not found');
            }

            $notification->update(['read_at' => now()]);

            return ApiResponse::success([
                'id' => $notification->id,
                'is_read' => true,
                'read_at' => $notification->read_at->toISOString(),
            ], 'Notification marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark notification as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mark All as Read
     * POST /api/v1/teacher/notifications/mark-all-read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->input('type');

            $query = Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->whereNull('read_at');

            if ($type) {
                $query->where('type', 'like', "%{$type}%");
            }

            $count = $query->count();
            $query->update(['read_at' => now()]);

            return ApiResponse::success([
                'marked_count' => $count,
            ], 'All notifications marked as read');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to mark all as read: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Notification
     * DELETE /api/v1/teacher/notifications/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $notification = Notification::where('id', $id)
                ->where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user))
                ->first();

            if (!$notification) {
                return ApiResponse::notFound('Notification not found');
            }

            $notification->delete();

            return ApiResponse::success(null, 'Notification deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Clear All Notifications
     * DELETE /api/v1/teacher/notifications/clear-all
     */
    public function clearAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $type = $request->input('type');
            $olderThanDays = $request->input('older_than_days');

            $query = Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user));

            if ($type) {
                $query->where('type', 'like', "%{$type}%");
            }

            if ($olderThanDays) {
                $query->where('created_at', '<', now()->subDays($olderThanDays));
            }

            $count = $query->count();
            $query->delete();

            return ApiResponse::success([
                'deleted_count' => $count,
            ], 'Notifications cleared successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to clear notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Notification Settings
     * GET /api/v1/teacher/notifications/settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        try {
            // Return default settings - can be extended to store in database
            return ApiResponse::success([
                'push_enabled' => true,
                'email_enabled' => false,
                'sms_enabled' => false,
                'preferences' => [
                    'announcement' => ['push' => true, 'email' => true, 'sms' => false],
                    'leave' => ['push' => true, 'email' => true, 'sms' => false],
                    'attendance' => ['push' => true, 'email' => false, 'sms' => false],
                    'homework' => ['push' => true, 'email' => false, 'sms' => false],
                    'system' => ['push' => true, 'email' => false, 'sms' => false],
                    'grade' => ['push' => true, 'email' => false, 'sms' => false],
                    'schedule' => ['push' => true, 'email' => false, 'sms' => false],
                ],
                'quiet_hours' => [
                    'enabled' => true,
                    'start' => '22:00',
                    'end' => '07:00',
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve settings: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Notification Settings
     * PUT /api/v1/teacher/notifications/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            // Placeholder - implement actual settings storage
            return ApiResponse::success([
                'push_enabled' => $request->input('push_enabled', true),
                'email_enabled' => $request->input('email_enabled', false),
                'preferences' => $request->input('preferences', []),
                'quiet_hours' => $request->input('quiet_hours', []),
            ], 'Notification settings updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update settings: ' . $e->getMessage(), 500);
        }
    }

    private function getNotificationType(string $type): string
    {
        if (str_contains($type, 'Announcement')) return 'announcement';
        if (str_contains($type, 'Leave')) return 'leave';
        if (str_contains($type, 'Attendance')) return 'attendance';
        if (str_contains($type, 'Homework')) return 'homework';
        if (str_contains($type, 'Grade')) return 'grade';
        if (str_contains($type, 'Schedule')) return 'schedule';
        return 'system';
    }

    private function getUnreadCountByType($user): array
    {
        $notifications = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->whereNull('read_at')
            ->get();

        $counts = [
            'announcement' => 0,
            'leave' => 0,
            'attendance' => 0,
            'homework' => 0,
            'system' => 0,
            'grade' => 0,
            'schedule' => 0,
        ];

        foreach ($notifications as $notification) {
            $type = $this->getNotificationType($notification->type);
            if (isset($counts[$type])) {
                $counts[$type]++;
            } else {
                $counts['system']++;
            }
        }

        return $counts;
    }
}
