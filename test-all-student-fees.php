#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\GuardianProfile;
use App\Models\User;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         Test All Student Fees API                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

$phone = $argv[1] ?? '09123456789';

$user = User::where('phone', $phone)->first();

if (!$user) {
    echo "❌ User not found with phone: {$phone}\n";
    exit(1);
}

$guardian = $user->guardianProfile;

if (!$guardian) {
    echo "❌ No guardian profile found\n";
    exit(1);
}

echo "👤 Guardian: {$user->name}\n";
echo "📱 Phone: {$user->phone}\n";
echo "\n";

$students = $guardian->students()->with(['user'])->get();

echo "📚 Testing API for {$students->count()} students:\n";
echo "─────────────────────────────────────────────────────────────\n";

$baseUrl = config('app.url');

foreach ($students as $index => $student) {
    echo "\n" . ($index + 1) . ". {$student->user->name} (ID: {$student->id})\n";
    echo "   API Endpoint:\n";
    echo "   GET {$baseUrl}/api/v1/guardian/students/{$student->id}/fees?per_page=10\n";
    echo "\n";
}

echo "─────────────────────────────────────────────────────────────\n";
echo "\n";
echo "💡 The API endpoint requires the student_id in the URL path.\n";
echo "   Each student has their own fees endpoint.\n";
echo "\n";
echo "📝 To get fees for ALL students, you need to:\n";
echo "   1. First get the list of students: GET /api/v1/guardian/students\n";
echo "   2. Then call fees endpoint for each student separately\n";
echo "\n";
echo "🔑 Example workflow:\n";
echo "   Step 1: GET {$baseUrl}/api/v1/guardian/students\n";
echo "   Step 2: For each student in response, call:\n";
echo "           GET {$baseUrl}/api/v1/guardian/students/{student_id}/fees\n";
echo "\n";
