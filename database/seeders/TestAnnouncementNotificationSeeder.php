<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestAnnouncementNotificationSeeder extends Seeder
{
    /**
     * Seed test announcement notifications for teachers
     */
    public function run(): void
    {
        // Get all teachers
        $teachers = User::role('teacher')->where('is_active', true)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No active teachers found. Skipping notification seeding.');
            return;
        }

        // Sample announcement data
        $announcements = [
            [
                'title' => 'Staff Meeting Tomorrow',
                'message' => 'All teachers are required to attend the staff meeting at 9:00 AM in the conference room.',
                'priority' => 'high',
            ],
            [
                'title' => 'New Curriculum Guidelines',
                'message' => 'Updated curriculum guidelines for the new semester are now available in the staff portal.',
                'priority' => 'medium',
            ],
            [
                'title' => 'School Anniversary Celebration',
                'message' => 'Join us for the school anniversary celebration on Friday. All staff are invited.',
                'priority' => 'low',
            ],
        ];

        $this->command->info("Creating test notifications for {$teachers->count()} teachers...");

        foreach ($announcements as $index => $announcementData) {
            foreach ($teachers as $teacher) {
                Notification::create([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\AnnouncementNotification',
                    'notifiable_type' => get_class($teacher),
                    'notifiable_id' => $teacher->id,
                    'data' => [
                        'title' => $announcementData['title'],
                        'message' => $announcementData['message'],
                        'announcement_id' => null, // Test notification without actual announcement
                        'priority' => $announcementData['priority'],
                        'type' => 'general',
                    ],
                    'read_at' => $index === 2 ? now() : null, // Mark last one as read
                    'created_at' => now()->subHours(3 - $index),
                ]);
            }
        }

        $totalNotifications = count($announcements) * $teachers->count();
        $this->command->info("✓ Created {$totalNotifications} test notifications");
    }
}

