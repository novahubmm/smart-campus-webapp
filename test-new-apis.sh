#!/bin/bash

# Test script for Teacher Attendance and Free Period Activities APIs
# Usage: ./test-new-apis.sh

echo "=========================================="
echo "Smart Campus API Test Script"
echo "Testing: Teacher Attendance & Free Period Activities"
echo "=========================================="
echo ""

# Configuration
BASE_URL="http://localhost:8000/api/v1"
TOKEN=""  # Add your auth token here

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to test endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4
    
    echo -e "${YELLOW}Testing:${NC} $description"
    echo "Endpoint: $method $endpoint"
    
    if [ -z "$data" ]; then
        response=$(curl -s -X $method \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            "$BASE_URL$endpoint")
    else
        response=$(curl -s -X $method \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            -d "$data" \
            "$BASE_URL$endpoint")
    fi
    
    # Check if response contains "success": true
    if echo "$response" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ PASSED${NC}"
    else
        echo -e "${RED}✗ FAILED${NC}"
        echo "Response: $response"
    fi
    echo ""
}

# Check if token is set
if [ -z "$TOKEN" ]; then
    echo -e "${RED}ERROR: Please set your authentication token in the script${NC}"
    echo "Get token from: POST $BASE_URL/auth/login"
    echo ""
    exit 1
fi

echo "=========================================="
echo "TEACHER ATTENDANCE TESTS"
echo "=========================================="
echo ""

# Test 1: Get today's status
test_endpoint "GET" "/teacher/attendance/today" "" "Get Today's Attendance Status"

# Test 2: Check-in
check_in_data='{
  "latitude": 16.8661,
  "longitude": 96.1951,
  "device_info": "Test Device",
  "app_version": "1.0.0"
}'
test_endpoint "POST" "/teacher/attendance/check-in" "$check_in_data" "Check-In (Morning Attendance)"

# Test 3: Get today's status after check-in
test_endpoint "GET" "/teacher/attendance/today" "" "Get Today's Status (After Check-In)"

# Test 4: Try duplicate check-in (should fail)
test_endpoint "POST" "/teacher/attendance/check-in" "$check_in_data" "Duplicate Check-In (Should Fail)"

# Test 5: Check-out
check_out_data='{
  "latitude": 16.8661,
  "longitude": 96.1951,
  "notes": "Completed all tasks for today"
}'
test_endpoint "POST" "/teacher/attendance/check-out" "$check_out_data" "Check-Out (Evening Attendance)"

# Test 6: Get attendance history
test_endpoint "GET" "/teacher/my-attendance?month=current" "" "Get Attendance History (Current Month)"

echo "=========================================="
echo "FREE PERIOD ACTIVITIES TESTS"
echo "=========================================="
echo ""

# Test 7: Get activity types
test_endpoint "GET" "/free-period/activity-types" "" "Get Activity Types"

# Test 8: Record activity
activity_data='{
  "date": "'$(date +%Y-%m-%d)'",
  "start_time": "10:30",
  "end_time": "11:30",
  "activities": [
    {
      "activity_type": "1",
      "notes": "Prepared lesson plans for Grade 10 Mathematics"
    },
    {
      "activity_type": "2",
      "notes": "Graded 25 test papers"
    }
  ]
}'
test_endpoint "POST" "/free-period/activities" "$activity_data" "Record Free Period Activity"

# Test 9: Get activity history
test_endpoint "GET" "/free-period/activities" "" "Get Activity History"

# Test 10: Try overlapping time (should fail)
overlap_data='{
  "date": "'$(date +%Y-%m-%d)'",
  "start_time": "10:45",
  "end_time": "11:45",
  "activities": [
    {
      "activity_type": "3",
      "notes": "This should fail due to time overlap"
    }
  ]
}'
test_endpoint "POST" "/free-period/activities" "$overlap_data" "Overlapping Time (Should Fail)"

echo "=========================================="
echo "TEST SUMMARY"
echo "=========================================="
echo ""
echo "All tests completed!"
echo ""
echo "Next steps:"
echo "1. Check the responses above for any failures"
echo "2. Verify data in database"
echo "3. Test with mobile app"
echo ""
echo "Database check commands:"
echo "  php artisan tinker"
echo "  >>> TeacherAttendance::count()"
echo "  >>> FreePeriodActivity::count()"
echo "  >>> ActivityType::count()"
echo ""
