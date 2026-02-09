<?php

/**
 * Test if a token is valid and belongs to the correct user
 * Usage: php test-token-auth.php "YOUR_TOKEN_HERE"
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PersonalAccessToken;
use App\Models\User;

echo "\n========================================\n";
echo "Token Authentication Test\n";
echo "========================================\n\n";

if ($argc < 2) {
    echo "Usage: php test-token-auth.php \"YOUR_TOKEN_HERE\"\n\n";
    echo "Example:\n";
    echo "  php test-token-auth.php \"1|abcdefghijklmnopqrstuvwxyz\"\n\n";
    exit(1);
}

$tokenString = $argv[1];

echo "Testing token: " . substr($tokenString, 0, 20) . "...\n\n";

// Parse token (format: ID|TOKEN)
$parts = explode('|', $tokenString, 2);

if (count($parts) !== 2) {
    echo "❌ Invalid token format!\n";
    echo "   Expected format: ID|TOKEN\n";
    echo "   Example: 1|abcdefghijklmnopqrstuvwxyz\n\n";
    exit(1);
}

[$id, $token] = $parts;

echo "Token ID: $id\n";
echo "Token Hash: " . substr(hash('sha256', $token), 0, 20) . "...\n\n";

// Find token in database
$accessToken = PersonalAccessToken::find($id);

if (!$accessToken) {
    echo "❌ Token not found in database!\n\n";
    echo "Possible reasons:\n";
    echo "  1. Token has been deleted\n";
    echo "  2. Token ID is incorrect\n";
    echo "  3. You need to login again\n\n";
    exit(1);
}

echo "✅ Token found in database\n\n";

// Verify token hash
$tokenHash = hash('sha256', $token);

if ($accessToken->token !== $tokenHash) {
    echo "❌ Token hash mismatch!\n";
    echo "   The token is invalid or has been tampered with\n\n";
    exit(1);
}

echo "✅ Token hash verified\n\n";

// Check if token is expired
if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
    echo "❌ Token has expired!\n";
    echo "   Expired at: {$accessToken->expires_at}\n";
    echo "   Please login again to get a new token\n\n";
    exit(1);
}

echo "✅ Token is not expired\n\n";

// Get user
$user = User::find($accessToken->tokenable_id);

if (!$user) {
    echo "❌ User not found!\n\n";
    exit(1);
}

echo "✅ User authenticated:\n";
echo "  - User ID: {$user->id}\n";
echo "  - Name: {$user->name}\n";
echo "  - Email: {$user->email}\n\n";

// Check if user has guardian profile
$guardianProfile = $user->guardianProfile;

if (!$guardianProfile) {
    echo "❌ User does not have a guardian profile!\n";
    echo "   This user cannot access guardian APIs\n\n";
    exit(1);
}

echo "✅ Guardian profile found:\n";
echo "  - Guardian ID: {$guardianProfile->id}\n\n";

// Get students
$students = $guardianProfile->students;

echo "✅ Guardian has {$students->count()} student(s):\n";
foreach ($students as $student) {
    echo "  - {$student->id} - {$student->user->name}\n";
}

echo "\n========================================\n";
echo "✅ TOKEN IS VALID!\n";
echo "========================================\n\n";

echo "You can use this token to access:\n";
echo "  - Guardian APIs\n";
echo "  - Student IDs: " . $students->pluck('id')->implode(', ') . "\n\n";

echo "Example API call:\n";
echo "─────────────────────────────────────────\n";
$firstStudent = $students->first();
if ($firstStudent) {
    echo "curl -X GET \"http://192.168.100.114:8088/api/v1/guardian/students/{$firstStudent->id}/fees/structure\" \\\n";
    echo "  -H \"Authorization: Bearer $tokenString\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
}

echo "========================================\n\n";
