<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianAnnouncementRepositoryInterface
{
    public function getAnnouncements(StudentProfile $student, ?string $category = null, ?bool $isRead = null): array;

    public function getAnnouncementDetail(string $announcementId): array;

    public function markAsRead(string $announcementId, string $guardianId): bool;

    public function markAllAsRead(string $guardianId): bool;
}
