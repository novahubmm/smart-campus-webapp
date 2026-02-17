<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianNotificationRepositoryInterface;
use App\Models\Notification;
use App\Models\Setting;

class GuardianNotificationRepository implements GuardianNotificationRepositoryInterface
{
    public function getNotifications(string $guardianId, ?string $category = null, ?bool $isRead = null, int $perPage = 20)
    {
        $query = Notification::where('notifiable_id', $guardianId)
            ->where('notifiable_type', 'App\\Models\\GuardianProfile');

        if ($category) {
            $query->where('data->category', $category);
        }

        if ($isRead !== null) {
            if ($isRead) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getUnreadCount(string $guardianId): int
    {
        return Notification::where('notifiable_id', $guardianId)
            ->where('notifiable_type', 'App\\Models\\GuardianProfile')
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(string $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if (!$notification) {
            return false;
        }

        $notification->update(['read_at' => now()]);
        return true;
    }

    public function markAllAsRead(string $guardianId): bool
    {
        Notification::where('notifiable_id', $guardianId)
            ->where('notifiable_type', 'App\\Models\\GuardianProfile')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return true;
    }

    public function getSettings(string $guardianId): array
    {
        // Get notification settings from settings table or return defaults
        $settings = Setting::where('key', 'guardian_notification_settings_' . $guardianId)->first();

        if ($settings) {
            return json_decode($settings->value, true);
        }

        return [
            'push_enabled' => true,
            'email_enabled' => false,
            'categories' => [
                'announcements' => true,
                'attendance' => true,
                'exams' => true,
                'homework' => true,
                'fees' => true,
                'leave_requests' => true,
            ],
        ];
    }

    public function updateSettings(string $guardianId, array $settings): array
    {
        Setting::updateOrCreate(
            ['key' => 'guardian_notification_settings_' . $guardianId],
            ['value' => json_encode($settings)]
        );

        return $settings;
    }
}
