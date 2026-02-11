<?php

/**
 * Simple Subject Performance API Test
 */

$baseUrl = 'http://localhost:8088/api/v1';
$studentId = 'b0ae26d7-0cb6-42db-9e90-4a057d27c50b';

// Get token from login
$loginData = json_encode([
    'email' => 'parent1@example.com',
    'password' => 'password',
]);

$ch = curl_init("$baseUrl/guardian/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response (HTTP $httpCode):\n";
echo $response . "\n\n";

if ($httpCode !== 200) {
    echo "Login failed. Exiting.\n";
    exit(1);
}

$loginResponse = json_decode($response, true);
$token = $loginResponse['data']['token'] ?? null;

if (!$token) {
    echo "No token received. Exiting.\n";
    exit(1);
}

echo "Token: " . substr($token, 0, 30) . "...\n\n";

// Test Subject Performance API
$url = "$baseUrl/guardian/students/$studentId/profile/subject-performance";
echo "Testing: $url\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Subject Performance Response (HTTP $httpCode):\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
