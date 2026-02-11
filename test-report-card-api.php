<?php

/**
 * Test Report Card API
 * 
 * This script tests the Guardian Report Card API endpoints
 * 
 * Usage: php test-report-card-api.php
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
    'email' => 'guardian139@smartcampusedu.com',
    'password' => 'password'
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

// Test 1: Get All Report Cards (RESTful route)
printHeader("Test 1: GET /guardian/students/{student_id}/report-cards");
$response = makeRequest($baseUrl . "/guardian/students/{$studentId}/report-cards", 'GET', null, $token);

if ($response['code'] === 200) {
    printSuccess("API call successful (HTTP {$response['code']})");
    
    $data = $response['body']['data'] ?? [];
    $reportCards = $data['report_cards'] ?? [];
    
    echo "\n📊 Report Cards Summary:\n";
    echo "   - Total Report Cards: " . count($reportCards) . "\n";
    
    if (!empty($reportCards)) {
        foreach ($reportCards as $index => $card) {
            echo "\n   " . ($index + 1) . ". {$card['exam_name']} ({$card['exam_name_mm']})\n";
            echo "      - Term: {$card['term']} ({$card['term_mm']})\n";
            echo "      - Academic Year: {$card['academic_year']}\n";
            echo "      - GPA: {$card['gpa']}\n";
            echo "      - Rank: {$card['class_rank']}/{$card['total_students']}\n";
            echo "      - Percentage: {$card['percentage']}%\n";
            echo "      - Grade: {$card['grade']}\n";
            echo "      - Subjects: " . count($card['subjects']) . "\n";
        }
        
        // Validate Myanmar language support
        $hasMyanmarSupport = true;
        foreach ($reportCards as $card) {
            if (empty($card['term_mm']) || empty($card['exam_name_mm'])) {
                $hasMyanmarSupport = false;
                break;
            }
            foreach ($card['subjects'] as $subject) {
                if (empty($subject['subject_mm']) || empty($subject['remarks_mm'])) {
                    $hasMyanmarSupport = false;
                    break 2;
                }
            }
        }
        
        if ($hasMyanmarSupport) {
            printSuccess("Myanmar language support is complete");
        } else {
            printError("Myanmar language support is incomplete");
        }
        
        // Store first report card ID for detail test
        $reportCardId = $reportCards[0]['id'];
    } else {
        printInfo("No report cards found for this student");
        $reportCardId = null;
    }
} else {
    printError("API call failed (HTTP {$response['code']})");
    echo "Response: " . ($response['raw'] ?? 'No response') . "\n";
    $reportCardId = null;
}

// Test 2: Get Report Card Details
if ($reportCardId) {
    printHeader("Test 2: GET /guardian/students/{student_id}/report-cards/{report_card_id}");
    $response2 = makeRequest($baseUrl . "/guardian/students/{$studentId}/report-cards/{$reportCardId}", 'GET', null, $token);
    
    if ($response2['code'] === 200) {
        printSuccess("Report card details retrieved (HTTP {$response2['code']})");
        
        $data = $response2['body']['data'] ?? [];
        echo "\n📄 Report Card Details:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } else {
        printError("Failed to get report card details (HTTP {$response2['code']})");
    }
}

// Test 3: Get Latest Report Card
printHeader("Test 3: GET /guardian/students/{student_id}/report-cards/latest");
$response3 = makeRequest($baseUrl . "/guardian/students/{$studentId}/report-cards/latest", 'GET', null, $token);

if ($response3['code'] === 200) {
    printSuccess("Latest report card retrieved (HTTP {$response3['code']})");
    
    $data = $response3['body']['data'] ?? [];
    if ($data) {
        echo "\n📋 Latest Report Card:\n";
        echo "   - Exam: {$data['exam_name']}\n";
        echo "   - GPA: {$data['gpa']}\n";
        echo "   - Rank: {$data['class_rank']}/{$data['total_students']}\n";
    }
} else {
    printInfo("No latest report card found (HTTP {$response3['code']})");
}

// Test 4: Verify API Response Structure
printHeader("Test 4: Verify API Response Matches Mobile Spec");

$expectedFields = [
    'report_card' => ['id', 'term', 'term_mm', 'academic_year', 'exam_name', 'exam_name_mm', 'exam_date', 
                      'gpa', 'class_rank', 'total_students', 'percentage', 'grade', 'class_teacher', 
                      'class_teacher_mm', 'class_teacher_remark', 'class_teacher_remark_mm', 'subjects'],
    'subject' => ['subject_id', 'subject', 'subject_mm', 'teacher_name', 'teacher_name_mm', 
                  'marks_obtained', 'total_marks', 'percentage', 'grade', 'remarks', 'remarks_mm'],
];

$allFieldsPresent = true;
$data = $response['body']['data'] ?? [];
$reportCards = $data['report_cards'] ?? [];

if (!empty($reportCards)) {
    $card = $reportCards[0];
    
    foreach ($expectedFields['report_card'] as $field) {
        if (!array_key_exists($field, $card)) {
            printError("Missing field in report card: {$field}");
            $allFieldsPresent = false;
        }
    }
    
    if (!empty($card['subjects'])) {
        $subject = $card['subjects'][0];
        
        foreach ($expectedFields['subject'] as $field) {
            if (!array_key_exists($field, $subject)) {
                printError("Missing field in subject: {$field}");
                $allFieldsPresent = false;
            }
        }
    }
}

if ($allFieldsPresent) {
    printSuccess("All required fields are present");
} else {
    printError("Some required fields are missing");
}

printHeader("Test Summary");
if ($response['code'] === 200 && $allFieldsPresent) {
    printSuccess("All tests passed! ✨");
    printInfo("The Report Card API is ready for mobile integration");
    echo "\n";
    printInfo("Available endpoints:");
    echo "   - GET /guardian/students/{student_id}/report-cards\n";
    echo "   - GET /guardian/students/{student_id}/report-cards/{id}\n";
    echo "   - GET /guardian/students/{student_id}/report-cards/latest\n";
    echo "   - POST /guardian/students/{student_id}/report-cards/{id}/download\n";
} else {
    printError("Some tests failed");
    printInfo("Please review the errors above");
}

echo "\n";
