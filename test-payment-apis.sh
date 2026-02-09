#!/bin/bash

# Payment Screen APIs Test Script
# Usage: ./test-payment-apis.sh

BASE_URL="http://192.168.100.114:8088/api/v1"
TOKEN=""
STUDENT_ID=""

echo "========================================="
echo "Payment Screen APIs Test Script"
echo "========================================="
echo ""

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo "⚠️  Warning: 'jq' is not installed. Install it for better JSON formatting."
    echo "   macOS: brew install jq"
    echo "   Ubuntu: sudo apt-get install jq"
    echo ""
fi

# Prompt for token
read -p "Enter Guardian Access Token: " TOKEN
if [ -z "$TOKEN" ]; then
    echo "❌ Token is required!"
    exit 1
fi

# Prompt for student ID
read -p "Enter Student ID: " STUDENT_ID
if [ -z "$STUDENT_ID" ]; then
    echo "❌ Student ID is required!"
    exit 1
fi

echo ""
echo "========================================="
echo "Test 1: Get Fee Structure"
echo "========================================="
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""
echo "========================================="
echo "Test 2: Get Payment Methods"
echo "========================================="
curl -X GET "${BASE_URL}/guardian/payment-methods" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""
echo "========================================="
echo "Test 3: Get Payment Options"
echo "========================================="
curl -X GET "${BASE_URL}/guardian/payment-options" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""
echo "========================================="
echo "Test 4: Get Payment History"
echo "========================================="
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/payment-history?limit=5" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -w "\nHTTP Status: %{http_code}\n" \
  | jq '.' 2>/dev/null || cat

echo ""
echo ""
echo "========================================="
echo "Test 5: Submit Payment (Skipped)"
echo "========================================="
echo "⚠️  Payment submission requires a base64 encoded image."
echo "   Use Postman or mobile app to test this endpoint."
echo ""

echo "========================================="
echo "✅ Tests Complete!"
echo "========================================="
echo ""
echo "Next Steps:"
echo "1. Check if all endpoints returned 200 OK"
echo "2. Verify data structure matches API spec"
echo "3. Test payment submission via Postman"
echo ""
