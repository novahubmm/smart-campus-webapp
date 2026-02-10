#!/usr/bin/env php
<?php

/**
 * Monthly Fee Setup Script
 * 
 * This script sets up the monthly fee generation system:
 * 1. Seeds fee types and structures
 * 2. Generates fees for current month
 * 3. Tests the Guardian API
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentProfile;
use Illuminate\Support\Facades\Artisan;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         Monthly Fee Generation Setup Script               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Step 1: Seed fee structures
echo "📋 Step 1: Setting up fee structures...\n";
echo "─────────────────────────────────────────────────────────────\n";
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\FeeStructureSeeder']);
echo Artisan::output();

// Step 2: Generate monthly fees
echo "\n💰 Step 2: Generating monthly fees for all students...\n";
echo "─────────────────────────────────────────────────────────────\n";
Artisan::call('fees:generate-monthly');
echo Artisan::output();

// Step 3: Get a sample student for testing
echo "\n🧪 Step 3: Testing API endpoints...\n";
echo "─────────────────────────────────────────────────────────────\n";

$student = StudentProfile::with(['user', 'guardians'])
    ->whereHas('guardians')
    ->first();

if ($student) {
    $guardian = $student->guardians->first();
    
    echo "✅ Sample student found:\n";
    echo "   Student: {$student->user->name} (ID: {$student->id})\n";
    echo "   Guardian: {$guardian->user->name} (ID: {$guardian->id})\n";
    echo "\n";
    
    echo "📝 Test the API with these endpoints:\n";
    echo "─────────────────────────────────────────────────────────────\n";
    echo "\n";
    
    $baseUrl = config('app.url');
    
    echo "1️⃣  Get All Fees:\n";
    echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/fees?per_page=10\n";
    echo "   Authorization: Bearer {access_token}\n";
    echo "\n";
    
    echo "2️⃣  Get Pending Fees:\n";
    echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/fees/pending\n";
    echo "   Authorization: Bearer {access_token}\n";
    echo "\n";
    
    echo "3️⃣  Get Payment Summary:\n";
    echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/fees/summary?year=2026\n";
    echo "   Authorization: Bearer {access_token}\n";
    echo "\n";
    
    echo "📌 To get access token, login as guardian:\n";
    echo "   POST {$baseUrl}/api/v1/auth/login\n";
    echo "   Body: {\"phone\": \"{$guardian->user->phone}\", \"password\": \"your_password\"}\n";
    echo "\n";
} else {
    echo "⚠️  No students with guardians found.\n";
    echo "   Please create students and assign guardians first.\n";
    echo "\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    Setup Complete! ✨                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📚 Additional Commands:\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "• Generate fees for specific month:\n";
echo "  php artisan fees:generate-monthly --month=2026-03\n";
echo "\n";
echo "• Generate fees for specific student:\n";
echo "  php artisan fees:generate-monthly --student={student_id}\n";
echo "\n";
echo "• Force regenerate (overwrite existing):\n";
echo "  php artisan fees:generate-monthly --force\n";
echo "\n";
echo "• Mark overdue fees:\n";
echo "  php artisan fees:mark-overdue\n";
echo "\n";
echo "• View scheduled tasks:\n";
echo "  php artisan schedule:list\n";
echo "\n";

echo "⏰ Scheduled Tasks (runs automatically):\n";
echo "─────────────────────────────────────────────────────────────\n";
echo "• Monthly fee generation: 1st of every month at 1:00 AM\n";
echo "• Mark overdue fees: Daily at 2:00 AM\n";
echo "\n";
echo "💡 To run scheduler in development:\n";
echo "   php artisan schedule:work\n";
echo "\n";
echo "💡 In production, add to crontab:\n";
echo "   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1\n";
echo "\n";
