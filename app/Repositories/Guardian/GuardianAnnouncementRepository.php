<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianAnnouncementRepositoryInterface;
use App\Models\Announcement;
use App\Models\GuardianAnnouncementInteraction;
use App\Models\StudentProfile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GuardianAnnouncementRepository implements GuardianAnnouncementRepositoryInterface
{
    public function getAnnouncements(StudentProfile $student, ?string $category = null, ?bool $isRead = null, ?bool $isPinned = null, string $guardianId = null): array
    {
        $query = Announcement::where('status', true)
            ->where('is_published', true)
            ->whereDate('publish_date', '<=', now())
            ->where(function ($q) use ($student) {
                // Check if target_roles is null (all users) or contains 'parent', 'guardian', or 'student'
                $q->whereNull('target_roles')
                    ->orWhereJsonContains('target_roles', 'parent')
                    ->orWhereJsonContains('target_roles', 'guardian')
                    ->orWhereJsonContains('target_roles', 'student');
            })
            ->where(function ($q) use ($student) {
                // Check if target_grades is null (all grades) or contains 'all' or student's grade
                $q->whereNull('target_grades')
                    ->orWhereJsonContains('target_grades', 'all')
                    ->orWhereJsonContains('target_grades', $student->grade_id);
            })
            ->with('announcementType');

        if ($category) {
            // Filter by announcement type slug
            $query->whereHas('announcementType', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Get announcements with interaction data
        $announcements = $query->with(['interactions' => function ($q) use ($guardianId) {
            if ($guardianId) {
                $q->where('guardian_id', $guardianId);
            }
        }])->get();

        // Filter by read status if specified
        if ($isRead !== null && $guardianId) {
            $announcements = $announcements->filter(function ($announcement) use ($isRead, $guardianId) {
                $interaction = $announcement->interactions->first();
                return $interaction ? $interaction->is_read === $isRead : !$isRead;
            });
        }

        // Filter by pinned status if specified
        if ($isPinned !== null && $guardianId) {
            $announcements = $announcements->filter(function ($announcement) use ($isPinned, $guardianId) {
                $interaction = $announcement->interactions->first();
                return $interaction ? $interaction->is_pinned === $isPinned : !$isPinned;
            });
        }

        // Sort: pinned first (by pinned_at desc), then by created_at desc
        $announcements = $announcements->sort(function ($a, $b) use ($guardianId) {
            $aInteraction = $a->interactions->first();
            $bInteraction = $b->interactions->first();
            
            $aIsPinned = $aInteraction && $aInteraction->is_pinned;
            $bIsPinned = $bInteraction && $bInteraction->is_pinned;
            
            if ($aIsPinned && !$bIsPinned) return -1;
            if (!$aIsPinned && $bIsPinned) return 1;
            
            if ($aIsPinned && $bIsPinned) {
                return $bInteraction->pinned_at <=> $aInteraction->pinned_at;
            }
            
            return $b->created_at <=> $a->created_at;
        });

        return $announcements->values()->map(function ($announcement) use ($guardianId) {
            $interaction = $announcement->interactions->first();
            
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => Str::limit($announcement->content, 150),
                'category' => $announcement->announcementType?->slug ?? $announcement->type ?? 'general',
                'priority' => $announcement->priority ?? 'normal',
                'date' => $announcement->created_at->format('Y-m-d'),
                'is_read' => $interaction ? $interaction->is_read : false,
                'is_pinned' => $interaction ? $interaction->is_pinned : false,
                'pinned_at' => $interaction && $interaction->pinned_at ? $interaction->pinned_at->toISOString() : null,
                'created_at' => $announcement->created_at->toISOString(),
            ];
        })->toArray();
    }

    public function getAnnouncementDetail(string $announcementId, ?string $guardianId = null): array
    {
        $announcement = Announcement::where('id', $announcementId)
            ->where('status', true)
            ->where('is_published', true)
            ->with(['announcementType', 'interactions' => function ($q) use ($guardianId) {
                if ($guardianId) {
                    $q->where('guardian_id', $guardianId);
                }
            }])
            ->firstOrFail();

        $interaction = $announcement->interactions->first();

        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'category' => $announcement->announcementType?->slug ?? $announcement->type ?? 'general',
            'priority' => $announcement->priority ?? 'normal',
            'date' => $announcement->created_at->format('Y-m-d'),
            'attachment' => $announcement->attachment 
                ? asset('storage/' . $announcement->attachment) 
                : null,
            'is_read' => $interaction ? $interaction->is_read : false,
            'is_pinned' => $interaction ? $interaction->is_pinned : false,
            'pinned_at' => $interaction && $interaction->pinned_at ? $interaction->pinned_at->toISOString() : null,
            'created_at' => $announcement->created_at->toISOString(),
            'updated_at' => $announcement->updated_at->toISOString(),
        ];
    }

    public function markAsRead(string $announcementId, string $guardianId): array
    {
        $interaction = GuardianAnnouncementInteraction::updateOrCreate(
            [
                'guardian_id' => $guardianId,
                'announcement_id' => $announcementId,
            ],
            [
                'is_read' => true,
                'read_at' => now(),
            ]
        );

        return [
            'announcement_id' => $announcementId,
            'is_read' => true,
            'read_at' => $interaction->read_at->toISOString(),
        ];
    }
    
    public function markAsUnread(string $announcementId, string $guardianId): array
    {
        $interaction = GuardianAnnouncementInteraction::updateOrCreate(
            [
                'guardian_id' => $guardianId,
                'announcement_id' => $announcementId,
            ],
            [
                'is_read' => false,
                'read_at' => null,
            ]
        );

        return [
            'announcement_id' => $announcementId,
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function markAllAsRead(string $guardianId): bool
    {
        // Get all announcements for this guardian
        $announcements = Announcement::where('status', true)
            ->where('is_published', true)
            ->whereDate('publish_date', '<=', now())
            ->pluck('id');

        foreach ($announcements as $announcementId) {
            GuardianAnnouncementInteraction::updateOrCreate(
                [
                    'guardian_id' => $guardianId,
                    'announcement_id' => $announcementId,
                ],
                [
                    'is_read' => true,
                    'read_at' => now(),
                ]
            );
        }

        return true;
    }
    
    public function pinAnnouncement(string $announcementId, string $guardianId): array
    {
        $interaction = GuardianAnnouncementInteraction::updateOrCreate(
            [
                'guardian_id' => $guardianId,
                'announcement_id' => $announcementId,
            ],
            [
                'is_pinned' => true,
                'pinned_at' => now(),
            ]
        );

        return [
            'announcement_id' => $announcementId,
            'is_pinned' => true,
            'pinned_at' => $interaction->pinned_at->toISOString(),
        ];
    }
    
    public function unpinAnnouncement(string $announcementId, string $guardianId): array
    {
        $interaction = GuardianAnnouncementInteraction::updateOrCreate(
            [
                'guardian_id' => $guardianId,
                'announcement_id' => $announcementId,
            ],
            [
                'is_pinned' => false,
                'pinned_at' => null,
            ]
        );

        return [
            'announcement_id' => $announcementId,
            'is_pinned' => false,
            'pinned_at' => null,
        ];
    }
    
    public function getAnnouncementsByCalendar(StudentProfile $student, int $year, int $month, string $guardianId): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $announcements = Announcement::where('status', true)
            ->where('is_published', true)
            ->whereBetween('publish_date', [$startDate, $endDate])
            ->where(function ($q) use ($student) {
                $q->whereNull('target_roles')
                    ->orWhereJsonContains('target_roles', 'parent')
                    ->orWhereJsonContains('target_roles', 'guardian')
                    ->orWhereJsonContains('target_roles', 'student');
            })
            ->where(function ($q) use ($student) {
                $q->whereNull('target_grades')
                    ->orWhereJsonContains('target_grades', 'all')
                    ->orWhereJsonContains('target_grades', $student->grade_id);
            })
            ->with(['announcementType', 'interactions' => function ($q) use ($guardianId) {
                $q->where('guardian_id', $guardianId);
            }])
            ->orderBy('publish_date')
            ->get();

        $announcementsByDate = [];
        $totalAnnouncements = 0;

        foreach ($announcements as $announcement) {
            $date = $announcement->publish_date->format('Y-m-d');
            $interaction = $announcement->interactions->first();

            if (!isset($announcementsByDate[$date])) {
                $announcementsByDate[$date] = [];
            }

            $announcementsByDate[$date][] = [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'category' => $announcement->announcementType?->slug ?? $announcement->type ?? 'general',
                'priority' => $announcement->priority ?? 'normal',
                'is_read' => $interaction ? $interaction->is_read : false,
            ];

            $totalAnnouncements++;
        }

        return [
            'year' => $year,
            'month' => $month,
            'announcements_by_date' => $announcementsByDate,
            'summary' => [
                'total_days_with_announcements' => count($announcementsByDate),
                'total_announcements' => $totalAnnouncements,
            ],
        ];
    }
}
