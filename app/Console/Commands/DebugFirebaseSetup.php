<?php

namespace App\Console\Commands;

use App\Services\FirebaseService;
use Illuminate\Console\Command;

class DebugFirebaseSetup extends Command
{
    protected $signature = 'firebase:debug';
    protected $description = 'Debug Firebase setup and configuration';

    public function handle()
    {
        $this->info('🔍 Debugging Firebase Setup...');

        // Check Firebase credentials file
        $credentialsPath = storage_path('app/firebase-credentials.json');
        if (file_exists($credentialsPath)) {
            $this->info('✅ Firebase credentials file exists');
        } else {
            $this->error('❌ Firebase credentials file missing');
            return 1;
        }

        // Check environment variables
        $this->info("\n📋 Environment Configuration:");
        $this->info("FIREBASE_PROJECT_ID: " . env('FIREBASE_PROJECT_ID', 'not set'));
        $this->info("VAPID_PUBLIC_KEY: " . (env('VAPID_PUBLIC_KEY') ? 'set' : 'not set'));
        $this->info("VAPID_PRIVATE_KEY: " . (env('VAPID_PRIVATE_KEY') ? 'set' : 'not set'));

        // Test Firebase service initialization
        $this->info("\n🚀 Testing Firebase Service...");
        try {
            $firebaseService = new FirebaseService();
            $this->info('✅ Firebase service initialized successfully');
        } catch (\Exception $e) {
            $this->error('❌ Firebase service initialization failed: ' . $e->getMessage());
            return 1;
        }

        // Check user FCM tokens
        $this->info("\n📱 FCM Token Status:");
        $totalUsers = \App\Models\User::count();
        $usersWithTokens = \App\Models\User::whereNotNull('fcm_token')->count();
        $mockTokens = \App\Models\User::where('fcm_token', 'LIKE', 'mock_%')->count();
        $realTokens = \App\Models\User::whereNotNull('fcm_token')->where('fcm_token', 'NOT LIKE', 'mock_%')->count();

        $this->info("Total users: {$totalUsers}");
        $this->info("Users with FCM tokens: {$usersWithTokens}");
        $this->info("Mock tokens: {$mockTokens}");
        $this->info("Real tokens: {$realTokens}");

        if ($realTokens === 0) {
            $this->warn("\n⚠️  No real FCM tokens found!");
            $this->info("To get real tokens:");
            $this->info("1. Visit your website in a browser");
            $this->info("2. Allow notifications when prompted");
            $this->info("3. Check browser console for 'FCM token received' message");
        }

        // Test mobile notification format
        $this->info("\n📲 Testing Mobile Notification Format...");
        try {
            $success = $firebaseService->sendMobileTestNotification('test_token_will_fail');
            if (!$success) {
                $this->info('✅ Mobile test method works (expected to fail with test token)');
            }
        } catch (\Exception $e) {
            $this->error('❌ Mobile test method error: ' . $e->getMessage());
        }

        $this->info("\n🎉 Firebase debug completed!");
        $this->info("\n💡 Next steps:");
        $this->info("1. Visit your website to register real FCM tokens");
        $this->info("2. Run: php artisan fcm:test --type=all");
        $this->info("3. Check browser notifications and console logs");

        return 0;
    }
}