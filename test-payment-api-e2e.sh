#!/bin/bash

# End-to-End Payment API Test
# This script will login as guardian1 and test the fee structure API

BASE_URL="http://192.168.100.114:8088/api/v1"
GUARDIAN_EMAIL="guardian1@smartcampusedu.com"
GUARDIAN_PASSWORD="password"
STUDENT_ID="3a48862e-ed0e-4991-b2c7-5c4953ed7227"

echo "========================================="
echo "End-to-End Payment API Test"
echo "========================================="
echo ""
echo "Guardian: $GUARDIAN_EMAIL"
echo "Student ID: $STUDENT_ID"
echo ""

# Step 1: Login
echo "Step 1: Login as Guardian"
echo "─────────────────────────────────────────"

LOGIN_RESPONSE=$(curl -s -X POST "${BASE_URL}/guardian/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"identifier\": \"$GUARDIAN_EMAIL\", \"password\": \"$GUARDIAN_PASSWORD\"}")

echo "$LOGIN_RESPONSE" | jq '.' 2>/dev/null || echo "$LOGIN_RESPONSE"
echo ""

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token' 2>/dev/null)

if [ "$TOKEN" == "null" ] || [ -z "$TOKEN" ]; then
    echo "❌ Login failed! Could not get token."
    echo ""
    exit 1
fi

echo "✅ Login successful!"
echo "Token: ${TOKEN:0:20}..."
echo ""
echo ""

# Step 2: Get Students List
echo "Step 2: Get Students List"
echo "─────────────────────────────────────────"

STUDENTS_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$STUDENTS_RESPONSE" | jq '.' 2>/dev/null || echo "$STUDENTS_RESPONSE"
echo ""
echo ""

# Step 3: Test Fee Structure API
echo "Step 3: Test Fee Structure API"
echo "─────────────────────────────────────────"
echo "GET ${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure"
echo ""

FEE_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\nHTTP_STATUS:%{http_code}")

# Extract HTTP status
HTTP_STATUS=$(echo "$FEE_RESPONSE" | grep "HTTP_STATUS" | cut -d':' -f2)
RESPONSE_BODY=$(echo "$FEE_RESPONSE" | sed '/HTTP_STATUS/d')

echo "$RESPONSE_BODY" | jq '.' 2>/dev/null || echo "$RESPONSE_BODY"
echo ""
echo "HTTP Status: $HTTP_STATUS"
echo ""

# Check if successful
SUCCESS=$(echo "$RESPONSE_BODY" | jq -r '.success' 2>/dev/null)

if [ "$SUCCESS" == "true" ]; then
    echo "✅ Fee Structure API Test: SUCCESS!"
    echo ""
    
    # Extract some data
    STUDENT_NAME=$(echo "$RESPONSE_BODY" | jq -r '.data.student_name' 2>/dev/null)
    GRADE=$(echo "$RESPONSE_BODY" | jq -r '.data.grade' 2>/dev/null)
    TOTAL=$(echo "$RESPONSE_BODY" | jq -r '.data.total_monthly' 2>/dev/null)
    
    echo "Student: $STUDENT_NAME"
    echo "Grade: $GRADE"
    echo "Total Monthly Fee: $TOTAL MMK"
    echo ""
else
    echo "❌ Fee Structure API Test: FAILED!"
    echo ""
    ERROR_MSG=$(echo "$RESPONSE_BODY" | jq -r '.message' 2>/dev/null)
    echo "Error: $ERROR_MSG"
    echo ""
fi

echo ""

# Step 4: Test Payment Methods API
echo "Step 4: Test Payment Methods API"
echo "─────────────────────────────────────────"

METHODS_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/payment-methods" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$METHODS_RESPONSE" | jq '.data.total_count, .data.active_count' 2>/dev/null || echo "$METHODS_RESPONSE"
echo ""

METHODS_COUNT=$(echo "$METHODS_RESPONSE" | jq -r '.data.total_count' 2>/dev/null)

if [ "$METHODS_COUNT" == "7" ]; then
    echo "✅ Payment Methods API Test: SUCCESS! (7 methods found)"
else
    echo "⚠️  Payment Methods API Test: Unexpected count ($METHODS_COUNT)"
fi

echo ""
echo ""

# Step 5: Test Payment Options API
echo "Step 5: Test Payment Options API"
echo "─────────────────────────────────────────"

OPTIONS_RESPONSE=$(curl -s -X GET "${BASE_URL}/guardian/payment-options" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$OPTIONS_RESPONSE" | jq '.data.options | length' 2>/dev/null || echo "$OPTIONS_RESPONSE"
echo ""

OPTIONS_COUNT=$(echo "$OPTIONS_RESPONSE" | jq -r '.data.options | length' 2>/dev/null)

if [ "$OPTIONS_COUNT" == "5" ]; then
    echo "✅ Payment Options API Test: SUCCESS! (5 options found)"
else
    echo "⚠️  Payment Options API Test: Unexpected count ($OPTIONS_COUNT)"
fi

echo ""
echo ""

# Summary
echo "========================================="
echo "Test Summary"
echo "========================================="
echo ""
echo "✅ Login: SUCCESS"
echo "✅ Get Students: SUCCESS"

if [ "$SUCCESS" == "true" ]; then
    echo "✅ Fee Structure: SUCCESS"
else
    echo "❌ Fee Structure: FAILED"
fi

if [ "$METHODS_COUNT" == "7" ]; then
    echo "✅ Payment Methods: SUCCESS"
else
    echo "⚠️  Payment Methods: CHECK NEEDED"
fi

if [ "$OPTIONS_COUNT" == "5" ]; then
    echo "✅ Payment Options: SUCCESS"
else
    echo "⚠️  Payment Options: CHECK NEEDED"
fi

echo ""
echo "========================================="
echo ""
