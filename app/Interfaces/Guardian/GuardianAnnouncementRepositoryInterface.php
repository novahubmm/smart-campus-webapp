<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianAnnouncementRepositoryInterface
{
    public function getAnnouncements(StudentProfile $student, ?string $category = null, ?bool $isRead = null, ?bool $isPinned = null, string $guardianId = null): array;

    public function getAnnouncementDetail(string $announcementId, ?string $guardianId = null): array;

    public function markAsRead(string $announcementId, string $guardianId): array;
    
    public function markAsUnread(string $announcementId, string $guardianId): array;

    public function markAllAsRead(string $guardianId): bool;
    
    public function pinAnnouncement(string $announcementId, string $guardianId): array;
    
    public function unpinAnnouncement(string $announcementId, string $guardianId): array;
    
    public function getAnnouncementsByCalendar(StudentProfile $student, int $year, int $month, string $guardianId): array;
}
