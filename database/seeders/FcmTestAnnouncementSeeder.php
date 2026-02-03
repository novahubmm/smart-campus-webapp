<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FcmTestAnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating test announcements for FCM notifications...');

        // Ensure we have test users with FCM tokens
        $this->createTestUsersWithFcmTokens();

        // Create different types of test announcements
        $this->createTestAnnouncements();

        $this->command->info('✅ FCM test announcements created successfully!');
        $this->command->info('📱 Check your browser console for FCM token registration');
        $this->command->info('🔔 Notifications should appear in real-time when announcements are created');
    }

    /**
     * Create test users with mock FCM tokens
     */
    private function createTestUsersWithFcmTokens(): void
    {
        $this->command->info('👥 Setting up test users with FCM tokens...');

        // Create test staff users
        $staffUsers = User::role('staff')->take(3)->get();
        if ($staffUsers->count() < 3) {
            for ($i = $staffUsers->count(); $i < 3; $i++) {
                $user = User::factory()->create([
                    'name' => "Test Staff User " . ($i + 1),
                    'email' => "staff" . ($i + 1) . "@smartcampus.test",
                    'is_active' => true,
                ]);
                $user->assignRole('staff');
                $staffUsers->push($user);
            }
        }

        // Create test teacher users
        $teacherUsers = User::role('teacher')->take(2)->get();
        if ($teacherUsers->count() < 2) {
            for ($i = $teacherUsers->count(); $i < 2; $i++) {
                $user = User::factory()->create([
                    'name' => "Test Teacher User " . ($i + 1),
                    'email' => "teacher" . ($i + 1) . "@smartcampus.test",
                    'is_active' => true,
                ]);
                $user->assignRole('teacher');
                $teacherUsers->push($user);
            }
        }

        // Add mock FCM tokens to test users
        $mockTokens = [
            'fcm_token_staff_1_' . uniqid(),
            'fcm_token_staff_2_' . uniqid(),
            'fcm_token_staff_3_' . uniqid(),
            'fcm_token_teacher_1_' . uniqid(),
            'fcm_token_teacher_2_' . uniqid(),
        ];

        $allUsers = $staffUsers->concat($teacherUsers);
        foreach ($allUsers as $index => $user) {
            if (isset($mockTokens[$index])) {
                $user->update(['fcm_token' => $mockTokens[$index]]);
                $this->command->info("  ✓ Added FCM token to {$user->name}");
            }
        }
    }

    /**
     * Create various test announcements
     */
    private function createTestAnnouncements(): void
    {
        $this->command->info('📢 Creating test announcements...');

        // 1. Staff-only announcement
        $staffAnnouncement = Announcement::factory()->forStaff()->create([
            'title' => 'FCM Test: Staff Meeting Tomorrow',
            'content' => 'This is a test announcement for staff members only. Please check if you receive the FCM notification in real-time.',
        ]);
        $this->command->info("  ✓ Created staff announcement: {$staffAnnouncement->title}");

        // 2. Teacher-only announcement
        $teacherAnnouncement = Announcement::factory()->forTeachers()->create([
            'title' => 'FCM Test: New Teaching Guidelines',
            'content' => 'This is a test announcement for teachers only. FCM notifications should be sent immediately.',
        ]);
        $this->command->info("  ✓ Created teacher announcement: {$teacherAnnouncement->title}");

        // 3. All staff announcement (both staff and teachers)
        $allAnnouncement = Announcement::factory()->forAll()->create([
            'title' => 'FCM Test: School Holiday Notice',
            'content' => 'This is a test announcement for all staff and teachers. Everyone should receive FCM notifications.',
        ]);
        $this->command->info("  ✓ Created all-staff announcement: {$allAnnouncement->title}");

        // 4. Urgent announcement
        $urgentAnnouncement = Announcement::factory()->forStaff()->urgent()->create([
            'title' => 'FCM Test: URGENT - Emergency Drill',
            'content' => 'This is an urgent test announcement. FCM notifications should have high priority.',
        ]);
        $this->command->info("  ✓ Created urgent announcement: {$urgentAnnouncement->title}");

        // 5. High priority announcement
        $highPriorityAnnouncement = Announcement::factory()->forAll()->highPriority()->create([
            'title' => 'FCM Test: Important System Update',
            'content' => 'This is a high priority test announcement for system updates.',
        ]);
        $this->command->info("  ✓ Created high priority announcement: {$highPriorityAnnouncement->title}");

        // Trigger notifications manually for testing
        $this->triggerTestNotifications([
            $staffAnnouncement,
            $teacherAnnouncement,
            $allAnnouncement,
            $urgentAnnouncement,
            $highPriorityAnnouncement,
        ]);
    }

    /**
     * Manually trigger notifications for testing
     */
    private function triggerTestNotifications(array $announcements): void
    {
        $this->command->info('🔔 Triggering FCM notifications...');

        $firebaseService = new FirebaseService();

        foreach ($announcements as $announcement) {
            // Get target users based on announcement target roles
            $targetUsers = collect();

            if (in_array('staff', $announcement->target_roles)) {
                $targetUsers = $targetUsers->concat(User::role('staff')->where('is_active', true)->get());
            }

            if (in_array('teacher', $announcement->target_roles)) {
                $targetUsers = $targetUsers->concat(User::role('teacher')->where('is_active', true)->get());
            }

            $fcmTokens = $targetUsers->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

            if (!empty($fcmTokens)) {
                $title = $announcement->title;
                $body = \Illuminate\Support\Str::limit(strip_tags($announcement->content), 100);

                $results = $firebaseService->sendToMultipleTokens(
                    $fcmTokens,
                    $title,
                    $body,
                    [
                        'announcement_id' => $announcement->id,
                        'type' => 'announcement',
                        'priority' => $announcement->priority,
                    ]
                );

                $this->command->info("  📱 Sent FCM to {$results['success']} users, {$results['failed']} failed for: {$title}");

                // Also create database notifications
                foreach ($targetUsers as $user) {
                    \App\Models\Notification::create([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'type' => 'App\\Notifications\\AnnouncementNotification',
                        'notifiable_type' => get_class($user),
                        'notifiable_id' => $user->id,
                        'data' => [
                            'title' => $announcement->title,
                            'message' => strip_tags($announcement->content),
                            'announcement_id' => $announcement->id,
                            'priority' => $announcement->priority ?? 'medium',
                            'type' => 'announcement',
                        ],
                        'read_at' => null,
                    ]);
                }

                $this->command->info("  💾 Created database notifications for {$targetUsers->count()} users");
            } else {
                $this->command->warn("  ⚠️  No FCM tokens found for announcement: {$announcement->title}");
            }

            // Small delay between notifications
            sleep(1);
        }
    }
}