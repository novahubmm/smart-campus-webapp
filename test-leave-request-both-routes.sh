#!/bin/bash

# Test Leave Request Detail API - Both Old and New Routes
# This script tests both backward compatibility and new RESTful routes

BASE_URL="http://localhost:8000/api/v1"
STUDENT_ID="3a48862e-ed0e-4991-b2c7-5c4953ed7227"
REQUEST_ID="c586d56a-8184-444c-ab32-fbed2a01ec84"

echo "=========================================="
echo "Testing Leave Request Detail API"
echo "Both Old and New Routes"
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

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // .token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ Failed to get authentication token"
  exit 1
fi

echo "✅ Login successful"
echo ""

# Step 2: Test NEW RESTful route
echo "=========================================="
echo "Step 2: Test NEW RESTful Route"
echo "=========================================="
echo "URL: ${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests/${REQUEST_ID}"
echo ""

NEW_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests/${REQUEST_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$NEW_RESPONSE" | jq '.'

NEW_SUCCESS=$(echo "$NEW_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$NEW_SUCCESS" = "true" ]; then
  echo "✅ NEW route works!"
else
  echo "❌ NEW route failed"
  echo "Error: $(echo "$NEW_RESPONSE" | jq -r '.message')"
fi

echo ""
echo "=========================================="
echo "Step 3: Test OLD Backward Compatibility Route"
echo "=========================================="
echo "URL: ${BASE_URL}/guardian/leave-requests/${REQUEST_ID}?student_id=${STUDENT_ID}"
echo ""

OLD_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/leave-requests/${REQUEST_ID}?student_id=${STUDENT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$OLD_RESPONSE" | jq '.'

OLD_SUCCESS=$(echo "$OLD_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$OLD_SUCCESS" = "true" ]; then
  echo "✅ OLD route works!"
else
  echo "❌ OLD route failed"
  echo "Error: $(echo "$OLD_RESPONSE" | jq -r '.message')"
fi

echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
if [ "$NEW_SUCCESS" = "true" ] && [ "$OLD_SUCCESS" = "true" ]; then
  echo "✅ Both routes work correctly!"
  echo ""
  echo "Leave Request Details:"
  echo "  ID: $(echo "$NEW_RESPONSE" | jq -r '.data.id')"
  echo "  Type: $(echo "$NEW_RESPONSE" | jq -r '.data.leave_type.name')"
  echo "  Start Date: $(echo "$NEW_RESPONSE" | jq -r '.data.start_date')"
  echo "  End Date: $(echo "$NEW_RESPONSE" | jq -r '.data.end_date')"
  echo "  Status: $(echo "$NEW_RESPONSE" | jq -r '.data.status')"
else
  echo "❌ Some routes failed"
  echo "NEW route: $NEW_SUCCESS"
  echo "OLD route: $OLD_SUCCESS"
  exit 1
fi

echo ""
echo "=========================================="
echo "Test completed successfully!"
echo "=========================================="
