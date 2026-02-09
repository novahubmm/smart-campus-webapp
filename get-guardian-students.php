<?php

/**
 * Helper script to find guardian-student relationships for testing
 * Usage: php get-guardian-students.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;

echo "\n========================================\n";
echo "Guardian-Student Relationships\n";
echo "========================================\n\n";

// Get all guardians with their students
$guardians = GuardianProfile::with(['user', 'students.user', 'students.grade', 'students.classModel'])
    ->get();

if ($guardians->isEmpty()) {
    echo "❌ No guardians found in the database.\n\n";
    echo "To create test data, you need to:\n";
    echo "1. Create a guardian user\n";
    echo "2. Create a guardian profile\n";
    echo "3. Link students to the guardian\n\n";
    exit(1);
}

echo "Found " . $guardians->count() . " guardian(s)\n\n";

foreach ($guardians as $guardian) {
    $user = $guardian->user;
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Guardian: " . ($user->name ?? 'N/A') . "\n";
    echo "Email: " . ($user->email ?? 'N/A') . "\n";
    echo "Phone: " . ($user->phone ?? 'N/A') . "\n";
    echo "Guardian ID: " . $guardian->id . "\n";
    echo "User ID: " . $guardian->user_id . "\n";
    echo "\n";
    
    $students = $guardian->students;
    
    if ($students->isEmpty()) {
        echo "⚠️  No students linked to this guardian\n";
    } else {
        echo "Students (" . $students->count() . "):\n";
        echo "─────────────────────────────────────────\n";
        
        foreach ($students as $student) {
            echo "  • Student ID: " . $student->id . "\n";
            echo "    Name: " . ($student->user->name ?? 'N/A') . "\n";
            echo "    Grade: " . ($student->grade->name ?? 'N/A') . "\n";
            echo "    Class: " . ($student->classModel->name ?? 'N/A') . "\n";
            echo "\n";
        }
    }
    
    echo "\n";
}

echo "========================================\n";
echo "Testing Instructions\n";
echo "========================================\n\n";

$firstGuardian = $guardians->first();
$firstStudent = $firstGuardian->students->first();

if ($firstGuardian && $firstStudent) {
    echo "To test the API:\n\n";
    echo "1. Login as guardian:\n";
    echo "   POST /api/v1/guardian/auth/login\n";
    echo "   {\n";
    echo "     \"identifier\": \"" . ($firstGuardian->user->email ?? 'guardian@example.com') . "\",\n";
    echo "     \"password\": \"password\"\n";
    echo "   }\n\n";
    
    echo "2. Use the access_token from login response\n\n";
    
    echo "3. Test fee structure endpoint:\n";
    echo "   GET /api/v1/guardian/students/" . $firstStudent->id . "/fees/structure\n";
    echo "   Authorization: Bearer {access_token}\n\n";
    
    echo "Example cURL command:\n";
    echo "─────────────────────────────────────────\n";
    echo "curl -X GET \"http://192.168.100.114:8088/api/v1/guardian/students/" . $firstStudent->id . "/fees/structure\" \\\n";
    echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
} else {
    echo "⚠️  No guardian-student relationship found.\n";
    echo "   Please create test data first.\n\n";
}

echo "========================================\n\n";
