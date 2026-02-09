#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Guardian Subjects API Test (Old Routes) ===${NC}\n"

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

# Test 1: Get Subjects List (Old Route)
echo -e "${BLUE}Test 1: GET /subjects?student_id={student_id}${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/subjects?student_id=${STUDENT_ID}" \
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

# Test 2: Get Subject Detail (Old Route)
echo -e "${BLUE}Test 2: GET /subjects/{subject_id}?student_id={student_id}${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/subjects/${SUBJECT_ID}?student_id=${STUDENT_ID}" \
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

# Test 3: Get Subject Performance (Old Route)
echo -e "${BLUE}Test 3: GET /subjects/{subject_id}/performance?student_id={student_id}${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/subjects/${SUBJECT_ID}/performance?student_id=${STUDENT_ID}" \
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

# Test 4: Get Subject Schedule (Old Route)
echo -e "${BLUE}Test 4: GET /subjects/{subject_id}/schedule?student_id={student_id}${NC}"
RESPONSE=$(curl -s -X GET "${BASE_URL}/subjects/${SUBJECT_ID}/schedule?student_id=${STUDENT_ID}" \
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
echo -e "${GREEN}=== All Tests Completed ===${NC}"
