<?php

/**
 * Timetable API Test Script
 * Tests the Guardian Timetable API endpoints
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\GuardianProfile;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = config('app.url');
$guardianBaseUrl = "$baseUrl/api/v1/guardian";

// Colors for CLI output
$colors = [
    'green' => "\033[0;32m",
    'red' => "\033[0;31m",
    'yellow' => "\033[1;33m",
    'blue' => "\033[0;34m",
    'reset' => "\033[0m",
];

// Test counters
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

// Helper functions
function printHeader($title) {
    global $colors;
    echo "\n{$colors['blue']}========================================{$colors['reset']}\n";
    echo "{$colors['blue']}$title{$colors['reset']}\n";
    echo "{$colors['blue']}========================================{$colors['reset']}\n\n";
}

function printResult($passed, $message) {
    global $colors, $totalTests, $passedTests, $failedTests;
    $totalTests++;
    
    if ($passed) {
        echo "{$colors['green']}✓ PASS{$colors['reset']}: $message\n";
        $passedTests++;
    } else {
        echo "{$colors['red']}✗ FAIL{$colors['reset']}: $message\n";
        $failedTests++;
    }
}

function printSummary() {
    global $colors, $totalTests, $passedTests, $failedTests;
    
    echo "\n{$colors['blue']}========================================{$colors['reset']}\n";
    echo "{$colors['blue']}TEST SUMMARY{$colors['reset']}\n";
    echo "{$colors['blue']}========================================{$colors['reset']}\n";
    echo "Total Tests: $totalTests\n";
    echo "{$colors['green']}Passed: $passedTests{$colors['reset']}\n";
    echo "{$colors['red']}Failed: $failedTests{$colors['reset']}\n";
    
    if ($failedTests === 0) {
        echo "\n{$colors['green']}🎉 All tests passed!{$colors['reset']}\n\n";
    } else {
        echo "\n{$colors['red']}❌ Some tests failed{$colors['reset']}\n\n";
    }
}

function makeRequest($method, $url, $headers = [], $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'body' => json_decode($response, true),
        'code' => $httpCode,
    ];
}

// Step 0: Get test data
printHeader('STEP 0: Preparing Test Data');

try {
    // Find a guardian with students and phone number
    $guardian = GuardianProfile::whereHas('students')
        ->whereHas('user', function($q) {
            $q->whereNotNull('phone')->where('phone', '!=', '');
        })
        ->with('user')
        ->first();
    
    if (!$guardian) {
        // Try to find any guardian with students
        $guardian = GuardianProfile::whereHas('students')->with('user')->first();
        
        if (!$guardian) {
            echo "{$colors['red']}✗ No guardian found with students. Please seed the database first.{$colors['reset']}\n";
            exit(1);
        }
        
        // Update guardian with a test phone number if missing
        if (empty($guardian->user->phone)) {
            $guardian->user->phone = '09123456789';
            $guardian->user->password = bcrypt('password123');
            $guardian->user->save();
            echo "{$colors['yellow']}⚠ Updated guardian phone number and password for testing{$colors['reset']}\n";
        }
    } else {
        // Ensure password is set correctly
        $guardian->user->password = bcrypt('password123');
        $guardian->user->save();
    }
    
    $student = $guardian->students()->with(['classModel', 'grade'])->first();
    
    if (!$student) {
        echo "{$colors['red']}✗ No student found for guardian. Please seed the database first.{$colors['reset']}\n";
        exit(1);
    }
    
    echo "{$colors['green']}✓ Found test guardian: {$guardian->user->name} (Phone: {$guardian->user->phone}){$colors['reset']}\n";
    echo "{$colors['green']}✓ Found test student: {$student->user->name} (ID: {$student->id}){$colors['reset']}\n";
    
} catch (\Exception $e) {
    echo "{$colors['red']}✗ Error preparing test data: {$e->getMessage()}{$colors['reset']}\n";
    exit(1);
}

// Step 1: Login
printHeader('STEP 1: Login');

echo "Logging in as guardian...\n";
$loginResponse = makeRequest('POST', "$guardianBaseUrl/auth/login", [
    'Content-Type: application/json',
], [
    'login' => $guardian->user->phone,
    'password' => 'password123',
]);

echo json_encode($loginResponse['body'], JSON_PRETTY_PRINT) . "\n";

if (!isset($loginResponse['body']['data']['token']) && !isset($loginResponse['body']['data']['access_token'])) {
    echo "{$colors['red']}❌ Login failed. Cannot proceed with tests.{$colors['reset']}\n";
    exit(1);
}

$token = $loginResponse['body']['data']['token'] ?? $loginResponse['body']['data']['access_token'];
$studentId = $student->id;

printResult(true, 'Login successful');
echo "{$colors['green']}Student ID: $studentId{$colors['reset']}\n";

// Step 2: Test Weekly Timetable (Old Route)
printHeader('STEP 2: Get Weekly Timetable (Old Route)');

echo "Testing: GET /guardian/timetable?student_id=$studentId\n";
$timetableResponse = makeRequest('GET', "$guardianBaseUrl/timetable?student_id=$studentId", [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
]);

echo json_encode($timetableResponse['body'], JSON_PRETTY_PRINT) . "\n";

$success = $timetableResponse['body']['success'] ?? false;
printResult($success, 'Get weekly timetable (old route)');

if ($success) {
    $data = $timetableResponse['body']['data'];
    
    // Validate data structure
    printResult(!empty($data['student_name']), "Student name present: " . ($data['student_name'] ?? 'N/A'));
    printResult(!empty($data['grade']), "Grade present: " . ($data['grade'] ?? 'N/A'));
    printResult(!empty($data['week_start_date']), "Week start date present: " . ($data['week_start_date'] ?? 'N/A'));
    printResult(!empty($data['week_end_date']), "Week end date present: " . ($data['week_end_date'] ?? 'N/A'));
    
    // Check schedule days
    $mondayCount = count($data['schedule']['Monday'] ?? []);
    $tuesdayCount = count($data['schedule']['Tuesday'] ?? []);
    
    echo "{$colors['yellow']}Monday classes: $mondayCount{$colors['reset']}\n";
    echo "{$colors['yellow']}Tuesday classes: $tuesdayCount{$colors['reset']}\n";
    
    // Check break times
    $breakCount = count($data['break_times'] ?? []);
    printResult($breakCount > 0, "Break times present: $breakCount breaks");
    
    // Validate first period structure
    if ($mondayCount > 0) {
        $firstPeriod = $data['schedule']['Monday'][0];
        echo "\n{$colors['yellow']}First Monday Period:{$colors['reset']}\n";
        echo json_encode($firstPeriod, JSON_PRETTY_PRINT) . "\n";
        
        printResult(isset($firstPeriod['id']), 'Period has ID');
        printResult(isset($firstPeriod['subject']), 'Period has subject');
        printResult(isset($firstPeriod['teacher']), 'Period has teacher');
        printResult(isset($firstPeriod['start_time']) && isset($firstPeriod['end_time']), 'Period has time');
        printResult(isset($firstPeriod['status']), 'Period has status');
        printResult(isset($firstPeriod['teacher_phone']), 'Period has teacher phone');
        printResult(isset($firstPeriod['teacher_email']), 'Period has teacher email');
    }
}

// Step 3: Test Weekly Timetable (New RESTful Route)
printHeader('STEP 3: Get Weekly Timetable (New RESTful Route)');

echo "Testing: GET /guardian/students/$studentId/timetable\n";
$timetableNewResponse = makeRequest('GET', "$guardianBaseUrl/students/$studentId/timetable", [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
]);

echo json_encode($timetableNewResponse['body'], JSON_PRETTY_PRINT) . "\n";

$success = $timetableNewResponse['body']['success'] ?? false;
printResult($success, 'Get weekly timetable (new RESTful route)');

// Step 4: Test Weekly Timetable with Week Start Date
printHeader('STEP 4: Get Weekly Timetable with Week Start Date');

$weekStartDate = '2026-02-10';
echo "Testing: GET /guardian/students/$studentId/timetable?week_start_date=$weekStartDate\n";
$timetableWeekResponse = makeRequest('GET', "$guardianBaseUrl/students/$studentId/timetable?week_start_date=$weekStartDate", [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
]);

echo json_encode($timetableWeekResponse['body'], JSON_PRETTY_PRINT) . "\n";

$success = $timetableWeekResponse['body']['success'] ?? false;
printResult($success, 'Get weekly timetable with week_start_date parameter');

if ($success) {
    $returnedWeekStart = $timetableWeekResponse['body']['data']['week_start_date'] ?? '';
    printResult($returnedWeekStart === $weekStartDate, "Week start date matches requested date (Expected: $weekStartDate, Got: $returnedWeekStart)");
}

// Step 5: Test Day Timetable
printHeader('STEP 5: Get Day Timetable');

echo "Testing: GET /guardian/timetable/Monday?student_id=$studentId\n";
$dayResponse = makeRequest('GET', "$guardianBaseUrl/timetable/Monday?student_id=$studentId", [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
]);

echo json_encode($dayResponse['body'], JSON_PRETTY_PRINT) . "\n";

$success = $dayResponse['body']['success'] ?? false;
printResult($success, 'Get day timetable');

// Step 6: Test Invalid Student ID
printHeader('STEP 6: Test Error Handling - Invalid Student ID');

$invalidStudentId = '00000000-0000-0000-0000-000000000000';
echo "Testing: GET /guardian/students/$invalidStudentId/timetable\n";
$errorResponse = makeRequest('GET', "$guardianBaseUrl/students/$invalidStudentId/timetable", [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
]);

echo json_encode($errorResponse['body'], JSON_PRETTY_PRINT) . "\n";

$success = $errorResponse['body']['success'] ?? false;
printResult(!$success, 'Invalid student ID returns error');

// Step 7: Test Without Authentication
printHeader('STEP 7: Test Error Handling - No Authentication');

echo "Testing: GET /guardian/students/$studentId/timetable (without token)\n";
$unauthResponse = makeRequest('GET', "$guardianBaseUrl/students/$studentId/timetable", [
    'Content-Type: application/json',
]);

echo json_encode($unauthResponse['body'], JSON_PRETTY_PRINT) . "\n";

$message = strtolower($unauthResponse['body']['message'] ?? '');
$isUnauthorized = str_contains($message, 'unauthenticated') || str_contains($message, 'unauthorized');
printResult($isUnauthorized, 'Unauthenticated request returns error');

// Step 8: Validate Myanmar Language Support
printHeader('STEP 8: Validate Myanmar Language Support');

if (isset($timetableResponse['body']['data']['schedule']['Monday'][0])) {
    $firstPeriod = $timetableResponse['body']['data']['schedule']['Monday'][0];
    
    printResult(isset($firstPeriod['subject_mm']), 'Myanmar subject name present');
    printResult(isset($firstPeriod['teacher_mm']), 'Myanmar teacher name present');
    printResult(isset($firstPeriod['room_mm']), 'Myanmar room name present');
    
    // Check break times Myanmar
    if (isset($timetableResponse['body']['data']['break_times'][0])) {
        $firstBreak = $timetableResponse['body']['data']['break_times'][0];
        printResult(isset($firstBreak['name_mm']), 'Myanmar break name present');
    }
}

// Step 9: Validate Class Status Types
printHeader('STEP 9: Validate Class Status Types');

if (isset($timetableResponse['body']['data']['schedule']['Monday'][0])) {
    $status = $timetableResponse['body']['data']['schedule']['Monday'][0]['status'] ?? '';
    $validStatuses = ['normal', 'cancelled', 'substitute', 'swapped'];
    
    printResult(in_array($status, $validStatuses), "Valid status type: $status");
}

// Step 10: Validate Total Classes Count
printHeader('STEP 10: Validate Total Classes Count');

if (isset($timetableResponse['body']['data'])) {
    $data = $timetableResponse['body']['data'];
    $totalClasses = $data['total_classes_this_week'] ?? 0;
    
    // Calculate actual total
    $actualTotal = 0;
    foreach ($data['schedule'] as $day => $periods) {
        $actualTotal += count($periods);
    }
    
    printResult($totalClasses === $actualTotal, "Total classes count matches (Expected: $actualTotal, Got: $totalClasses)");
}

// Print final summary
printSummary();
