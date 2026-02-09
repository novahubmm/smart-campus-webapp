#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Guardian Subjects API Test ===${NC}\n"

# Get auth token and student ID
echo "Getting authentication token..."
AUTH_DATA=$(php artisan tinker --execute="
\$user = App\Models\User::whereHas('guardianProfile')->first();
\$token = \$user->createToken('test')->plainTextToken;
\$guardian = \$user->guardianProfile;
\$student = \$guardian->students()->first();
echo json_encode(['token' => \$token, 'student_id' => \$student->id]);
")

TOKEN=$(echo $AUTH_DATA | jq -r '.token')
STUDENT_ID=$(echo $AUTH_DATA | jq -r '.student_id')

echo -e "Token: ${GREEN}${TOKEN:0:20}...${NC}"
echo -e "Student ID: ${GREEN}${STUDENT_ID}${NC}\n"

BASE_URL="http://localhost:8000/api/v1/guardian"

# Test 1: Get Subjects List
echo -e "${BLUE}Test 1: GET /students/{student_id}/subjects${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/students/${STUDENT_ID}/subjects" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

SUCCESS=$(echo $RESPONSE | jq -r '.success')
if [ "$SUCCESS" = "true" ]; then
    echo -e "${GREEN}✓ Success${NC}"
    SUBJECT_COUNT=$(echo $RESPONSE | jq '.data | length')
    echo "  Found ${SUBJECT_COUNT} subjects"
    
    # Get first subject ID for next tests
    SUBJECT_ID=$(echo $RESPONSE | jq -r '.data[0].id')
    SUBJECT_NAME=$(echo $RESPONSE | jq -r '.data[0].name')
    echo -e "  First subject: ${SUBJECT_NAME} (${SUBJECT_ID})"
else
    echo -e "${RED}✗ Failed${NC}"
    echo $RESPONSE | jq '.'
    exit 1
fi

echo ""

# Test 2: Get Subject Detail
echo -e "${BLUE}Test 2: GET /students/{student_id}/subjects/{subject_id}${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/students/${STUDENT_ID}/subjects/${SUBJECT_ID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

SUCCESS=$(echo $RESPONSE | jq -r '.success')
if [ "$SUCCESS" = "true" ]; then
    echo -e "${GREEN}✓ Success${NC}"
    echo "  Subject: $(echo $RESPONSE | jq -r '.data.name')"
    echo "  Teacher: $(echo $RESPONSE | jq -r '.data.teacher // "N/A"')"
else
    echo -e "${RED}✗ Failed${NC}"
    echo $RESPONSE | jq '.'
fi

echo ""

# Test 3: Get Subject Performance
echo -e "${BLUE}Test 3: GET /students/{student_id}/subjects/{subject_id}/performance${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/students/${STUDENT_ID}/subjects/${SUBJECT_ID}/performance" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

SUCCESS=$(echo $RESPONSE | jq -r '.success')
if [ "$SUCCESS" = "true" ]; then
    echo -e "${GREEN}✓ Success${NC}"
    echo "  Average: $(echo $RESPONSE | jq -r '.data.average_percentage')%"
    echo "  Grade: $(echo $RESPONSE | jq -r '.data.average_grade')"
    echo "  Total Exams: $(echo $RESPONSE | jq -r '.data.total_exams')"
else
    echo -e "${RED}✗ Failed${NC}"
    echo $RESPONSE | jq '.'
fi

echo ""

# Test 4: Get Subject Schedule
echo -e "${BLUE}Test 4: GET /students/{student_id}/subjects/{subject_id}/schedule${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/students/${STUDENT_ID}/subjects/${SUBJECT_ID}/schedule" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

SUCCESS=$(echo $RESPONSE | jq -r '.success')
if [ "$SUCCESS" = "true" ]; then
    echo -e "${GREEN}✓ Success${NC}"
    SCHEDULE_COUNT=$(echo $RESPONSE | jq '.data.schedule | length')
    UPCOMING_COUNT=$(echo $RESPONSE | jq '.data.upcoming_classes | length')
    echo "  Schedule periods: ${SCHEDULE_COUNT}"
    echo "  Upcoming classes: ${UPCOMING_COUNT}"
else
    echo -e "${RED}✗ Failed${NC}"
    echo $RESPONSE | jq '.'
fi

echo ""

# Test 5: Get Subject Curriculum
echo -e "${BLUE}Test 5: GET /students/{student_id}/subjects/{subject_id}/curriculum${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/students/${STUDENT_ID}/subjects/${SUBJECT_ID}/curriculum" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

SUCCESS=$(echo $RESPONSE | jq -r '.success')
if [ "$SUCCESS" = "true" ]; then
    echo -e "${GREEN}✓ Success${NC}"
    CHAPTERS=$(echo $RESPONSE | jq '.data.total_chapters')
    TOPICS=$(echo $RESPONSE | jq '.data.total_topics')
    PROGRESS=$(echo $RESPONSE | jq '.data.overall_progress')
    echo "  Total chapters: ${CHAPTERS}"
    echo "  Total topics: ${TOPICS}"
    echo "  Overall progress: ${PROGRESS}%"
else
    echo -e "${RED}✗ Failed${NC}"
    echo $RESPONSE | jq '.'
fi

echo ""
echo -e "${GREEN}=== All Tests Completed ===${NC}"
