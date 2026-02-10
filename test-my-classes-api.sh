#!/bin/bash

# My Classes API Test Script
# Tests all endpoints for the My Classes screen

BASE_URL="http://192.168.100.114:8088/api/v1"
STUDENT_ID="019c45b4-d7b1-73b5-b03c-b1cff25f05d7"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================="
echo "My Classes API Test Suite"
echo "========================================="
echo ""

# Function to test endpoint
test_endpoint() {
    local name=$1
    local endpoint=$2
    local method=${3:-GET}
    
    echo -e "${YELLOW}Testing: $name${NC}"
    echo "Endpoint: $method $endpoint"
    
    response=$(curl -s -X $method \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        "$BASE_URL$endpoint")
    
    success=$(echo $response | jq -r '.success')
    
    if [ "$success" = "true" ]; then
        echo -e "${GREEN}✓ PASSED${NC}"
        echo "Response:"
        echo $response | jq '.'
    else
        echo -e "${RED}✗ FAILED${NC}"
        echo "Error:"
        echo $response | jq '.'
    fi
    
    echo ""
    echo "-----------------------------------------"
    echo ""
}

# Step 1: Login to get token
echo -e "${YELLOW}Step 1: Logging in...${NC}"
login_response=$(curl -s -X POST \
    -H "Content-Type: application/json" \
    -d '{
        "phone": "+959123456789",
        "password": "password123"
    }' \
    "$BASE_URL/guardian/auth/login")

TOKEN=$(echo $login_response | jq -r '.data.access_token')

if [ "$TOKEN" = "null" ] || [ -z "$TOKEN" ]; then
    echo -e "${RED}✗ Login failed. Please check credentials.${NC}"
    echo "Response:"
    echo $login_response | jq '.'
    exit 1
fi

echo -e "${GREEN}✓ Login successful${NC}"
echo "Token: ${TOKEN:0:20}..."
echo ""
echo "========================================="
echo ""

# Step 2: Get students list to get a valid student ID
echo -e "${YELLOW}Step 2: Getting students list...${NC}"
students_response=$(curl -s -X GET \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    "$BASE_URL/guardian/students")

STUDENT_ID=$(echo $students_response | jq -r '.data.students[0].id')

if [ "$STUDENT_ID" = "null" ] || [ -z "$STUDENT_ID" ]; then
    echo -e "${RED}✗ No students found${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Student ID: $STUDENT_ID${NC}"
echo ""
echo "========================================="
echo ""

# Step 3: Test all My Classes endpoints
echo -e "${YELLOW}Step 3: Testing My Classes Endpoints${NC}"
echo ""

# Test 1: Get Class Info
test_endpoint \
    "Get Class Info" \
    "/guardian/students/$STUDENT_ID/class-info"

# Test 2: Get Class Teachers
test_endpoint \
    "Get Class Teachers" \
    "/guardian/students/$STUDENT_ID/class-teachers"

# Test 3: Get Class Subjects
test_endpoint \
    "Get Class Subjects" \
    "/guardian/students/$STUDENT_ID/subjects"

# Test 4: Get Class Timetable (current week)
test_endpoint \
    "Get Class Timetable (Current Week)" \
    "/guardian/students/$STUDENT_ID/timetable"

# Test 5: Get Class Timetable (specific week)
WEEK_DATE=$(date +%Y-%m-%d)
test_endpoint \
    "Get Class Timetable (Specific Week)" \
    "/guardian/students/$STUDENT_ID/timetable?week_start_date=$WEEK_DATE"

# Test 6: Get Class Statistics
test_endpoint \
    "Get Class Statistics" \
    "/guardian/students/$STUDENT_ID/class-statistics"

# Summary
echo "========================================="
echo -e "${GREEN}Test Suite Completed${NC}"
echo "========================================="
echo ""
echo "All endpoints have been tested."
echo "Check the output above for any failures."
echo ""
