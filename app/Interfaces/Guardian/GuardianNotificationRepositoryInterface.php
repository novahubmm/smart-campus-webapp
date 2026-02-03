<?php

namespace App\Interfaces\Guardian;

interface GuardianNotificationRepositoryInterface
{
    public function getNotifications(string $guardianId, ?string $category = null, ?bool $isRead = null): array;

    public function getUnreadCount(string $guardianId): int;

    public function markAsRead(string $notificationId): bool;

    public function markAllAsRead(string $guardianId): bool;

    public function getSettings(string $guardianId): array;

    public function updateSettings(string $guardianId, array $settings): array;
}
