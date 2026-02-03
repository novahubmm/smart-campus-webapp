<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestFcmNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test 
                            {--type=all : Type of notification (staff|teacher|all|mobile)}
                            {--priority=medium : Priority level (low|medium|high|urgent)}
                            {--count=1 : Number of test notifications to send}
                            {--token= : Specific FCM token for mobile testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test FCM notifications to staff and teachers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $priority = $this->option('priority');
        $count = (int) $this->option('count');
        $specificToken = $this->option('token');

        $this->info("🚀 Testing FCM notifications...");
        $this->info("Type: {$type}, Priority: {$priority}, Count: {$count}");

        $firebaseService = new FirebaseService();

        // Handle mobile-specific testing
        if ($type === 'mobile' || $specificToken) {
            return $this->handleMobileTest($firebaseService, $specificToken, $count);
        }

        for ($i = 1; $i <= $count; $i++) {
            $this->info("\n📢 Sending test notification {$i}/{$count}...");

            // Create test announcement
            $announcement = $this->createTestAnnouncement($type, $priority, $i);

            // Get target users
            $targetUsers = $this->getTargetUsers($type);

            if ($targetUsers->isEmpty()) {
                $this->error("❌ No users found for type: {$type}");
                continue;
            }

            // Create database notifications FIRST (before FCM)
            $this->createDatabaseNotifications($targetUsers, $announcement);

            // Get FCM tokens
            $fcmTokens = $targetUsers->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

            if (empty($fcmTokens)) {
                $this->warn("⚠️  No FCM tokens found. Users need to visit the site to register tokens.");
                
                // Add mock tokens for testing
                $this->addMockFcmTokens($targetUsers);
                $fcmTokens = $targetUsers->fresh()->whereNotNull('fcm_token')->pluck('fcm_token')->toArray();
            }

            // Send FCM notifications AFTER database notifications are created
            if (!empty($fcmTokens)) {
                $title = $announcement->title;
                $body = Str::limit(strip_tags($announcement->content), 100);

                $results = $firebaseService->sendToMultipleTokens(
                    $fcmTokens,
                    $title,
                    $body,
                    [
                        'announcement_id' => (string) $announcement->id,
                        'type' => 'announcement',
                        'priority' => $priority,
                    ]
                );

                $this->info("📱 FCM Results: {$results['success']} success, {$results['failed']} failed");
            }

            $this->info("✅ Test notification {$i} completed");

            if ($i < $count) {
                $this->info("⏳ Waiting 2 seconds before next notification...");
                sleep(2);
            }
        }

        $this->info("\n🎉 FCM test completed!");
        $this->info("📊 Check the logs and browser console for results");
    }

    /**
     * Create a test announcement
     */
    private function createTestAnnouncement(string $type, string $priority, int $number): Announcement
    {
        $targetRoles = match($type) {
            'staff' => ['staff'],
            'teacher' => ['teacher'],
            'all' => ['staff', 'teacher'],
            default => ['staff', 'teacher'],
        };

        $priorityEmoji = match($priority) {
            'urgent' => '🚨',
            'high' => '⚠️',
            'medium' => 'ℹ️',
            'low' => '📝',
            default => 'ℹ️',
        };

        return Announcement::factory()->create([
            'title' => "{$priorityEmoji} FCM Test #{$number} - " . ucfirst($type) . " Notification",
            'content' => "This is test notification #{$number} for {$type} with {$priority} priority. " .
                        "If you receive this as a push notification, FCM is working correctly! " .
                        "Time sent: " . now()->format('Y-m-d H:i:s'),
            'priority' => $priority,
            'target_roles' => $targetRoles,
            'is_published' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Get target users based on type
     */
    private function getTargetUsers(string $type)
    {
        return match($type) {
            'staff' => User::role('staff')->where('is_active', true)->get(),
            'teacher' => User::role('teacher')->where('is_active', true)->get(),
            'all' => User::role(['staff', 'teacher'])->where('is_active', true)->get(),
            default => User::role(['staff', 'teacher'])->where('is_active', true)->get(),
        };
    }

    /**
     * Add mock FCM tokens for testing
     */
    private function addMockFcmTokens($users): void
    {
        $this->info("🔧 Adding mock FCM tokens for testing...");

        foreach ($users as $user) {
            if (!$user->fcm_token) {
                $mockToken = 'mock_fcm_token_' . $user->id . '_' . uniqid();
                $user->update(['fcm_token' => $mockToken]);
                $this->info("  ✓ Added mock token to {$user->name}");
            }
        }
    }

    /**
     * Create database notifications
     */
    private function createDatabaseNotifications($users, Announcement $announcement): void
    {
        foreach ($users as $user) {
            \App\Models\Notification::create([
                'id' => Str::uuid(),
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

        $this->info("💾 Created database notifications for {$users->count()} users");
    }

    /**
     * Handle mobile-specific testing
     */
    private function handleMobileTest(FirebaseService $firebaseService, ?string $specificToken, int $count): int
    {
        $this->info("\n📱 Testing mobile FCM notifications...");

        if ($specificToken) {
            $this->info("🎯 Testing with specific token: " . substr($specificToken, 0, 20) . "...");
            
            for ($i = 1; $i <= $count; $i++) {
                $this->info("\n📢 Sending mobile test notification {$i}/{$count}...");
                
                $success = $firebaseService->sendMobileTestNotification($specificToken);
                
                if ($success) {
                    $this->info("✅ Mobile notification {$i} sent successfully");
                } else {
                    $this->error("❌ Mobile notification {$i} failed");
                }

                if ($i < $count) {
                    $this->info("⏳ Waiting 2 seconds before next notification...");
                    sleep(2);
                }
            }
        } else {
            // Get all users with FCM tokens for mobile testing
            $users = User::whereNotNull('fcm_token')->get();
            
            if ($users->isEmpty()) {
                $this->error("❌ No users with FCM tokens found for mobile testing");
                $this->info("💡 Tip: Use --token=YOUR_MOBILE_FCM_TOKEN to test with a specific mobile token");
                return 1;
            }

            $this->info("📱 Found {$users->count()} users with FCM tokens");

            foreach ($users as $user) {
                $this->info("\n📢 Testing mobile notification for {$user->name}...");
                
                $success = $firebaseService->sendMobileTestNotification($user->fcm_token);
                
                if ($success) {
                    $this->info("✅ Mobile notification sent to {$user->name}");
                } else {
                    $this->error("❌ Mobile notification failed for {$user->name}");
                }
                
                sleep(1); // Small delay between users
            }
        }

        $this->info("\n🎉 Mobile FCM test completed!");
        $this->info("📊 Check the logs for detailed results");
        $this->info("📱 The notification format sent was:");
        $this->info('   {"to": "device_token", "notification": {"title": "New Message", "body": "You have a new notification"}, "data": {"type": "announcement", "id": "123"}}');
        
        return 0;
    }
}