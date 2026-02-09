#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000/api/v1/guardian"

echo -e "${YELLOW}=== Bulk Leave Request API Test ===${NC}\n"

# Step 1: Login as Guardian
echo -e "${YELLOW}Step 1: Login as Guardian${NC}"
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guardian1@smartcampusedu.com",
    "password": "password"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.token // empty')

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Login failed${NC}"
    echo $LOGIN_RESPONSE | jq '.'
    exit 1
fi

echo -e "${GREEN}✓ Login successful${NC}"
echo "Token: ${TOKEN:0:20}..."
echo ""

# Step 2: Get Guardian's Students
echo -e "${YELLOW}Step 2: Get Guardian's Students${NC}"
STUDENTS_RESPONSE=$(curl -s -X GET "$BASE_URL/students" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo $STUDENTS_RESPONSE | jq '.'

# Extract student IDs properly as a JSON array
STUDENT_IDS_ARRAY=$(echo $STUDENTS_RESPONSE | jq -c '[.data[].id] | .[0:3]')

echo -e "\n${GREEN}✓ Found students${NC}"
echo "Student IDs: $STUDENT_IDS_ARRAY"
echo ""

# Step 3: Create Bulk Leave Request (Success Case)
echo -e "${YELLOW}Step 3: Create Bulk Leave Request (All Valid Students)${NC}"
BULK_RESPONSE=$(curl -s -X POST "$BASE_URL/leave-requests/bulk" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"student_ids\": $STUDENT_IDS_ARRAY,
    \"leave_type\": \"sick\",
    \"start_date\": \"2026-02-10\",
    \"end_date\": \"2026-02-11\",
    \"reason\": \"All students have fever\"
  }")

echo $BULK_RESPONSE | jq '.'
echo ""

# Step 4: Create Bulk Leave Request with Invalid Student (Partial Failure)
echo -e "${YELLOW}Step 4: Create Bulk Leave Request (With Invalid Student)${NC}"
# Get first student ID and add an invalid one
FIRST_STUDENT_ID=$(echo $STUDENTS_RESPONSE | jq -r '.data[0].id')
INVALID_STUDENT_IDS=$(jq -n --arg id "$FIRST_STUDENT_ID" '[$id, "invalid-student-id-123"]')

PARTIAL_RESPONSE=$(curl -s -X POST "$BASE_URL/leave-requests/bulk" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"student_ids\": $INVALID_STUDENT_IDS,
    \"leave_type\": \"casual\",
    \"start_date\": \"2026-02-12\",
    \"end_date\": \"2026-02-13\",
    \"reason\": \"Family event\"
  }")

echo $PARTIAL_RESPONSE | jq '.'
echo ""

# Step 5: Create Bulk Leave Request with Invalid Date Range (All Fail)
echo -e "${YELLOW}Step 5: Create Bulk Leave Request (Invalid Date Range)${NC}"
INVALID_DATE_RESPONSE=$(curl -s -X POST "$BASE_URL/leave-requests/bulk" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"student_ids\": $STUDENT_IDS_ARRAY,
    \"leave_type\": \"sick\",
    \"start_date\": \"2026-02-15\",
    \"end_date\": \"2026-02-14\",
    \"reason\": \"Invalid date range test\"
  }")

echo $INVALID_DATE_RESPONSE | jq '.'
echo ""

# Step 6: Verify Created Leave Requests
echo -e "${YELLOW}Step 6: Verify Created Leave Requests${NC}"
FIRST_STUDENT_ID=$(echo $STUDENTS_RESPONSE | jq -r '.data[0].id')

VERIFY_RESPONSE=$(curl -s -X GET "$BASE_URL/students/$FIRST_STUDENT_ID/leave-requests" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json")

echo $VERIFY_RESPONSE | jq '.'
echo ""

echo -e "${GREEN}=== Test Complete ===${NC}"
