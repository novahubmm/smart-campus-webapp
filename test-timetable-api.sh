#!/bin/bash

# Timetable API Test Script
# Tests the Guardian Timetable API endpoints

BASE_URL="http://192.168.100.114:8088/api/v1"
GUARDIAN_BASE_URL="$BASE_URL/guardian"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test counter
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to print test header
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

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

# Function to print summary
print_summary() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}TEST SUMMARY${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo -e "Total Tests: $TOTAL_TESTS"
    echo -e "${GREEN}Passed: $PASSED_TESTS${NC}"
    echo -e "${RED}Failed: $FAILED_TESTS${NC}"
    
    if [ $FAILED_TESTS -eq 0 ]; then
        echo -e "\n${GREEN}🎉 All tests passed!${NC}\n"
    else
        echo -e "\n${RED}❌ Some tests failed${NC}\n"
    fi
}

# Step 1: Login
print_header "STEP 1: Login"

# Try to login with phone number (common pattern in the system)
echo "Logging in as guardian..."
LOGIN_RESPONSE=$(curl -s -X POST "$GUARDIAN_BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "login": "09123456789",
    "password": "password123"
  }')

echo "$LOGIN_RESPONSE" | jq '.'

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token // empty')

if [ -z "$TOKEN" ]; then
    echo -e "${YELLOW}First login attempt failed, trying with email...${NC}"
    
    # Try alternative login
    LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
      -H "Content-Type: application/json" \
      -d '{
        "identifier": "guardian1@example.com",
        "password": "password123",
        "role": "guardian"
      }')
    
    echo "$LOGIN_RESPONSE" | jq '.'
    TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.access_token // empty')
fi

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Login failed. Cannot proceed with tests.${NC}"
    echo -e "${YELLOW}Please ensure you have guardian users seeded in the database.${NC}"
    exit 1
fi

print_result 0 "Login successful"

# Extract student ID
STUDENT_ID=$(echo "$LOGIN_RESPONSE" | jq -r '.data.user.guardian_profile.students[0].id // empty')

if [ -z "$STUDENT_ID" ]; then
    echo -e "${RED}❌ No student found. Cannot proceed with tests.${NC}"
    exit 1
fi

echo -e "${GREEN}Student ID: $STUDENT_ID${NC}"

# Step 2: Test Weekly Timetable (Old Route)
print_header "STEP 2: Get Weekly Timetable (Old Route)"

echo "Testing: GET /guardian/timetable?student_id=$STUDENT_ID"
TIMETABLE_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/timetable?student_id=$STUDENT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$TIMETABLE_RESPONSE" | jq '.'

# Validate response
SUCCESS=$(echo "$TIMETABLE_RESPONSE" | jq -r '.success // false')
if [ "$SUCCESS" = "true" ]; then
    print_result 0 "Get weekly timetable (old route)"
    
    # Validate data structure
    STUDENT_NAME=$(echo "$TIMETABLE_RESPONSE" | jq -r '.data.student_name // empty')
    GRADE=$(echo "$TIMETABLE_RESPONSE" | jq -r '.data.grade // empty')
    WEEK_START=$(echo "$TIMETABLE_RESPONSE" | jq -r '.data.week_start_date // empty')
    WEEK_END=$(echo "$TIMETABLE_RESPONSE" | jq -r '.data.week_end_date // empty')
    
    [ ! -z "$STUDENT_NAME" ] && print_result 0 "Student name present: $STUDENT_NAME" || print_result 1 "Student name missing"
    [ ! -z "$GRADE" ] && print_result 0 "Grade present: $GRADE" || print_result 1 "Grade missing"
    [ ! -z "$WEEK_START" ] && print_result 0 "Week start date present: $WEEK_START" || print_result 1 "Week start date missing"
    [ ! -z "$WEEK_END" ] && print_result 0 "Week end date present: $WEEK_END" || print_result 1 "Week end date missing"
    
    # Check schedule days
    MONDAY_COUNT=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Monday | length')
    TUESDAY_COUNT=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Tuesday | length')
    
    echo -e "${YELLOW}Monday classes: $MONDAY_COUNT${NC}"
    echo -e "${YELLOW}Tuesday classes: $TUESDAY_COUNT${NC}"
    
    # Check break times
    BREAK_COUNT=$(echo "$TIMETABLE_RESPONSE" | jq '.data.break_times | length')
    [ "$BREAK_COUNT" -gt 0 ] && print_result 0 "Break times present: $BREAK_COUNT breaks" || print_result 1 "Break times missing"
    
    # Validate first period structure
    if [ "$MONDAY_COUNT" -gt 0 ]; then
        FIRST_PERIOD=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Monday[0]')
        echo -e "\n${YELLOW}First Monday Period:${NC}"
        echo "$FIRST_PERIOD" | jq '.'
        
        HAS_ID=$(echo "$FIRST_PERIOD" | jq 'has("id")')
        HAS_SUBJECT=$(echo "$FIRST_PERIOD" | jq 'has("subject")')
        HAS_TEACHER=$(echo "$FIRST_PERIOD" | jq 'has("teacher")')
        HAS_TIME=$(echo "$FIRST_PERIOD" | jq 'has("start_time") and has("end_time")')
        HAS_STATUS=$(echo "$FIRST_PERIOD" | jq 'has("status")')
        
        [ "$HAS_ID" = "true" ] && print_result 0 "Period has ID" || print_result 1 "Period missing ID"
        [ "$HAS_SUBJECT" = "true" ] && print_result 0 "Period has subject" || print_result 1 "Period missing subject"
        [ "$HAS_TEACHER" = "true" ] && print_result 0 "Period has teacher" || print_result 1 "Period missing teacher"
        [ "$HAS_TIME" = "true" ] && print_result 0 "Period has time" || print_result 1 "Period missing time"
        [ "$HAS_STATUS" = "true" ] && print_result 0 "Period has status" || print_result 1 "Period missing status"
    fi
else
    print_result 1 "Get weekly timetable (old route)"
    echo "$TIMETABLE_RESPONSE" | jq '.message'
fi

# Step 3: Test Weekly Timetable (New RESTful Route)
print_header "STEP 3: Get Weekly Timetable (New RESTful Route)"

echo "Testing: GET /guardian/students/$STUDENT_ID/timetable"
TIMETABLE_NEW_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/students/$STUDENT_ID/timetable" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$TIMETABLE_NEW_RESPONSE" | jq '.'

SUCCESS=$(echo "$TIMETABLE_NEW_RESPONSE" | jq -r '.success // false')
if [ "$SUCCESS" = "true" ]; then
    print_result 0 "Get weekly timetable (new RESTful route)"
else
    print_result 1 "Get weekly timetable (new RESTful route)"
fi

# Step 4: Test Weekly Timetable with Week Start Date
print_header "STEP 4: Get Weekly Timetable with Week Start Date"

WEEK_START_DATE="2026-02-10"
echo "Testing: GET /guardian/students/$STUDENT_ID/timetable?week_start_date=$WEEK_START_DATE"
TIMETABLE_WEEK_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/students/$STUDENT_ID/timetable?week_start_date=$WEEK_START_DATE" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$TIMETABLE_WEEK_RESPONSE" | jq '.'

SUCCESS=$(echo "$TIMETABLE_WEEK_RESPONSE" | jq -r '.success // false')
if [ "$SUCCESS" = "true" ]; then
    print_result 0 "Get weekly timetable with week_start_date parameter"
    
    RETURNED_WEEK_START=$(echo "$TIMETABLE_WEEK_RESPONSE" | jq -r '.data.week_start_date')
    if [ "$RETURNED_WEEK_START" = "$WEEK_START_DATE" ]; then
        print_result 0 "Week start date matches requested date"
    else
        print_result 1 "Week start date doesn't match (Expected: $WEEK_START_DATE, Got: $RETURNED_WEEK_START)"
    fi
else
    print_result 1 "Get weekly timetable with week_start_date parameter"
fi

# Step 5: Test Day Timetable
print_header "STEP 5: Get Day Timetable"

echo "Testing: GET /guardian/timetable/Monday?student_id=$STUDENT_ID"
DAY_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/timetable/Monday?student_id=$STUDENT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$DAY_RESPONSE" | jq '.'

SUCCESS=$(echo "$DAY_RESPONSE" | jq -r '.success // false')
if [ "$SUCCESS" = "true" ]; then
    print_result 0 "Get day timetable"
else
    print_result 1 "Get day timetable"
fi

# Step 6: Test Invalid Student ID
print_header "STEP 6: Test Error Handling - Invalid Student ID"

INVALID_STUDENT_ID="00000000-0000-0000-0000-000000000000"
echo "Testing: GET /guardian/students/$INVALID_STUDENT_ID/timetable"
ERROR_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/students/$INVALID_STUDENT_ID/timetable" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$ERROR_RESPONSE" | jq '.'

SUCCESS=$(echo "$ERROR_RESPONSE" | jq -r '.success // false')
if [ "$SUCCESS" = "false" ]; then
    print_result 0 "Invalid student ID returns error"
else
    print_result 1 "Invalid student ID should return error"
fi

# Step 7: Test Without Authentication
print_header "STEP 7: Test Error Handling - No Authentication"

echo "Testing: GET /guardian/students/$STUDENT_ID/timetable (without token)"
UNAUTH_RESPONSE=$(curl -s -X GET "$GUARDIAN_BASE_URL/students/$STUDENT_ID/timetable" \
  -H "Content-Type: application/json")

echo "$UNAUTH_RESPONSE" | jq '.'

# Check if response indicates unauthorized (401)
if echo "$UNAUTH_RESPONSE" | jq -e '.message' | grep -qi "unauthenticated\|unauthorized"; then
    print_result 0 "Unauthenticated request returns error"
else
    print_result 1 "Unauthenticated request should return error"
fi

# Step 8: Validate Myanmar Language Support
print_header "STEP 8: Validate Myanmar Language Support"

if [ "$SUCCESS" = "true" ]; then
    HAS_MM_SUBJECT=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Monday[0] | has("subject_mm")')
    HAS_MM_TEACHER=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Monday[0] | has("teacher_mm")')
    HAS_MM_ROOM=$(echo "$TIMETABLE_RESPONSE" | jq '.data.schedule.Monday[0] | has("room_mm")')
    
    [ "$HAS_MM_SUBJECT" = "true" ] && print_result 0 "Myanmar subject name present" || print_result 1 "Myanmar subject name missing"
    [ "$HAS_MM_TEACHER" = "true" ] && print_result 0 "Myanmar teacher name present" || print_result 1 "Myanmar teacher name missing"
    [ "$HAS_MM_ROOM" = "true" ] && print_result 0 "Myanmar room name present" || print_result 1 "Myanmar room name missing"
    
    # Check break times Myanmar
    HAS_MM_BREAK=$(echo "$TIMETABLE_RESPONSE" | jq '.data.break_times[0] | has("name_mm")')
    [ "$HAS_MM_BREAK" = "true" ] && print_result 0 "Myanmar break name present" || print_result 1 "Myanmar break name missing"
fi

# Step 9: Validate Class Status Types
print_header "STEP 9: Validate Class Status Types"

if [ "$MONDAY_COUNT" -gt 0 ]; then
    STATUS=$(echo "$TIMETABLE_RESPONSE" | jq -r '.data.schedule.Monday[0].status')
    
    if [[ "$STATUS" =~ ^(normal|cancelled|substitute|swapped)$ ]]; then
        print_result 0 "Valid status type: $STATUS"
    else
        print_result 1 "Invalid status type: $STATUS"
    fi
fi

# Print final summary
print_summary
