<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display staff notifications
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get notifications for the current staff user
        $notifications = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get unread count
        $unreadCount = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('staff.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();
        
        // Clear the cache so next request gets updated count
        \Cache::forget("unread_notifications_count_{$user->id}");

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        
        Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        // Clear the cache so next request gets updated count
        \Cache::forget("unread_notifications_count_{$user->id}");

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Always get fresh count (no caching) for real-time updates
        $count = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get notifications list (for AJAX real-time updates with pagination)
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = 10;
        $page = max(1, (int) $request->get('page', 1));
        
        // Get paginated notifications for the current staff user
        $query = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc');
            
        $totalCount = $query->count();
        $lastPage = max(1, (int) ceil($totalCount / $perPage));
        
        $notifications = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'message' => \Illuminate\Support\Str::limit($data['message'] ?? 'No message content', 100),
                    'priority' => $data['priority'] ?? 'medium',
                    'announcement_id' => $data['announcement_id'] ?? null,
                    'is_unread' => is_null($notification->read_at),
                    'created_at' => $notification->created_at->format('M d, Y'),
                    'created_time' => $notification->created_at->format('g:i A'),
                ];
            });
            
        $unreadCount = Notification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'total_count' => $totalCount,
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'from' => $totalCount > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => min($page * $perPage, $totalCount),
            ],
        ]);
    }

    /**
     * Save FCM token for push notifications
     */
    public function saveFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $request->token]);

        return response()->json(['success' => true]);
    }

    /**
     * Show notification detail
     */
    public function show(Request $request, string $id): View
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            abort(404, 'Notification not found');
        }

        // Mark as read if not already read
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return view('staff.notifications.show', [
            'notification' => $notification,
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();
        
        // Clear the cache so next request gets updated count
        \Cache::forget("unread_notifications_count_{$user->id}");

        return response()->json(['success' => true]);
    }
}