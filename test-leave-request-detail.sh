#!/bin/bash

# Test Leave Request Detail API Fix
# This script tests the fixed leave request detail endpoint

BASE_URL="http://localhost:8000/api/v1"
STUDENT_ID="3a48862e-ed0e-4991-b2c7-5c4953ed7227"
REQUEST_ID="c586d56a-8184-444c-ab32-fbed2a01ec84"

echo "=========================================="
echo "Testing Leave Request Detail API Fix"
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

echo "$LOGIN_RESPONSE" | jq '.'

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // .token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ Failed to get authentication token"
  exit 1
fi

echo ""
echo "✅ Login successful"
echo "Token: ${TOKEN:0:20}..."
echo ""

# Step 2: Get leave request detail
echo "Step 2: Get leave request detail..."
echo "URL: ${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests/${REQUEST_ID}"
echo ""

DETAIL_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/leave-requests/${REQUEST_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo "$DETAIL_RESPONSE" | jq '.'

# Check if successful
SUCCESS=$(echo "$DETAIL_RESPONSE" | jq -r '.success // false')

echo ""
if [ "$SUCCESS" = "true" ]; then
  echo "✅ Leave request detail retrieved successfully!"
  echo ""
  echo "Leave Request Details:"
  echo "  ID: $(echo "$DETAIL_RESPONSE" | jq -r '.data.id')"
  echo "  Type: $(echo "$DETAIL_RESPONSE" | jq -r '.data.leave_type.name')"
  echo "  Start Date: $(echo "$DETAIL_RESPONSE" | jq -r '.data.start_date')"
  echo "  End Date: $(echo "$DETAIL_RESPONSE" | jq -r '.data.end_date')"
  echo "  Total Days: $(echo "$DETAIL_RESPONSE" | jq -r '.data.total_days')"
  echo "  Status: $(echo "$DETAIL_RESPONSE" | jq -r '.data.status')"
  echo "  Reason: $(echo "$DETAIL_RESPONSE" | jq -r '.data.reason')"
else
  echo "❌ Failed to retrieve leave request detail"
  echo "Error: $(echo "$DETAIL_RESPONSE" | jq -r '.message')"
  exit 1
fi

echo ""
echo "=========================================="
echo "Test completed successfully!"
echo "=========================================="
