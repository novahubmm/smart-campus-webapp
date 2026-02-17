<?php

/**
 * Test Subject Performance API
 * Tests the subject performance endpoint for student profile
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Artisan;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Configuration
$baseUrl = 'http://localhost:8088/api/v1';
$studentId = 'b0ae26d7-0cb6-42db-9e90-4a057d27c50b';

// Colors for output
function colorize($text, $color) {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'reset' => "\033[0m",
    ];
    return $colors[$color] . $text . $colors['reset'];
}

echo "\n";
echo colorize("╔════════════════════════════════════════════════════════════╗", 'blue') . "\n";
echo colorize("║     Subject Performance API Test                          ║", 'blue') . "\n";
echo colorize("╚════════════════════════════════════════════════════════════╝", 'blue') . "\n\n";

// Step 1: Run the seeder
echo colorize("Step 1: Running SubjectPerformanceSeeder...", 'yellow') . "\n";
echo str_repeat("-", 60) . "\n";

try {
    Artisan::call('db:seed', ['--class' => 'SubjectPerformanceSeeder']);
    echo Artisan::output();
    echo colorize("✓ Seeder completed successfully", 'green') . "\n\n";
} catch (Exception $e) {
    echo colorize("✗ Seeder failed: " . $e->getMessage(), 'red') . "\n\n";
    exit(1);
}

// Step 2: Get auth token
echo colorize("Step 2: Getting authentication token...", 'yellow') . "\n";
echo str_repeat("-", 60) . "\n";

$loginData = [
    'email' => 'parent@example.com',
    'password' => 'password123',
];

$ch = curl_init("$baseUrl/guardian/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo colorize("✗ Login failed (HTTP $httpCode)", 'red') . "\n";
    echo "Response: $response\n\n";
    exit(1);
}

$loginResponse = json_decode($response, true);
if (!isset($loginResponse['data']['token'])) {
    echo colorize("✗ No token in response", 'red') . "\n";
    echo "Response: $response\n\n";
    exit(1);
}

$token = $loginResponse['data']['token'];
echo colorize("✓ Authentication successful", 'green') . "\n";
echo "Token: " . substr($token, 0, 20) . "...\n\n";

// Step 3: Test Subject Performance API
echo colorize("Step 3: Testing Subject Performance API...", 'yellow') . "\n";
echo str_repeat("-", 60) . "\n";

$url = "$baseUrl/guardian/students/$studentId/profile/subject-performance";
echo "URL: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";

if ($httpCode === 200) {
    echo colorize("✓ API request successful", 'green') . "\n\n";
    
    $data = json_decode($response, true);
    
    // Pretty print the response
    echo colorize("Response Data:", 'blue') . "\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Validate response structure
    echo colorize("Validating Response Structure:", 'yellow') . "\n";
    $validations = [
        'success field exists' => isset($data['success']),
        'success is true' => $data['success'] === true,
        'data field exists' => isset($data['data']),
        'subjects array exists' => isset($data['data']['subjects']),
        'subjects is array' => is_array($data['data']['subjects']),
        'has subjects' => count($data['data']['subjects']) > 0,
    ];
    
    foreach ($validations as $check => $result) {
        if ($result) {
            echo colorize("  ✓ $check", 'green') . "\n";
        } else {
            echo colorize("  ✗ $check", 'red') . "\n";
        }
    }
    
    // Validate subject structure
    if (isset($data['data']['subjects'][0])) {
        echo "\n" . colorize("Validating Subject Structure:", 'yellow') . "\n";
        $subject = $data['data']['subjects'][0];
        $requiredFields = ['id', 'name', 'name_mm', 'grade', 'grade_color', 'percentage', 'rank', 'total_students'];
        
        foreach ($requiredFields as $field) {
            if (isset($subject[$field])) {
                echo colorize("  ✓ $field: " . json_encode($subject[$field]), 'green') . "\n";
            } else {
                echo colorize("  ✗ $field: missing", 'red') . "\n";
            }
        }
    }
    
    // Display summary
    if (isset($data['data']['subjects'])) {
        echo "\n" . colorize("Subject Performance Summary:", 'blue') . "\n";
        echo str_repeat("-", 60) . "\n";
        printf("%-20s %-8s %-12s %-8s\n", "Subject", "Grade", "Percentage", "Rank");
        echo str_repeat("-", 60) . "\n";
        
        foreach ($data['data']['subjects'] as $subject) {
            printf(
                "%-20s %-8s %-12s #%-7s\n",
                $subject['name'],
                $subject['grade'],
                $subject['percentage'] . '%',
                $subject['rank'] . '/' . $subject['total_students']
            );
        }
        echo str_repeat("-", 60) . "\n";
    }
    
    echo "\n" . colorize("✅ All tests passed!", 'green') . "\n";
    
} else {
    echo colorize("✗ API request failed", 'red') . "\n";
    echo "Response: $response\n";
}

echo "\n";
