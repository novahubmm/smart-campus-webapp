<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'announcements:publish-scheduled {--dry-run : Show what would be published without actually publishing}';

    /**
     * The console command description.
     */
    protected $description = 'Publish scheduled announcements that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        // Find announcements that should be published
        $scheduledAnnouncements = Announcement::where('is_published', false)
            ->where('status', true)
            ->where('publish_date', '<=', now())
            ->whereNotNull('publish_date')
            ->get();

        if ($scheduledAnnouncements->isEmpty()) {
            $this->info('No scheduled announcements to publish.');
            return 0;
        }

        $this->info("Found {$scheduledAnnouncements->count()} scheduled announcement(s) to publish.");

        foreach ($scheduledAnnouncements as $announcement) {
            $this->line("- {$announcement->title} (scheduled for {$announcement->publish_date->format('Y-m-d H:i')})");
            
            if (!$dryRun) {
                // Publish the announcement
                $announcement->update(['is_published' => true]);
                
                // Send notifications to target roles
                $this->sendNotifications($announcement);
                
                Log::info("Published scheduled announcement: {$announcement->title}", [
                    'announcement_id' => $announcement->id,
                    'publish_date' => $announcement->publish_date,
                ]);
            }
        }

        if ($dryRun) {
            $this->warn('DRY RUN: No announcements were actually published. Remove --dry-run to publish.');
        } else {
            $this->info("Successfully published {$scheduledAnnouncements->count()} announcement(s).");
        }

        return 0;
    }

    /**
     * Send notifications for the published announcement
     */
    private function sendNotifications(Announcement $announcement): void
    {
        if (!$announcement->target_roles) {
            return;
        }

        try {
            // Send notifications to teachers
            if (in_array('teacher', $announcement->target_roles)) {
                $this->sendTeacherNotifications($announcement);
            }

            // Send notifications to staff
            if (in_array('staff', $announcement->target_roles)) {
                $this->sendStaffNotifications($announcement);
            }

            // TODO: Add guardian notifications when implemented
            // if (in_array('guardian', $announcement->target_roles)) {
            //     $this->sendGuardianNotifications($announcement);
            // }

        } catch (\Exception $e) {
            Log::error('Failed to send notifications for scheduled announcement', [
                'announcement_id' => $announcement->id,
                'error' => $e->getMessage(),
            ]);
            $this->error("Failed to send notifications for: {$announcement->title}");
        }
    }

    /**
     * Send notifications to all teachers
     */
    private function sendTeacherNotifications(Announcement $announcement): void
    {
        $teachers = User::role('teacher')
            ->where('is_active', true)
            ->get();

        foreach ($teachers as $teacher) {
            \App\Models\Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\AnnouncementNotification',
                'notifiable_type' => get_class($teacher),
                'notifiable_id' => $teacher->id,
                'data' => [
                    'title' => $announcement->title,
                    'message' => strip_tags($announcement->content),
                    'announcement_id' => $announcement->id,
                    'priority' => $announcement->priority ?? 'medium',
                ],
                'read_at' => null,
            ]);
        }

        $this->line("  → Sent notifications to {$teachers->count()} teachers");
    }

    /**
     * Send notifications to all staff members
     */
    private function sendStaffNotifications(Announcement $announcement): void
    {
        $staffMembers = User::role('staff')
            ->where('is_active', true)
            ->get();

        foreach ($staffMembers as $staff) {
            \App\Models\Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\AnnouncementNotification',
                'notifiable_type' => get_class($staff),
                'notifiable_id' => $staff->id,
                'data' => [
                    'title' => $announcement->title,
                    'message' => strip_tags($announcement->content),
                    'announcement_id' => $announcement->id,
                    'priority' => $announcement->priority ?? 'medium',
                ],
                'read_at' => null,
            ]);
        }

        $this->line("  → Sent notifications to {$staffMembers->count()} staff members");
    }
}