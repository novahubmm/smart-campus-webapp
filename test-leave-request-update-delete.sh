#!/bin/bash

# Test Leave Request Update and Delete with request_uuid query parameter
# This script tests PUT and DELETE operations using request_uuid

BASE_URL="http://localhost:8000/api/v1"
GUARDIAN_EMAIL="guardian@example.com"
GUARDIAN_PASSWORD="password"

echo "=========================================="
echo "Leave Request Update/Delete Test"
echo "=========================================="
echo ""

# Step 1: Login
echo "1. Logging in as guardian..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$GUARDIAN_EMAIL\",
    \"password\": \"$GUARDIAN_PASSWORD\"
  }")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo "✅ Login successful"
echo ""

# Step 2: Get students
echo "2. Getting guardian's students..."
STUDENTS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students" \
  -H "Authorization: Bearer $TOKEN")

STUDENT_ID=$(echo $STUDENTS_RESPONSE | grep -o '"id":"[^"]*' | head -1 | cut -d'"' -f4)

if [ -z "$STUDENT_ID" ]; then
    echo "❌ No students found"
    echo "Response: $STUDENTS_RESPONSE"
    exit 1
fi

echo "✅ Found student: $STUDENT_ID"
echo ""

# Step 3: Create a test leave request
echo "3. Creating a test leave request..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/guardian/students/$STUDENT_ID/leave-requests" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"leave_type\": \"sick\",
    \"start_date\": \"2024-03-20\",
    \"end_date\": \"2024-03-21\",
    \"reason\": \"Test leave request for update/delete\"
  }")

REQUEST_UUID=$(echo $CREATE_RESPONSE | grep -o '"request_uuid":"[^"]*' | cut -d'"' -f4)

if [ -z "$REQUEST_UUID" ]; then
    echo "❌ Failed to create leave request"
    echo "Response: $CREATE_RESPONSE"
    exit 1
fi

echo "✅ Created leave request: $REQUEST_UUID"
echo ""

# Step 4: Update using request_uuid query parameter
echo "4. Testing PUT with request_uuid query parameter..."
UPDATE_RESPONSE=$(curl -s -X PUT "$BASE_URL/guardian/students/$STUDENT_ID/leave-requests?request_uuid=$REQUEST_UUID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"reason\": \"Updated reason via query parameter\"
  }")

echo "Response: $UPDATE_RESPONSE"

if echo "$UPDATE_RESPONSE" | grep -q '"success":true'; then
    echo "✅ PUT with request_uuid successful"
else
    echo "❌ PUT with request_uuid failed"
fi
echo ""

# Step 5: Verify the update
echo "5. Verifying the update..."
DETAIL_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/leave-requests?request_uuid=$REQUEST_UUID" \
  -H "Authorization: Bearer $TOKEN")

echo "Response: $DETAIL_RESPONSE"

if echo "$DETAIL_RESPONSE" | grep -q "Updated reason via query parameter"; then
    echo "✅ Update verified successfully"
else
    echo "❌ Update verification failed"
fi
echo ""

# Step 6: Delete using request_uuid query parameter
echo "6. Testing DELETE with request_uuid query parameter..."
DELETE_RESPONSE=$(curl -s -X DELETE "$BASE_URL/guardian/students/$STUDENT_ID/leave-requests?request_uuid=$REQUEST_UUID" \
  -H "Authorization: Bearer $TOKEN")

echo "Response: $DELETE_RESPONSE"

if echo "$DELETE_RESPONSE" | grep -q '"success":true'; then
    echo "✅ DELETE with request_uuid successful"
else
    echo "❌ DELETE with request_uuid failed"
fi
echo ""

# Step 7: Verify deletion
echo "7. Verifying deletion..."
VERIFY_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/leave-requests?request_uuid=$REQUEST_UUID" \
  -H "Authorization: Bearer $TOKEN")

echo "Response: $VERIFY_RESPONSE"

if echo "$VERIFY_RESPONSE" | grep -q '"success":false'; then
    echo "✅ Deletion verified - request not found"
else
    echo "⚠️  Request still exists or unexpected response"
fi
echo ""

echo "=========================================="
echo "Test Complete"
echo "=========================================="
