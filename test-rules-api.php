<?php

/**
 * Test Rules API
 * 
 * This script tests the Guardian Rules API endpoint
 * 
 * Usage: php test-rules-api.php
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

// Get Guardian token (you need to replace this with actual login)
printHeader("Step 1: Login as Guardian");
$loginResponse = makeRequest($baseUrl . '/guardian/auth/login', 'POST', [
    'email' => 'konyeinchan@smartcampusedu.com',
    'password' => 'password',
    'device_name' => 'test_device'
]);

if ($loginResponse['code'] === 200 && isset($loginResponse['body']['data']['token'])) {
    $token = $loginResponse['body']['data']['token'];
    printSuccess("Login successful");
    printInfo("Token: " . substr($token, 0, 20) . "...");
} else {
    printError("Login failed. Please update credentials in the script.");
    printInfo("Response: " . json_encode($loginResponse['body'], JSON_PRETTY_PRINT));
    exit(1);
}

// Test 1: Get All Rules
printHeader("Test 1: GET /guardian/school/rules");
$response = makeRequest($baseUrl . '/guardian/school/rules', 'GET', null, $token);

if ($response['code'] === 200) {
    printSuccess("API call successful (HTTP {$response['code']})");
    
    $data = $response['body']['data'] ?? [];
    
    // Validate response structure
    if (isset($data['categories']) && isset($data['total_categories']) && isset($data['total_rules'])) {
        printSuccess("Response structure is correct");
        
        echo "\n📊 Summary:\n";
        echo "   - Total Categories: {$data['total_categories']}\n";
        echo "   - Total Rules: {$data['total_rules']}\n";
        echo "   - Last Updated: {$data['last_updated']}\n";
        
        echo "\n📚 Categories:\n";
        foreach ($data['categories'] as $index => $category) {
            echo "\n   " . ($index + 1) . ". {$category['icon']} {$category['title']} ({$category['title_mm']})\n";
            echo "      - ID: {$category['id']}\n";
            echo "      - Description: {$category['description']}\n";
            echo "      - Rules Count: {$category['rules_count']}\n";
            echo "      - Priority: {$category['priority']}\n";
            echo "      - Icon Color: {$category['icon_color']}\n";
            echo "      - Background Color: {$category['icon_background_color']}\n";
            
            if (!empty($category['rules'])) {
                echo "      - Rules:\n";
                foreach ($category['rules'] as $ruleIndex => $rule) {
                    echo "         " . ($ruleIndex + 1) . ". {$rule['title']} ({$rule['title_mm']})\n";
                    echo "            Severity: {$rule['severity']}, Order: {$rule['order']}\n";
                }
            }
        }
        
        // Validate Myanmar language support
        $hasMyanmarSupport = true;
        foreach ($data['categories'] as $category) {
            if (empty($category['title_mm']) || empty($category['description_mm'])) {
                $hasMyanmarSupport = false;
                break;
            }
            foreach ($category['rules'] as $rule) {
                if (empty($rule['title_mm']) || empty($rule['description_mm'])) {
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
        
        // Validate severity levels
        $validSeverities = ['low', 'medium', 'high'];
        $allSeveritiesValid = true;
        foreach ($data['categories'] as $category) {
            foreach ($category['rules'] as $rule) {
                if (!in_array($rule['severity'], $validSeverities)) {
                    $allSeveritiesValid = false;
                    printError("Invalid severity level: {$rule['severity']}");
                }
            }
        }
        
        if ($allSeveritiesValid) {
            printSuccess("All severity levels are valid");
        }
        
    } else {
        printError("Response structure is incorrect");
        echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
    }
} else {
    printError("API call failed (HTTP {$response['code']})");
    echo "Response: " . ($response['raw'] ?? 'No response') . "\n";
}

// Test 2: Verify API matches mobile spec
printHeader("Test 2: Verify API Response Matches Mobile Spec");

$expectedFields = [
    'categories' => ['id', 'title', 'title_mm', 'description', 'description_mm', 'icon', 'icon_color', 'icon_background_color', 'rules_count', 'priority', 'is_active', 'rules'],
    'rules' => ['id', 'title', 'title_mm', 'description', 'description_mm', 'severity', 'order'],
];

$allFieldsPresent = true;
$data = $response['body']['data'] ?? [];

if (!empty($data['categories'])) {
    $category = $data['categories'][0];
    
    foreach ($expectedFields['categories'] as $field) {
        if (!array_key_exists($field, $category)) {
            printError("Missing field in category: {$field}");
            $allFieldsPresent = false;
        }
    }
    
    if (!empty($category['rules'])) {
        $rule = $category['rules'][0];
        
        foreach ($expectedFields['rules'] as $field) {
            if (!array_key_exists($field, $rule)) {
                printError("Missing field in rule: {$field}");
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
    printInfo("The Rules API is ready for mobile integration");
} else {
    printError("Some tests failed");
    printInfo("Please review the errors above");
}

echo "\n";
