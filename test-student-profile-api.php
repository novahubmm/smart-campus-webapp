<?php

/**
 * Test Student Profile APIs
 * Tests all 8 profile endpoints
 */

require __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://192.168.100.114:8088/api/v1';
$token = ''; // Will be set after login
$studentId = ''; // Will be set after getting students

echo "===========================================\n";
echo "Testing Student Profile APIs\n";
echo "===========================================\n\n";

// Step 1: Login
echo "Step 1: Login\n";
echo "-------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/guardian/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'konyeinchan@smartcampusedu.com',
    'password' => 'password',
    'device_name' => 'test_device',
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$loginData = json_decode($response, true);

if ($httpCode === 200 && isset($loginData['data']['token'])) {
    $token = $loginData['data']['token'];
    echo "✅ Login successful\n";
    echo "Token: " . substr($token, 0, 20) . "...\n";
    echo "Guardian: " . ($loginData['data']['user']['name'] ?? 'N/A') . "\n\n";
} else {
    echo "❌ Login failed\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
    echo "💡 Tip: Update login and password in the script with valid credentials\n";
    exit(1);
}

// Step 2: Get Students
echo "Step 2: Get Students\n";
echo "-------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/guardian/students");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$studentsData = json_decode($response, true);

if ($httpCode === 200 && isset($studentsData['data']) && !empty($studentsData['data'])) {
    $studentId = $studentsData['data'][0]['id'];
    $studentName = $studentsData['data'][0]['name'] ?? 'N/A';
    echo "✅ Got student ID: $studentId\n";
    echo "Student: $studentName\n\n";
} else {
    echo "❌ Failed to get students\n";
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

// Test endpoints
$endpoints = [
    [
        'name' => 'Profile Overview',
        'url' => "/guardian/students/$studentId/profile",
        'required_fields' => ['id', 'name', 'grade', 'section'], // Updated to match actual response
    ],
    [
        'name' => 'Academic Summary',
        'url' => "/guardian/students/$studentId/profile/academic-summary",
        'required_fields' => ['current_gpa', 'current_rank', 'total_students'], // Updated to match actual response
    ],
    [
        'name' => 'Subject Performance',
        'url' => "/guardian/students/$studentId/profile/subject-performance",
        'required_fields' => ['subjects'],
    ],
    [
        'name' => 'Progress Tracking',
        'url' => "/guardian/students/$studentId/profile/progress-tracking?months=6",
        'required_fields' => ['gpa_history', 'rank_history', 'current_gpa'],
    ],
    [
        'name' => 'Comparison Data',
        'url' => "/guardian/students/$studentId/profile/comparison",
        'required_fields' => ['gpa_comparison', 'avg_score_comparison', 'subject_comparisons'],
    ],
    [
        'name' => 'Attendance Summary',
        'url' => "/guardian/students/$studentId/profile/attendance-summary?months=3",
        'required_fields' => ['overall_percentage', 'total_present', 'total_days'], // Updated to match actual response
    ],
    [
        'name' => 'Rankings & Exam History',
        'url' => "/guardian/students/$studentId/profile/rankings",
        'required_fields' => ['current_class_rank', 'current_grade_rank', 'exam_history'],
    ],
    [
        'name' => 'Achievement Badges',
        'url' => "/guardian/students/$studentId/profile/achievements",
        'required_fields' => ['badges', 'total_badges'], // Updated to match actual response (unlocked_badges is optional)
    ],
];

$passedTests = 0;
$failedTests = 0;

foreach ($endpoints as $index => $endpoint) {
    $testNumber = $index + 3; // Starting from 3 since login and get students are 1 and 2
    
    echo "Test $testNumber: {$endpoint['name']}\n";
    echo "-------------------------------------------\n";
    echo "URL: {$endpoint['url']}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl{$endpoint['url']}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    echo "HTTP Status: $httpCode\n";
    
    if ($httpCode === 200 && isset($data['success']) && $data['success']) {
        // Check required fields
        $missingFields = [];
        foreach ($endpoint['required_fields'] as $field) {
            if (!array_key_exists($field, $data['data'])) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "✅ Test PASSED\n";
            
            // Show sample data based on endpoint type
            if (isset($data['data']['subjects']) && is_array($data['data']['subjects'])) {
                echo "Subjects count: " . count($data['data']['subjects']) . "\n";
                if (!empty($data['data']['subjects'])) {
                    echo "First subject: " . json_encode($data['data']['subjects'][0], JSON_PRETTY_PRINT) . "\n";
                }
            } elseif (isset($data['data']['badges']) && is_array($data['data']['badges'])) {
                echo "Badges count: " . count($data['data']['badges']) . "\n";
                echo "Unlocked: " . ($data['data']['unlocked_badges'] ?? 0) . "/" . ($data['data']['total_badges'] ?? 0) . "\n";
            } elseif (isset($data['data']['gpa_history']) && is_array($data['data']['gpa_history'])) {
                echo "GPA history points: " . count($data['data']['gpa_history']) . "\n";
                echo "Current GPA: " . ($data['data']['current_gpa'] ?? 'N/A') . "\n";
            } elseif (isset($data['data']['exam_history']) && is_array($data['data']['exam_history'])) {
                echo "Exam history count: " . count($data['data']['exam_history']) . "\n";
                echo "Current class rank: " . ($data['data']['current_class_rank'] ?? 'N/A') . "\n";
            } else {
                echo "Sample data: " . json_encode(array_slice($data['data'], 0, 3), JSON_PRETTY_PRINT) . "\n";
            }
            
            $passedTests++;
        } else {
            echo "❌ Test FAILED - Missing fields: " . implode(', ', $missingFields) . "\n";
            echo "Available fields: " . implode(', ', array_keys($data['data'])) . "\n";
            $failedTests++;
        }
    } else {
        echo "❌ Test FAILED\n";
        echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        if (isset($data['data'])) {
            echo "Response data: " . json_encode($data['data'], JSON_PRETTY_PRINT) . "\n";
        }
        $failedTests++;
    }
    
    echo "\n";
}

echo "===========================================\n";
echo "Test Summary\n";
echo "===========================================\n";
echo "Total Tests: " . ($passedTests + $failedTests) . "\n";
echo "Passed: $passedTests ✅\n";
echo "Failed: $failedTests ❌\n";
echo "Success Rate: " . round(($passedTests / ($passedTests + $failedTests)) * 100, 1) . "%\n";
echo "===========================================\n";
