<?php

/**
 * Test Class Info API
 * 
 * This script tests the Guardian Class Info API endpoint
 * 
 * Usage: php test-class-info-api.php
 */

require __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://192.168.100.114:8088/api/v1';

// ANSI color codes
$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

function printHeader($text) {
    global $BLUE, $NC;
    echo "\n{$BLUE}========================================{$NC}\n";
    echo "{$BLUE}{$text}{$NC}\n";
    echo "{$BLUE}========================================{$NC}\n\n";
}

function printSuccess($text) {
    global $GREEN, $NC;
    echo "{$GREEN}✅ {$text}{$NC}\n";
}

function printError($text) {
    global $RED, $NC;
    echo "{$RED}❌ {$text}{$NC}\n";
}

function printInfo($text) {
    global $YELLOW, $NC;
    echo "{$YELLOW}ℹ️  {$text}{$NC}\n";
}

// Get Guardian token
printHeader("Step 1: Login as Guardian");
$loginResponse = makeRequest($baseUrl . '/guardian/auth/login', 'POST', [
    'email' => 'konyeinchan@smartcampusedu.com',
    'password' => 'password',
    'device_name' => 'test_device'
]);

if ($loginResponse['code'] === 200 && isset($loginResponse['body']['data']['token'])) {
    $token = $loginResponse['body']['data']['token'];
    printSuccess("Login successful");
} else {
    printError("Login failed. Please update credentials in the script.");
    echo "Response: " . json_encode($loginResponse['body'], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

// Get student list
printHeader("Step 2: Get Student List");
$studentsResponse = makeRequest($baseUrl . '/guardian/students', 'GET', null, $token);

if ($studentsResponse['code'] === 200 && !empty($studentsResponse['body']['data'])) {
    $students = $studentsResponse['body']['data'];
    printSuccess("Found " . count($students) . " student(s)");
    
    $studentId = $students[0]['id'];
    $studentName = $students[0]['name'];
    printInfo("Testing with student: {$studentName} (ID: {$studentId})");
} else {
    printError("Failed to get students");
    echo "Response: " . json_encode($studentsResponse['body'], JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

// Test 1: Get Class Info (RESTful route)
printHeader("Test 1: GET /guardian/students/{student_id}/class-info");
$response = makeRequest($baseUrl . "/guardian/students/{$studentId}/class-info", 'GET', null, $token);

if ($response['code'] === 200) {
    printSuccess("API call successful (HTTP {$response['code']})");
    
    $data = $response['body']['data'] ?? [];
    
    echo "\n📚 Class Information:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    // Validate response structure
    $expectedFields = ['class_id', 'class_name', 'grade', 'section'];
    $hasAllFields = true;
    
    foreach ($expectedFields as $field) {
        if (!isset($data[$field])) {
            printError("Missing field: {$field}");
            $hasAllFields = false;
        }
    }
    
    if ($hasAllFields) {
        printSuccess("All expected fields are present");
    }
} else {
    printError("API call failed (HTTP {$response['code']})");
    echo "Response: " . ($response['raw'] ?? 'No response') . "\n";
}

// Test 2: Get Class Info (Alternative route)
printHeader("Test 2: GET /guardian/students/{student_id}/class");
$response2 = makeRequest($baseUrl . "/guardian/students/{$studentId}/class", 'GET', null, $token);

if ($response2['code'] === 200) {
    printSuccess("Alternative route works (HTTP {$response2['code']})");
} else {
    printError("Alternative route failed (HTTP {$response2['code']})");
}

// Test 3: Get Class Info (Old route with query param)
printHeader("Test 3: GET /guardian/class-info?student_id={student_id}");
$response3 = makeRequest($baseUrl . "/guardian/class-info?student_id={$studentId}", 'GET', null, $token);

if ($response3['code'] === 200) {
    printSuccess("Old route still works (HTTP {$response3['code']})");
} else {
    printError("Old route failed (HTTP {$response3['code']})");
}

// Test 4: Get Detailed Class Info
printHeader("Test 4: GET /guardian/students/{student_id}/class/details");
$response4 = makeRequest($baseUrl . "/guardian/students/{$studentId}/class/details", 'GET', null, $token);

if ($response4['code'] === 200) {
    printSuccess("Detailed class info works (HTTP {$response4['code']})");
    
    $data = $response4['body']['data'] ?? [];
    echo "\n📊 Detailed Class Information:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    printError("Detailed class info failed (HTTP {$response4['code']})");
}

// Test 5: Get Class Teachers
printHeader("Test 5: GET /guardian/students/{student_id}/class/teachers");
$response5 = makeRequest($baseUrl . "/guardian/students/{$studentId}/class/teachers", 'GET', null, $token);

if ($response5['code'] === 200) {
    printSuccess("Class teachers endpoint works (HTTP {$response5['code']})");
    
    $data = $response5['body']['data'] ?? [];
    echo "\n👨‍🏫 Class Teachers:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    printError("Class teachers endpoint failed (HTTP {$response5['code']})");
}

// Test 6: Get Class Statistics
printHeader("Test 6: GET /guardian/students/{student_id}/class/statistics");
$response6 = makeRequest($baseUrl . "/guardian/students/{$studentId}/class/statistics", 'GET', null, $token);

if ($response6['code'] === 200) {
    printSuccess("Class statistics endpoint works (HTTP {$response6['code']})");
    
    $data = $response6['body']['data'] ?? [];
    echo "\n📈 Class Statistics:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    printError("Class statistics endpoint failed (HTTP {$response6['code']})");
}

printHeader("Test Summary");
$allPassed = $response['code'] === 200 && 
             $response2['code'] === 200 && 
             $response3['code'] === 200;

if ($allPassed) {
    printSuccess("All tests passed! ✨");
    printInfo("The Class Info API is ready for mobile integration");
    echo "\n";
    printInfo("Available endpoints:");
    echo "   - GET /guardian/students/{student_id}/class-info (NEW)\n";
    echo "   - GET /guardian/students/{student_id}/class (NEW)\n";
    echo "   - GET /guardian/class-info?student_id={id} (OLD)\n";
    echo "   - GET /guardian/students/{student_id}/class/details\n";
    echo "   - GET /guardian/students/{student_id}/class/teachers\n";
    echo "   - GET /guardian/students/{student_id}/class/statistics\n";
} else {
    printError("Some tests failed");
    printInfo("Please review the errors above");
}

echo "\n";
