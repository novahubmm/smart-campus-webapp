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
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $hasGuardianRole = $user->guardianProfile && $user->hasRole('guardian');
            $hasTeacherRole = $user->teacherProfile && $user->hasRole('teacher');

            // If user has both roles, merge notifications from both profiles
            if ($hasGuardianRole && $hasTeacherRole) {
                return $this->getMergedNotifications($request, $user, $perPage, $page);
            }

            // Single role - use existing logic
            if ($hasGuardianRole) {
                return $this->guardianNotifications->index($request);
            } elseif ($hasTeacherRole) {
                return $this->teacherNotifications->index($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load notifications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get merged notifications from both teacher and guardian profiles
     */
    private function getMergedNotifications(Request $request, $user, int $perPage, int $page): JsonResponse
    {
        $userId = $user->id;

        // Get all notifications for this user
        $query = \App\Models\Notification::where('notifiable_id', $userId)
            ->where('notifiable_type', get_class($user));

        // Apply filters if provided
        $isRead = $request->input('is_read');
        $category = $request->input('category');
        $type = $request->input('type');

        if ($isRead === 'read' || $isRead === true || $isRead === 'true') {
            $query->whereNotNull('read_at');
        } elseif ($isRead === 'unread' || $isRead === false || $isRead === 'false') {
            $query->whereNull('read_at');
        }

        if ($category) {
            $query->where('data->category', $category);
        }

        if ($type && $type !== 'all') {
            $query->where(function ($q) use ($type) {
                $q->where('type', 'like', "%{$type}%")
                  ->orWhere('data->type', $type);
            });
        }

        // Get all notifications
        $allNotifications = $query->orderByDesc('created_at')->get();

        // Separate notifications by role based on notification type/data
        $processedNotifications = $allNotifications->map(function ($notification) use ($user) {
            $data = $notification->data ?? [];
            $notificationType = $notification->type;
            
            // Determine role based on notification type or data
            // Guardian-specific notifications
            $guardianTypes = [
                'DailyReportReceived',
                'StudentAttendanceAlert',
                'HomeworkAssigned',
                'ExamScheduled',
                'FeeReminder',
                'LeaveRequestResponse',
            ];
            
            // Check if this is a guardian-specific notification
            $isGuardianNotification = false;
            foreach ($guardianTypes as $guardianType) {
                if (str_contains($notificationType, $guardianType)) {
                    $isGuardianNotification = true;
                    break;
                }
            }
            
            // If data has a role field, use it
            if (isset($data['role'])) {
                $role = $data['role'];
            } 
            // If notification type suggests guardian, use guardian
            elseif ($isGuardianNotification) {
                $role = 'guardian';
            }
            // For announcements and general notifications, check which profile exists
            // If user has both roles, we'll duplicate the notification for both
            else {
                $role = null; // Will be handled below
            }

            return [
                'id' => $notification->id,
                'type' => $this->getNotificationType($notification->type),
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? $data['body'] ?? '',
                'data' => $data,
                'is_read' => $notification->read_at !== null,
                'role' => $role,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
                'time_ago' => $notification->created_at->diffForHumans(),
                'timestamp' => $notification->created_at->timestamp,
                'original_notification' => $notification,
            ];
        });

        // For notifications without a specific role (like announcements),
        // duplicate them for both teacher and guardian roles
        // BUT if the notification has a role field in data, respect it
        $finalNotifications = collect();
        foreach ($processedNotifications as $notif) {
            // If notification has a specific role assigned, only show it for that role
            if ($notif['role'] !== null) {
                unset($notif['original_notification']);
                $finalNotifications->push($notif);
            } 
            // If no role specified, duplicate for both roles (legacy behavior)
            else {
                // Add as teacher
                $teacherNotif = $notif;
                $teacherNotif['role'] = 'teacher';
                unset($teacherNotif['original_notification']);
                $finalNotifications->push($teacherNotif);
                
                // Add as guardian
                $guardianNotif = $notif;
                $guardianNotif['role'] = 'guardian';
                // Create unique ID for the guardian version
                $guardianNotif['id'] = $notif['id'] . '-guardian';
                unset($guardianNotif['original_notification']);
                $finalNotifications->push($guardianNotif);
            }
        }

        // Sort by timestamp (newest first)
        $finalNotifications = $finalNotifications->sortByDesc('timestamp')->values();

        // Manual pagination
        $total = $finalNotifications->count();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedNotifications = $finalNotifications->slice($offset, $perPage)->values();

        // Remove timestamp field used for sorting
        $paginatedNotifications = $paginatedNotifications->map(function ($notification) {
            unset($notification['timestamp']);
            return $notification;
        });

        // Calculate unread counts
        $unreadQuery = \App\Models\Notification::where('notifiable_id', $userId)
            ->where('notifiable_type', get_class($user))
            ->whereNull('read_at');
        
        $totalUnread = $unreadQuery->count();
        
        // For dual-role users, count general notifications (like announcements) for both roles
        $guardianSpecificUnread = $unreadQuery->get()->filter(function ($notif) {
            $guardianTypes = ['DailyReportReceived', 'StudentAttendanceAlert', 'HomeworkAssigned', 'ExamScheduled', 'FeeReminder', 'LeaveRequestResponse'];
            foreach ($guardianTypes as $type) {
                if (str_contains($notif->type, $type)) {
                    return true;
                }
            }
            return false;
        })->count();
        
        $teacherSpecificUnread = $totalUnread - $guardianSpecificUnread;
        
        // General notifications (announcements) count for both
        $generalUnread = $unreadQuery->get()->filter(function ($notif) {
            $data = $notif->data ?? [];
            return !isset($data['role']) && str_contains($notif->type, 'Announcement');
        })->count();

        return ApiResponse::success([
            'notifications' => $paginatedNotifications->toArray(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
            ],
            'summary' => [
                'total_unread' => $totalUnread + $generalUnread, // General notifications count twice
                'guardian_unread' => $guardianSpecificUnread + $generalUnread,
                'teacher_unread' => $teacherSpecificUnread + $generalUnread,
            ],
        ]);
    }

    /**
     * Get notification type from class name
     */
    private function getNotificationType(string $type): string
    {
        $parts = explode('\\', $type);
        $className = end($parts);
        
        // Convert from PascalCase to snake_case
        $snakeCase = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        
        // Remove common suffixes
        $snakeCase = str_replace(['_notification', '_received'], '', $snakeCase);
        
        return $snakeCase;
    }

    /**
     * Get unread notification count
     * GET /api/v1/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $hasGuardianRole = $user->guardianProfile && $user->hasRole('guardian');
            $hasTeacherRole = $user->teacherProfile && $user->hasRole('teacher');

            // If user has both roles, return counts for both
            if ($hasGuardianRole && $hasTeacherRole) {
                $guardianId = $user->guardianProfile->id;
                $teacherId = $user->id;

                $guardianUnread = \App\Models\Notification::where('notifiable_id', $guardianId)
                    ->where('notifiable_type', 'App\\Models\\GuardianProfile')
                    ->whereNull('read_at')
                    ->count();

                $teacherUnread = \App\Models\Notification::where('notifiable_id', $teacherId)
                    ->where('notifiable_type', get_class($user))
                    ->whereNull('read_at')
                    ->count();

                return ApiResponse::success([
                    'unread_count' => $guardianUnread + $teacherUnread,
                    'guardian_unread' => $guardianUnread,
                    'teacher_unread' => $teacherUnread,
                ]);
            }

            // Single role
            if ($hasGuardianRole) {
                return $this->guardianNotifications->unreadCount($request);
            } elseif ($hasTeacherRole) {
                return $this->teacherNotifications->unreadCount($request);
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

            // Check which profile is active (guardian takes priority if both exist)
            if ($user->guardianProfile && $user->hasRole('guardian')) {
                return $this->guardianNotifications->markAsRead($id);
            } elseif ($user->teacherProfile && $user->hasRole('teacher')) {
                return $this->teacherNotifications->markAsRead($request, $id);
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

            // Check which profile is active (guardian takes priority if both exist)
            if ($user->guardianProfile && $user->hasRole('guardian')) {
                return $this->guardianNotifications->markAllAsRead($request);
            } elseif ($user->teacherProfile && $user->hasRole('teacher')) {
                return $this->teacherNotifications->markAllAsRead($request);
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

            // Check which profile is active (guardian takes priority if both exist)
            if ($user->guardianProfile && $user->hasRole('guardian')) {
                return $this->guardianNotifications->getSettings($request);
            } elseif ($user->teacherProfile && $user->hasRole('teacher')) {
                return $this->teacherNotifications->getSettings($request);
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

            // Check which profile is active (guardian takes priority if both exist)
            if ($user->guardianProfile && $user->hasRole('guardian')) {
                return $this->guardianNotifications->updateSettings($request);
            } elseif ($user->teacherProfile && $user->hasRole('teacher')) {
                return $this->teacherNotifications->updateSettings($request);
            }

            return ApiResponse::error('Invalid user type', 403);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update notification settings: ' . $e->getMessage(), 500);
        }
    }
}