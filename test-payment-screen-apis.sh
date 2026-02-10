#!/bin/bash

# Payment Screen API Test Script
# Tests all Payment Screen APIs for Guardian App

BASE_URL="http://192.168.100.114:8088/api/v1"
CONTENT_TYPE="Content-Type: application/json"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to print test result
print_result() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ PASS${NC}: $2"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗ FAIL${NC}: $2"
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
}

# Function to print section header
print_header() {
    echo ""
    echo -e "${YELLOW}========================================${NC}"
    echo -e "${YELLOW}$1${NC}"
    echo -e "${YELLOW}========================================${NC}"
}

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo -e "${RED}Error: jq is not installed. Please install jq to run this script.${NC}"
    echo "Install with: brew install jq (macOS) or apt-get install jq (Linux)"
    exit 1
fi

print_header "PAYMENT SCREEN API TESTS"

# Step 1: Login as Guardian
print_header "Step 1: Guardian Login"
echo "Logging in as guardian..."

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/guardian/auth/login" \
  -H "$CONTENT_TYPE" \
  -d '{
    "login": "guardian1",
    "password": "password123"
  }')

echo "$LOGIN_RESPONSE" | jq '.'

ACCESS_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token // empty')

if [ -z "$ACCESS_TOKEN" ]; then
    echo -e "${RED}Failed to get access token. Please check credentials.${NC}"
    exit 1
fi

print_result 0 "Guardian login successful"
echo "Access Token: ${ACCESS_TOKEN:0:20}..."

# Step 2: Get Guardian's Students
print_header "Step 2: Get Guardian's Students"
echo "Fetching guardian's students..."

STUDENTS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$STUDENTS_RESPONSE" | jq '.'

STUDENT_ID=$(echo "$STUDENTS_RESPONSE" | jq -r '.data[0].id // empty')

if [ -z "$STUDENT_ID" ]; then
    echo -e "${RED}No students found for this guardian.${NC}"
    exit 1
fi

print_result 0 "Students retrieved successfully"
echo "Student ID: $STUDENT_ID"

# Step 3: Get Fee Structure
print_header "Step 3: Get Fee Structure"
echo "Fetching fee structure for student..."

FEE_STRUCTURE_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/structure" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$FEE_STRUCTURE_RESPONSE" | jq '.'

FEE_SUCCESS=$(echo "$FEE_STRUCTURE_RESPONSE" | jq -r '.success // false')
if [ "$FEE_SUCCESS" = "true" ]; then
    print_result 0 "Fee structure retrieved successfully"
    
    # Extract fee IDs for payment submission
    MONTHLY_FEE_ID=$(echo "$FEE_STRUCTURE_RESPONSE" | jq -r '.data.monthly_fees[0].id // empty')
    ADDITIONAL_FEE_ID=$(echo "$FEE_STRUCTURE_RESPONSE" | jq -r '.data.additional_fees[0].id // empty')
    TOTAL_AMOUNT=$(echo "$FEE_STRUCTURE_RESPONSE" | jq -r '.data.total_monthly // 0')
    
    echo "Monthly Fee ID: $MONTHLY_FEE_ID"
    echo "Additional Fee ID: $ADDITIONAL_FEE_ID"
    echo "Total Amount: $TOTAL_AMOUNT MMK"
else
    print_result 1 "Failed to retrieve fee structure"
fi

# Step 4: Get Payment Methods
print_header "Step 4: Get Payment Methods"
echo "Fetching payment methods..."

PAYMENT_METHODS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/payment-methods" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$PAYMENT_METHODS_RESPONSE" | jq '.'

METHODS_SUCCESS=$(echo "$PAYMENT_METHODS_RESPONSE" | jq -r '.success // false')
if [ "$METHODS_SUCCESS" = "true" ]; then
    print_result 0 "Payment methods retrieved successfully"
    
    # Extract first payment method ID
    PAYMENT_METHOD_ID=$(echo "$PAYMENT_METHODS_RESPONSE" | jq -r '.data.methods[0].id // empty')
    PAYMENT_METHOD_NAME=$(echo "$PAYMENT_METHODS_RESPONSE" | jq -r '.data.methods[0].name // empty')
    
    echo "Payment Method ID: $PAYMENT_METHOD_ID"
    echo "Payment Method Name: $PAYMENT_METHOD_NAME"
else
    print_result 1 "Failed to retrieve payment methods"
fi

# Step 5: Get Payment Methods (Filter by Bank)
print_header "Step 5: Get Payment Methods (Filter by Bank)"
echo "Fetching bank payment methods only..."

BANK_METHODS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/payment-methods?type=bank" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$BANK_METHODS_RESPONSE" | jq '.'

BANK_SUCCESS=$(echo "$BANK_METHODS_RESPONSE" | jq -r '.success // false')
if [ "$BANK_SUCCESS" = "true" ]; then
    print_result 0 "Bank payment methods retrieved successfully"
else
    print_result 1 "Failed to retrieve bank payment methods"
fi

# Step 6: Get Payment Methods (Filter by Mobile Wallet)
print_header "Step 6: Get Payment Methods (Filter by Mobile Wallet)"
echo "Fetching mobile wallet payment methods only..."

WALLET_METHODS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/payment-methods?type=mobile_wallet" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$WALLET_METHODS_RESPONSE" | jq '.'

WALLET_SUCCESS=$(echo "$WALLET_METHODS_RESPONSE" | jq -r '.success // false')
if [ "$WALLET_SUCCESS" = "true" ]; then
    print_result 0 "Mobile wallet payment methods retrieved successfully"
else
    print_result 1 "Failed to retrieve mobile wallet payment methods"
fi

# Step 7: Get Payment Options
print_header "Step 7: Get Payment Options"
echo "Fetching payment options..."

PAYMENT_OPTIONS_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/payment-options" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$PAYMENT_OPTIONS_RESPONSE" | jq '.'

OPTIONS_SUCCESS=$(echo "$PAYMENT_OPTIONS_RESPONSE" | jq -r '.success // false')
if [ "$OPTIONS_SUCCESS" = "true" ]; then
    print_result 0 "Payment options retrieved successfully"
else
    print_result 1 "Failed to retrieve payment options"
fi

# Step 8: Submit Payment (with mock receipt image)
print_header "Step 8: Submit Payment"
echo "Submitting payment with receipt..."

# Create a simple base64 encoded test image (1x1 pixel PNG)
MOCK_RECEIPT="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="

SUBMIT_PAYMENT_RESPONSE=$(curl -s -X POST "$BASE_URL/guardian/students/$STUDENT_ID/fees/payments" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -d "{
    \"fee_ids\": [\"$MONTHLY_FEE_ID\"],
    \"payment_method_id\": \"$PAYMENT_METHOD_ID\",
    \"payment_amount\": 120000,
    \"payment_months\": 1,
    \"payment_date\": \"$(date +%Y-%m-%d)\",
    \"receipt_image\": \"$MOCK_RECEIPT\",
    \"notes\": \"Test payment from API test script\"
  }")

echo "$SUBMIT_PAYMENT_RESPONSE" | jq '.'

SUBMIT_SUCCESS=$(echo "$SUBMIT_PAYMENT_RESPONSE" | jq -r '.success // false')
if [ "$SUBMIT_SUCCESS" = "true" ]; then
    print_result 0 "Payment submitted successfully"
    
    PAYMENT_ID=$(echo "$SUBMIT_PAYMENT_RESPONSE" | jq -r '.data.payment_id // empty')
    PAYMENT_STATUS=$(echo "$SUBMIT_PAYMENT_RESPONSE" | jq -r '.data.status // empty')
    
    echo "Payment ID: $PAYMENT_ID"
    echo "Payment Status: $PAYMENT_STATUS"
else
    print_result 1 "Failed to submit payment"
fi

# Step 9: Get Payment History
print_header "Step 9: Get Payment History"
echo "Fetching payment history..."

PAYMENT_HISTORY_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/payment-history?limit=10" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$PAYMENT_HISTORY_RESPONSE" | jq '.'

HISTORY_SUCCESS=$(echo "$PAYMENT_HISTORY_RESPONSE" | jq -r '.success // false')
if [ "$HISTORY_SUCCESS" = "true" ]; then
    print_result 0 "Payment history retrieved successfully"
    
    PAYMENT_COUNT=$(echo "$PAYMENT_HISTORY_RESPONSE" | jq -r '.data.meta.total // 0')
    echo "Total Payments: $PAYMENT_COUNT"
else
    print_result 1 "Failed to retrieve payment history"
fi

# Step 10: Get Payment History (Filter by Status)
print_header "Step 10: Get Payment History (Filter by Pending)"
echo "Fetching pending payments..."

PENDING_HISTORY_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/payment-history?status=pending&limit=10" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN")

echo "$PENDING_HISTORY_RESPONSE" | jq '.'

PENDING_SUCCESS=$(echo "$PENDING_HISTORY_RESPONSE" | jq -r '.success // false')
if [ "$PENDING_SUCCESS" = "true" ]; then
    print_result 0 "Pending payment history retrieved successfully"
else
    print_result 1 "Failed to retrieve pending payment history"
fi

# Step 11: Test Validation - Missing Required Fields
print_header "Step 11: Test Validation - Missing Required Fields"
echo "Testing payment submission with missing fields..."

VALIDATION_RESPONSE=$(curl -s -X POST "$BASE_URL/guardian/students/$STUDENT_ID/fees/payments" \
  -H "$CONTENT_TYPE" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -d '{
    "fee_ids": [],
    "payment_amount": 0
  }')

echo "$VALIDATION_RESPONSE" | jq '.'

VALIDATION_FAILED=$(echo "$VALIDATION_RESPONSE" | jq -r '.success // true')
if [ "$VALIDATION_FAILED" = "false" ]; then
    print_result 0 "Validation correctly rejected invalid data"
else
    print_result 1 "Validation should have rejected invalid data"
fi

# Step 12: Test Unauthorized Access
print_header "Step 12: Test Unauthorized Access"
echo "Testing fee structure access without token..."

UNAUTH_RESPONSE=$(curl -s -X GET "$BASE_URL/guardian/students/$STUDENT_ID/fees/structure" \
  -H "$CONTENT_TYPE")

echo "$UNAUTH_RESPONSE" | jq '.'

UNAUTH_FAILED=$(echo "$UNAUTH_RESPONSE" | jq -r '.success // true')
if [ "$UNAUTH_FAILED" = "false" ]; then
    print_result 0 "Unauthorized access correctly rejected"
else
    print_result 1 "Unauthorized access should have been rejected"
fi

# Print Summary
print_header "TEST SUMMARY"
echo -e "Total Tests: ${YELLOW}$TOTAL_TESTS${NC}"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
echo -e "Failed: ${RED}$FAILED_TESTS${NC}"

if [ $FAILED_TESTS -eq 0 ]; then
    echo -e "\n${GREEN}All tests passed! ✓${NC}"
    exit 0
else
    echo -e "\n${RED}Some tests failed. Please review the output above.${NC}"
    exit 1
fi
