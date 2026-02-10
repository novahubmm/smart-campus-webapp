#!/bin/bash

# Test Guardian Fees API
# This script tests the fees API endpoints

BASE_URL="http://localhost:8088/api/v1"

echo "╔════════════════════════════════════════════════════════════╗"
echo "║           Guardian Fees API Test Script                   ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Get a sample student with guardian
echo "📋 Getting sample student..."
STUDENT_DATA=$(php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$student = App\Models\StudentProfile::with(['user', 'guardians.user'])
    ->whereHas('guardians')
    ->first();

if (\$student && \$student->guardians->isNotEmpty()) {
    \$guardian = \$student->guardians->first();
    echo json_encode([
        'student_id' => \$student->id,
        'student_name' => \$student->user->name,
        'guardian_phone' => \$guardian->user->phone,
        'guardian_name' => \$guardian->user->name,
    ]);
}
")

if [ -z "$STUDENT_DATA" ]; then
    echo "❌ No students with guardians found"
    exit 1
fi

STUDENT_ID=$(echo $STUDENT_DATA | php -r "echo json_decode(file_get_contents('php://stdin'))->student_id;")
STUDENT_NAME=$(echo $STUDENT_DATA | php -r "echo json_decode(file_get_contents('php://stdin'))->student_name;")
GUARDIAN_PHONE=$(echo $STUDENT_DATA | php -r "echo json_decode(file_get_contents('php://stdin'))->guardian_phone;")
GUARDIAN_NAME=$(echo $STUDENT_DATA | php -r "echo json_decode(file_get_contents('php://stdin'))->guardian_name;")

echo "✅ Found student: $STUDENT_NAME (ID: $STUDENT_ID)"
echo "   Guardian: $GUARDIAN_NAME (Phone: $GUARDIAN_PHONE)"
echo ""

# Login as guardian
echo "🔐 Logging in as guardian..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d "{\"phone\":\"$GUARDIAN_PHONE\",\"password\":\"password\"}")

TOKEN=$(echo $LOGIN_RESPONSE | php -r "\$data = json_decode(file_get_contents('php://stdin'), true); echo \$data['data']['access_token'] ?? '';")

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed. Response:"
    echo "$LOGIN_RESPONSE" | jq '.' 2>/dev/null || echo "$LOGIN_RESPONSE"
    echo ""
    echo "💡 Make sure the guardian password is 'password' or update the script"
    exit 1
fi

echo "✅ Login successful"
echo ""

# Test 1: Get all fees
echo "1️⃣  Testing: Get All Fees"
echo "─────────────────────────────────────────────────────────────"
FEES_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees?per_page=10" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$FEES_RESPONSE" | jq '.' 2>/dev/null || echo "$FEES_RESPONSE"
echo ""

# Test 2: Get pending fees
echo "2️⃣  Testing: Get Pending Fees"
echo "─────────────────────────────────────────────────────────────"
PENDING_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/pending" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$PENDING_RESPONSE" | jq '.' 2>/dev/null || echo "$PENDING_RESPONSE"
echo ""

# Test 3: Get payment summary
echo "3️⃣  Testing: Get Payment Summary"
echo "─────────────────────────────────────────────────────────────"
SUMMARY_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/summary?year=2026" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/json")

echo "$SUMMARY_RESPONSE" | jq '.' 2>/dev/null || echo "$SUMMARY_RESPONSE"
echo ""

echo "╔════════════════════════════════════════════════════════════╗"
echo "║                   Tests Complete! ✨                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
