#!/bin/bash

echo "🧪 Testing Guardian API Endpoints"
echo "=================================="
echo ""

BASE_URL="http://localhost:8088/api/v1"
FAILED_TESTS=()
PASSED_TESTS=()

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to test endpoint
test_endpoint() {
    local test_name="$1"
    local method="$2"
    local endpoint="$3"
    local token="$4"
    local data="$5"
    
    echo "📝 Testing: $test_name"
    echo "   Method: $method $endpoint"
    
    if [ "$method" = "GET" ]; then
        RESPONSE=$(curl -s -w "\n%{http_code}" -X GET "$BASE_URL$endpoint" \
          -H "Authorization: Bearer $token" \
          -H "Accept: application/json")
    elif [ "$method" = "POST" ]; then
        RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL$endpoint" \
          -H "Authorization: Bearer $token" \
          -H "Content-Type: application/json" \
          -H "Accept: application/json" \
          -d "$data")
    elif [ "$method" = "PUT" ]; then
        RESPONSE=$(curl -s -w "\n%{http_code}" -X PUT "$BASE_URL$endpoint" \
          -H "Authorization: Bearer $token" \
          -H "Content-Type: application/json" \
          -H "Accept: application/json" \
          -d "$data")
    elif [ "$method" = "DELETE" ]; then
        RESPONSE=$(curl -s -w "\n%{http_code}" -X DELETE "$BASE_URL$endpoint" \
          -H "Authorization: Bearer $token" \
          -H "Accept: application/json")
    fi
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    BODY=$(echo "$RESPONSE" | sed '$d')
    
    if [ "$HTTP_CODE" = "500" ]; then
        echo -e "   ${RED}❌ FAILED (500 Error)${NC}"
        echo "   Response: $BODY" | head -c 200
        echo ""
        FAILED_TESTS+=("$test_name")
    elif [ "$HTTP_CODE" = "401" ] || [ "$HTTP_CODE" = "403" ]; then
        echo -e "   ${YELLOW}⚠️  Auth Error ($HTTP_CODE)${NC}"
    elif [ "$HTTP_CODE" = "404" ]; then
        echo -e "   ${YELLOW}⚠️  Not Found (404) - May need data${NC}"
    else
        echo -e "   ${GREEN}✅ PASSED ($HTTP_CODE)${NC}"
        PASSED_TESTS+=("$test_name")
    fi
    echo ""
}

# Step 1: Login as Guardian
echo "🔐 Step 1: Guardian Login"
echo "-------------------------"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "guardian1@smartcampusedu.com",
    "password": "password",
    "device_name": "test_device"
  }')

GUARDIAN_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // empty')
STUDENT_ID=$(echo "$LOGIN_RESPONSE" | jq -r '.data.user.students[0].id // empty')

if [ -z "$GUARDIAN_TOKEN" ]; then
    echo "❌ Guardian login failed. Cannot proceed with tests."
    echo "$LOGIN_RESPONSE" | jq '.'
    exit 1
fi

echo "✅ Guardian login successful!"
echo "   Student ID: $STUDENT_ID"
echo ""
echo "=================================="
echo ""

# Test Dashboard Endpoints
echo "📊 DASHBOARD ENDPOINTS"
echo "======================"
test_endpoint "Dashboard" "GET" "/dashboard" "$GUARDIAN_TOKEN"
test_endpoint "Today Schedule" "GET" "/guardian/students/$STUDENT_ID/schedule/today" "$GUARDIAN_TOKEN"
test_endpoint "Current Class" "GET" "/guardian/students/$STUDENT_ID/schedule/current-class" "$GUARDIAN_TOKEN"
test_endpoint "Upcoming Homework" "GET" "/guardian/students/$STUDENT_ID/homework/upcoming" "$GUARDIAN_TOKEN"
test_endpoint "Recent Announcements" "GET" "/guardian/students/$STUDENT_ID/announcements/recent" "$GUARDIAN_TOKEN"
test_endpoint "Fee Reminder" "GET" "/guardian/students/$STUDENT_ID/fees/pending" "$GUARDIAN_TOKEN"

# Test Student Profile Endpoints
echo "👤 STUDENT PROFILE ENDPOINTS"
echo "============================="
test_endpoint "Student Profile" "GET" "/guardian/students/$STUDENT_ID/profile" "$GUARDIAN_TOKEN"
test_endpoint "Academic Summary" "GET" "/guardian/students/$STUDENT_ID/academic/overview" "$GUARDIAN_TOKEN"
test_endpoint "Rankings" "GET" "/guardian/students/$STUDENT_ID/rankings" "$GUARDIAN_TOKEN"
test_endpoint "Achievements" "GET" "/guardian/students/$STUDENT_ID/achievements" "$GUARDIAN_TOKEN"
test_endpoint "GPA Trends" "GET" "/guardian/students/$STUDENT_ID/academic/gpa-trends" "$GUARDIAN_TOKEN"
test_endpoint "Performance Analysis" "GET" "/guardian/students/$STUDENT_ID/academic/performance-analysis" "$GUARDIAN_TOKEN"
test_endpoint "Subject Strengths/Weaknesses" "GET" "/guardian/students/$STUDENT_ID/academic/strengths-weaknesses" "$GUARDIAN_TOKEN"
test_endpoint "Badges" "GET" "/guardian/students/$STUDENT_ID/academic/badges" "$GUARDIAN_TOKEN"

# Test Attendance Endpoints
echo "📅 ATTENDANCE ENDPOINTS"
echo "======================="
test_endpoint "Attendance List" "GET" "/guardian/students/$STUDENT_ID/attendance" "$GUARDIAN_TOKEN"
test_endpoint "Attendance Summary" "GET" "/guardian/students/$STUDENT_ID/attendance/summary" "$GUARDIAN_TOKEN"
test_endpoint "Attendance Calendar" "GET" "/guardian/students/$STUDENT_ID/attendance/calendar" "$GUARDIAN_TOKEN"
test_endpoint "Attendance Stats" "GET" "/guardian/students/$STUDENT_ID/attendance/stats" "$GUARDIAN_TOKEN"

# Test Exam Endpoints
echo "📝 EXAM ENDPOINTS"
echo "================="
test_endpoint "Exams List" "GET" "/guardian/students/$STUDENT_ID/exams" "$GUARDIAN_TOKEN"
test_endpoint "Performance Trends" "GET" "/guardian/students/$STUDENT_ID/exams/performance-trends" "$GUARDIAN_TOKEN"
test_endpoint "Upcoming Exams" "GET" "/guardian/students/$STUDENT_ID/exams/upcoming" "$GUARDIAN_TOKEN"
test_endpoint "Past Exams" "GET" "/guardian/students/$STUDENT_ID/exams/past" "$GUARDIAN_TOKEN"

# Test Subject Endpoints
echo "📚 SUBJECT ENDPOINTS"
echo "===================="
test_endpoint "Subjects List" "GET" "/guardian/students/$STUDENT_ID/subjects" "$GUARDIAN_TOKEN"

# Test Homework Endpoints
echo "📖 HOMEWORK ENDPOINTS"
echo "====================="
test_endpoint "Homework List" "GET" "/guardian/students/$STUDENT_ID/homework" "$GUARDIAN_TOKEN"
test_endpoint "Homework Stats" "GET" "/guardian/students/$STUDENT_ID/homework/stats" "$GUARDIAN_TOKEN"
test_endpoint "Upcoming Homework" "GET" "/guardian/students/$STUDENT_ID/homework/upcoming" "$GUARDIAN_TOKEN"

# Test Timetable Endpoints
echo "🕐 TIMETABLE ENDPOINTS"
echo "======================"
test_endpoint "Timetable" "GET" "/guardian/students/$STUDENT_ID/timetable" "$GUARDIAN_TOKEN"
test_endpoint "Monday Timetable" "GET" "/guardian/students/$STUDENT_ID/timetable/monday" "$GUARDIAN_TOKEN"

# Test Class Information Endpoints
echo "🏫 CLASS INFORMATION ENDPOINTS"
echo "==============================="
test_endpoint "Class Info" "GET" "/guardian/students/$STUDENT_ID/class" "$GUARDIAN_TOKEN"
test_endpoint "Class Details" "GET" "/guardian/students/$STUDENT_ID/class/details" "$GUARDIAN_TOKEN"
test_endpoint "Class Teachers" "GET" "/guardian/students/$STUDENT_ID/class/teachers" "$GUARDIAN_TOKEN"
test_endpoint "Class Statistics" "GET" "/guardian/students/$STUDENT_ID/class/statistics" "$GUARDIAN_TOKEN"

# Test Announcement Endpoints
echo "📢 ANNOUNCEMENT ENDPOINTS"
echo "========================="
test_endpoint "Announcements List" "GET" "/guardian/students/$STUDENT_ID/announcements" "$GUARDIAN_TOKEN"
test_endpoint "Recent Announcements" "GET" "/guardian/students/$STUDENT_ID/announcements/recent" "$GUARDIAN_TOKEN"

# Test Fee Endpoints
echo "💰 FEE ENDPOINTS"
echo "================"
test_endpoint "Fees List" "GET" "/guardian/students/$STUDENT_ID/fees" "$GUARDIAN_TOKEN"
test_endpoint "Pending Fees" "GET" "/guardian/students/$STUDENT_ID/fees/pending" "$GUARDIAN_TOKEN"
test_endpoint "Payment Summary" "GET" "/guardian/students/$STUDENT_ID/fees/summary" "$GUARDIAN_TOKEN"
test_endpoint "Payment History" "GET" "/guardian/students/$STUDENT_ID/fees/payment-history" "$GUARDIAN_TOKEN"

# Test Leave Request Endpoints
echo "🏥 LEAVE REQUEST ENDPOINTS"
echo "=========================="
test_endpoint "Leave Requests List" "GET" "/guardian/students/$STUDENT_ID/leave-requests" "$GUARDIAN_TOKEN"
test_endpoint "Leave Request Stats" "GET" "/guardian/students/$STUDENT_ID/leave-requests/stats" "$GUARDIAN_TOKEN"

# Test Curriculum Endpoints
echo "📋 CURRICULUM ENDPOINTS"
echo "======================="
test_endpoint "Curriculum" "GET" "/guardian/students/$STUDENT_ID/curriculum" "$GUARDIAN_TOKEN"
test_endpoint "Curriculum Chapters" "GET" "/guardian/students/$STUDENT_ID/curriculum/chapters" "$GUARDIAN_TOKEN"

# Test Report Card Endpoints
echo "📊 REPORT CARD ENDPOINTS"
echo "========================"
test_endpoint "Report Cards List" "GET" "/guardian/students/$STUDENT_ID/report-cards" "$GUARDIAN_TOKEN"
test_endpoint "Latest Report Card" "GET" "/guardian/students/$STUDENT_ID/report-cards/latest" "$GUARDIAN_TOKEN"

# Test Notification Endpoints
echo "🔔 NOTIFICATION ENDPOINTS"
echo "========================="
test_endpoint "Notifications List" "GET" "/guardian/students/$STUDENT_ID/notifications" "$GUARDIAN_TOKEN"
test_endpoint "Unread Count" "GET" "/guardian/students/$STUDENT_ID/notifications/unread-count" "$GUARDIAN_TOKEN"

# Summary
echo ""
echo "=================================="
echo "📊 TEST SUMMARY"
echo "=================================="
echo -e "${GREEN}✅ Passed: ${#PASSED_TESTS[@]}${NC}"
echo -e "${RED}❌ Failed: ${#FAILED_TESTS[@]}${NC}"
echo ""

if [ ${#FAILED_TESTS[@]} -gt 0 ]; then
    echo "Failed Tests:"
    for test in "${FAILED_TESTS[@]}"; do
        echo "  - $test"
    done
    echo ""
    exit 1
else
    echo "🎉 All tests passed!"
    exit 0
fi
