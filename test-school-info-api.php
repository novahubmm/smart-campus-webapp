<?php

/**
 * Test School Info API
 * Tests the /api/v1/guardian/school-info endpoint
 */

require __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://192.168.100.114:8088/api/v1';

echo "===========================================\n";
echo "Testing School Info API\n";
echo "===========================================\n\n";

// Test 1: Get School Info (Public - No Auth Required)
echo "Test 1: GET /guardian/school-info (Public)\n";
echo "Note: This endpoint should work without authentication\n";
echo "-------------------------------------------\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/guardian/school-info");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response:\n";
$data = json_decode($response, true);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

if ($httpCode === 200 && isset($data['success']) && $data['success']) {
    echo "✅ Test 1 PASSED\n\n";
    
    // Validate response structure
    echo "Validating Response Structure:\n";
    echo "-------------------------------------------\n";
    
    $requiredFields = [
        'school_id',
        'school_name',
        'school_name_mm',
        'school_code',
        'logo_url',
        'established_year',
        'motto',
        'motto_mm',
        'contact',
        'about',
        'facilities',
        'statistics',
        'accreditations',
        'social_media',
    ];
    
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($data['data'][$field])) {
            $missingFields[] = $field;
        } else {
            echo "✅ $field: present\n";
        }
    }
    
    if (empty($missingFields)) {
        echo "\n✅ All required fields present\n\n";
    } else {
        echo "\n❌ Missing fields: " . implode(', ', $missingFields) . "\n\n";
    }
    
    // Validate nested structures
    echo "Validating Nested Structures:\n";
    echo "-------------------------------------------\n";
    
    // Contact Info
    $contactFields = ['phone', 'email', 'website', 'address', 'address_mm', 'office_hours', 'office_hours_mm'];
    echo "Contact Info:\n";
    foreach ($contactFields as $field) {
        if (isset($data['data']['contact'][$field])) {
            echo "  ✅ $field: " . $data['data']['contact'][$field] . "\n";
        } else {
            echo "  ❌ $field: missing\n";
        }
    }
    
    // About Info
    echo "\nAbout Info:\n";
    $aboutFields = ['description', 'description_mm', 'vision', 'vision_mm', 'mission', 'mission_mm', 'values', 'values_mm'];
    foreach ($aboutFields as $field) {
        if (isset($data['data']['about'][$field])) {
            if (is_array($data['data']['about'][$field])) {
                echo "  ✅ $field: [" . count($data['data']['about'][$field]) . " items]\n";
            } else {
                echo "  ✅ $field: present\n";
            }
        } else {
            echo "  ❌ $field: missing\n";
        }
    }
    
    // Facilities
    echo "\nFacilities:\n";
    if (isset($data['data']['facilities']) && is_array($data['data']['facilities'])) {
        echo "  ✅ Total facilities: " . count($data['data']['facilities']) . "\n";
        if (!empty($data['data']['facilities'])) {
            $facility = $data['data']['facilities'][0];
            echo "  Sample facility:\n";
            echo "    - ID: " . ($facility['id'] ?? 'N/A') . "\n";
            echo "    - Name: " . ($facility['name'] ?? 'N/A') . "\n";
            echo "    - Name (MM): " . ($facility['name_mm'] ?? 'N/A') . "\n";
            echo "    - Icon: " . ($facility['icon'] ?? 'N/A') . "\n";
            echo "    - Capacity: " . ($facility['capacity'] ?? 'N/A') . "\n";
        }
    } else {
        echo "  ❌ Facilities: missing or invalid\n";
    }
    
    // Statistics
    echo "\nStatistics:\n";
    $statsFields = ['total_students', 'total_teachers', 'total_staff', 'total_classes', 'student_teacher_ratio', 'pass_rate', 'average_attendance'];
    foreach ($statsFields as $field) {
        if (isset($data['data']['statistics'][$field])) {
            echo "  ✅ $field: " . $data['data']['statistics'][$field] . "\n";
        } else {
            echo "  ❌ $field: missing\n";
        }
    }
    
    // Accreditations
    echo "\nAccreditations:\n";
    if (isset($data['data']['accreditations']) && is_array($data['data']['accreditations'])) {
        echo "  ✅ Total accreditations: " . count($data['data']['accreditations']) . "\n";
    } else {
        echo "  ❌ Accreditations: missing or invalid\n";
    }
    
    // Social Media
    echo "\nSocial Media:\n";
    $socialFields = ['facebook', 'twitter', 'instagram', 'youtube'];
    foreach ($socialFields as $field) {
        if (isset($data['data']['social_media'][$field])) {
            echo "  ✅ $field: " . $data['data']['social_media'][$field] . "\n";
        } else {
            echo "  ⚠️  $field: optional (not present)\n";
        }
    }
    
} else {
    echo "❌ Test 1 FAILED\n";
    echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n\n";
}

echo "\n===========================================\n";
echo "Test Summary\n";
echo "===========================================\n";
echo "Endpoint: GET /api/v1/guardian/school-info\n";
echo "Status: " . ($httpCode === 200 ? '✅ PASSED' : '❌ FAILED') . "\n";
echo "HTTP Code: $httpCode\n";
echo "===========================================\n";
