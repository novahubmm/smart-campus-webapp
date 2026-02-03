<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianAnnouncementRepositoryInterface;
use App\Models\Announcement;
use App\Models\StudentProfile;
use Illuminate\Support\Str;

class GuardianAnnouncementRepository implements GuardianAnnouncementRepositoryInterface
{
    public function getAnnouncements(StudentProfile $student, ?string $category = null, ?bool $isRead = null): array
    {
        $query = Announcement::where('is_active', true)
            ->where(function ($q) use ($student) {
                $q->where('target_audience', 'all')
                    ->orWhere('target_audience', 'students')
                    ->orWhere('target_audience', 'guardians')
                    ->orWhere(function ($subQ) use ($student) {
                        $subQ->where('target_audience', 'class')
                            ->where('target_class_id', $student->class_id);
                    })
                    ->orWhere(function ($subQ) use ($student) {
                        $subQ->where('target_audience', 'grade')
                            ->where('target_grade_id', $student->grade_id);
                    });
            });

        if ($category) {
            $query->where('category', $category);
        }

        // TODO: Implement read tracking for guardians
        // if ($isRead !== null) {
        //     $query->whereHas/whereDoesntHave based on read status
        // }

        $announcements = $query->orderBy('created_at', 'desc')->get();

        return $announcements->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => Str::limit($announcement->content, 150),
                'category' => $announcement->category ?? 'general',
                'priority' => $announcement->priority ?? 'normal',
                'date' => $announcement->created_at->format('Y-m-d'),
                'is_read' => false, // TODO: Implement read tracking
                'created_at' => $announcement->created_at->toISOString(),
            ];
        })->toArray();
    }

    public function getAnnouncementDetail(string $announcementId): array
    {
        $announcement = Announcement::findOrFail($announcementId);

        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'category' => $announcement->category ?? 'general',
            'priority' => $announcement->priority ?? 'normal',
            'date' => $announcement->created_at->format('Y-m-d'),
            'attachment' => $announcement->attachment_path 
                ? asset($announcement->attachment_path) 
                : null,
            'is_read' => false, // TODO: Implement read tracking
            'created_at' => $announcement->created_at->toISOString(),
            'updated_at' => $announcement->updated_at->toISOString(),
        ];
    }

    public function markAsRead(string $announcementId, string $guardianId): bool
    {
        // TODO: Implement read tracking table for guardians
        // For now, return true as placeholder
        return true;
    }

    public function markAllAsRead(string $guardianId): bool
    {
        // TODO: Implement read tracking table for guardians
        // For now, return true as placeholder
        return true;
    }
}
