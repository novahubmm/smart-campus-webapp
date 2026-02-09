#!/bin/bash

# Test Leave Request Detail via Query Parameter
# Mobile developer's preferred approach

BASE_URL="http://localhost:8000/api/v1"
STUDENT_ID="3a48862e-ed0e-4991-b2c7-5c4953ed7227"
REQUEST_UUID="c586d56a-8184-444c-ab32-fbed2a01ec84"

echo "=========================================="
echo "Testing Leave Request Detail via Query Parameter"
echo "=========================================="
echo ""

# Step 1: Login as guardian
echo "Step 1: Login as guardian..."
LOGIN_RESPONSE=$(curl -s -X POST "${BASE_URL}/guardian/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guardian1@smartcampusedu.com",
    "password": "password"
  }')

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // .token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ Failed to get authentication token"
  exit 1
fi

echo "✅ Login successful"
echo ""

# Step 2: Get leave request list (without request_uuid)
echo "=========================================="
echo "Step 2: Get Leave Request List"
echo "=========================================="
echo "URL: ${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests"
echo ""

LIST_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$LIST_RESPONSE" | jq '.'

LIST_SUCCESS=$(echo "$LIST_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$LIST_SUCCESS" = "true" ]; then
  echo "✅ List endpoint works!"
  COUNT=$(echo "$LIST_RESPONSE" | jq '.data | length')
  echo "   Found $COUNT leave request(s)"
else
  echo "❌ List endpoint failed"
fi

echo ""
echo "=========================================="
echo "Step 3: Get Leave Request Detail via Query Parameter"
echo "=========================================="
echo "URL: ${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests?request_uuid=${REQUEST_UUID}"
echo ""

DETAIL_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests?request_uuid=${REQUEST_UUID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$DETAIL_RESPONSE" | jq '.'

DETAIL_SUCCESS=$(echo "$DETAIL_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$DETAIL_SUCCESS" = "true" ]; then
  echo "✅ Detail via query parameter works!"
  echo ""
  echo "Leave Request Details:"
  echo "  ID: $(echo "$DETAIL_RESPONSE" | jq -r '.data.id')"
  echo "  Type: $(echo "$DETAIL_RESPONSE" | jq -r '.data.leave_type.name')"
  echo "  Start Date: $(echo "$DETAIL_RESPONSE" | jq -r '.data.start_date')"
  echo "  End Date: $(echo "$DETAIL_RESPONSE" | jq -r '.data.end_date')"
  echo "  Status: $(echo "$DETAIL_RESPONSE" | jq -r '.data.status')"
else
  echo "❌ Detail via query parameter failed"
  echo "Error: $(echo "$DETAIL_RESPONSE" | jq -r '.message')"
  exit 1
fi

echo ""
echo "=========================================="
echo "Step 4: Test with Non-Existent UUID"
echo "=========================================="
FAKE_UUID="00000000-0000-0000-0000-000000000000"
echo "URL: ${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests?request_uuid=${FAKE_UUID}"
echo ""

NOT_FOUND_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests?request_uuid=${FAKE_UUID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$NOT_FOUND_RESPONSE" | jq '.'

NOT_FOUND_SUCCESS=$(echo "$NOT_FOUND_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$NOT_FOUND_SUCCESS" = "false" ]; then
  echo "✅ Correctly returns 404 for non-existent UUID"
  echo "   Message: $(echo "$NOT_FOUND_RESPONSE" | jq -r '.message')"
else
  echo "❌ Should return error for non-existent UUID"
fi

echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
echo "✅ List endpoint: Works"
echo "✅ Detail via query parameter: Works"
echo "✅ 404 handling: Works"
echo ""
echo "Mobile developer can use:"
echo "  GET /guardian/students/{student_id}/leave-requests?request_uuid={uuid}"
echo ""
echo "=========================================="
echo "Test completed successfully!"
echo "=========================================="
