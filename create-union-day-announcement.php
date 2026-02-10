<?php

/**
 * Create Union Day Myanmar 2026 Announcement
 * 
 * This script creates an announcement for Union Day Myanmar 2026
 * targeting Students, Guardians, Teachers, and Staff
 * 
 * Union Day: February 12, 2026
 * 
 * Usage: php create-union-day-announcement.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Announcement;
use App\Models\AnnouncementType;
use App\Models\User;
use App\Jobs\SendAnnouncementNotifications;
use Illuminate\Support\Str;

echo "🇲🇲 Creating Union Day Myanmar 2026 Announcement...\n\n";

// Get or create announcement type
$announcementType = AnnouncementType::where('slug', 'holiday')->first();
if (!$announcementType) {
    $announcementType = AnnouncementType::where('slug', 'general')->first();
}

if (!$announcementType) {
    echo "❌ Error: No announcement type found. Please create announcement types first.\n";
    exit(1);
}

// Get admin user as creator
$creator = User::role('admin')->first();
if (!$creator) {
    $creator = User::first();
}

if (!$creator) {
    echo "❌ Error: No users found in the system.\n";
    exit(1);
}

// Union Day details
$unionDayDate = '2026-02-12';
$publishDate = now(); // Publish immediately

// Create the announcement
$announcement = Announcement::create([
    'id' => Str::uuid(),
    'title' => 'Union Day Myanmar 2026 - School Holiday',
    'content' => '<div class="announcement-content">
        <h3>🇲🇲 Union Day Myanmar 2026</h3>
        <p><strong>Date:</strong> Thursday, February 12, 2026</p>
        <p><strong>Status:</strong> School Holiday</p>
        
        <p>Dear Students, Parents, Teachers, and Staff,</p>
        
        <p>We would like to inform you that our school will be <strong>closed on February 12, 2026</strong> in observance of <strong>Union Day</strong>, one of Myanmar\'s most significant national holidays.</p>
        
        <h4>About Union Day:</h4>
        <p>Union Day commemorates the historic Panglong Agreement signed on February 12, 1947, which laid the foundation for Myanmar\'s independence and unity among its diverse ethnic groups.</p>
        
        <h4>Important Information:</h4>
        <ul>
            <li>🏫 School will be closed for all students and staff</li>
            <li>📚 No classes or activities scheduled</li>
            <li>🔄 Regular classes will resume on Friday, February 13, 2026</li>
            <li>📝 Any homework or assignments due on this day will be extended to the next school day</li>
        </ul>
        
        <p>We wish everyone a meaningful Union Day celebration!</p>
        
        <p><em>Smart Campus Administration</em></p>
    </div>',
    'announcement_type_id' => $announcementType->id,
    'priority' => 'high',
    'target_roles' => ['student', 'guardian', 'teacher', 'staff'],
    'target_grades' => ['all'],
    'target_departments' => ['all'],
    'publish_date' => $publishDate,
    'is_published' => true,
    'status' => true,
    'created_by' => $creator->id,
]);

echo "✅ Announcement created successfully!\n";
echo "   ID: {$announcement->id}\n";
echo "   Title: {$announcement->title}\n";
echo "   Target Roles: " . implode(', ', $announcement->target_roles) . "\n";
echo "   Priority: {$announcement->priority}\n";
echo "   Published: " . ($announcement->is_published ? 'Yes' : 'No') . "\n\n";

// Send push notifications
echo "📱 Sending push notifications...\n";

try {
    SendAnnouncementNotifications::dispatchSync(
        $announcement,
        ['student', 'guardian', 'teacher', 'staff'],
        ['all'],
        ['all']
    );
    echo "✅ Push notifications sent successfully!\n\n";
} catch (\Exception $e) {
    echo "⚠️  Warning: Could not send push notifications: {$e->getMessage()}\n\n";
}

// Display statistics
try {
    $stats = [
        'teachers' => User::role('teacher')->where('is_active', true)->count(),
        'staff' => User::role('staff')->where('is_active', true)->count(),
    ];
    
    // Try to get student and guardian counts if models exist
    if (class_exists('\App\Models\Student')) {
        $stats['students'] = \App\Models\Student::where('is_active', true)->count();
    }
    if (class_exists('\App\Models\Guardian')) {
        $stats['guardians'] = \App\Models\Guardian::count();
    }

    echo "📊 Notification Statistics:\n";
    if (isset($stats['students'])) echo "   Students: {$stats['students']}\n";
    if (isset($stats['guardians'])) echo "   Guardians: {$stats['guardians']}\n";
    echo "   Teachers: {$stats['teachers']}\n";
    echo "   Staff: {$stats['staff']}\n";
    echo "   Total Recipients: " . array_sum($stats) . "\n\n";
} catch (\Exception $e) {
    echo "📊 Notification sent to all target roles\n\n";
}

echo "🎉 Union Day Myanmar 2026 announcement created and published!\n";
echo "🔔 All students, guardians, teachers, and staff have been notified.\n";
