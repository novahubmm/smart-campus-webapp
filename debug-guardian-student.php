<?php

/**
 * Debug script to test guardian-student authorization
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;

echo "\n========================================\n";
echo "Debug Guardian-Student Authorization\n";
echo "========================================\n\n";

// Test with guardian1@smartcampusedu.com
$email = 'guardian1@smartcampusedu.com';
$studentId = '3a48862e-ed0e-4991-b2c7-5c4953ed7227';

echo "Testing:\n";
echo "  Guardian Email: $email\n";
echo "  Student ID: $studentId\n\n";

// Step 1: Find user
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found with email: $email\n";
    exit(1);
}

echo "✅ User found:\n";
echo "  - User ID: {$user->id}\n";
echo "  - Name: {$user->name}\n";
echo "  - Email: {$user->email}\n\n";

// Step 2: Get guardian profile
$guardianProfile = $user->guardianProfile;

if (!$guardianProfile) {
    echo "❌ Guardian profile not found for user\n";
    exit(1);
}

echo "✅ Guardian Profile found:\n";
echo "  - Guardian ID: {$guardianProfile->id}\n\n";

// Step 3: Get all students
$allStudents = $guardianProfile->students;

echo "✅ Guardian has {$allStudents->count()} student(s):\n";
foreach ($allStudents as $student) {
    echo "  - ID: {$student->id}\n";
    echo "    Name: {$student->user->name}\n";
    echo "    Grade: {$student->grade->name}\n";
    echo "    Class: {$student->classModel->name}\n\n";
}

// Step 4: Test the exact query from PaymentController
echo "Testing authorization query (same as PaymentController):\n";
echo "─────────────────────────────────────────\n";

$authorizedStudent = $guardianProfile->students()
    ->where('student_profiles.id', $studentId)
    ->with(['user', 'grade', 'classModel'])
    ->first();

if ($authorizedStudent) {
    echo "✅ AUTHORIZATION SUCCESS!\n\n";
    echo "Student Details:\n";
    echo "  - ID: {$authorizedStudent->id}\n";
    echo "  - Name: {$authorizedStudent->user->name}\n";
    echo "  - Grade: {$authorizedStudent->grade->name}\n";
    echo "  - Class: {$authorizedStudent->classModel->name}\n\n";
    
    echo "✅ The API should work with these credentials!\n\n";
} else {
    echo "❌ AUTHORIZATION FAILED!\n\n";
    echo "Possible issues:\n";
    echo "  1. Student ID mismatch\n";
    echo "  2. Guardian-student relationship not properly linked\n";
    echo "  3. Database inconsistency\n\n";
    
    // Debug: Check if student exists at all
    $studentExists = StudentProfile::find($studentId);
    if ($studentExists) {
        echo "  ℹ️  Student exists in database\n";
        echo "  ℹ️  But not linked to this guardian\n\n";
    } else {
        echo "  ❌ Student does not exist in database\n\n";
    }
}

// Step 5: Show the SQL query
echo "SQL Query being executed:\n";
echo "─────────────────────────────────────────\n";
$query = $guardianProfile->students()
    ->where('student_profiles.id', $studentId)
    ->with(['user', 'grade', 'classModel'])
    ->toSql();
echo $query . "\n\n";

echo "Query Bindings:\n";
$bindings = $guardianProfile->students()
    ->where('student_profiles.id', $studentId)
    ->getBindings();
print_r($bindings);

echo "\n========================================\n\n";
