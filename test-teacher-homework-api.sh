#!/bin/bash

BASE_URL="http://192.168.100.114:8088/api/v1"
EMAIL="konyeinchan@smartcampusedu.com"
PASSWORD="password"

echo "=== Teacher Homework API Test Cases ==="
echo ""

# 1. Login
echo "1. Login as Teacher..."
LOGIN_RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"${EMAIL}\",\"password\":\"${PASSWORD}\"}")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | sed 's/"token":"//')

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed"
    echo $LOGIN_RESPONSE | jq '.'
    exit 1
fi

echo "✓ Login successful"
echo "Token: ${TOKEN:0:20}..."
echo ""

# 2. Get Homework List
echo "2. Get Homework List..."
HOMEWORK_LIST=$(curl -s -X GET "${BASE_URL}/teacher/homework" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json")

echo $HOMEWORK_LIST | jq '.'
HOMEWORK_ID=$(echo $HOMEWORK_LIST | jq -r '.data[0].id // empty')
CLASS_ID=$(echo $HOMEWORK_LIST | jq -r '.data[0].class_id // empty')
SUBJECT_ID=$(echo $HOMEWORK_LIST | jq -r '.data[0].subject_id // empty')
echo ""

# 3. Get Homework List with Filters
echo "3. Get Homework List (Active Only)..."
curl -s -X GET "${BASE_URL}/teacher/homework?status=active" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq '.'
echo ""

# 4. Create New Homework
if [ ! -z "$CLASS_ID" ] && [ ! -z "$SUBJECT_ID" ]; then
    echo "4. Create New Homework..."
    DUE_DATE=$(date -v+7d +%Y-%m-%d 2>/dev/null || date -d "+7 days" +%Y-%m-%d)
    
    CREATE_RESPONSE=$(curl -s -X POST "${BASE_URL}/teacher/homework" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d "{
        \"title\": \"Test Homework - $(date +%Y%m%d%H%M%S)\",
        \"description\": \"This is a test homework assignment\",
        \"class_id\": \"${CLASS_ID}\",
        \"subject_id\": \"${SUBJECT_ID}\",
        \"due_date\": \"${DUE_DATE}\",
        \"priority\": \"high\"
      }")
    
    echo $CREATE_RESPONSE | jq '.'
    NEW_HOMEWORK_ID=$(echo $CREATE_RESPONSE | jq -r '.data.id // empty')
    
    if [ ! -z "$NEW_HOMEWORK_ID" ]; then
        HOMEWORK_ID=$NEW_HOMEWORK_ID
        echo "✓ Created homework: $HOMEWORK_ID"
    fi
    echo ""
fi

# 5. Get Homework Detail
if [ ! -z "$HOMEWORK_ID" ]; then
    echo "5. Get Homework Detail..."
    DETAIL_RESPONSE=$(curl -s -X GET "${BASE_URL}/teacher/homework/${HOMEWORK_ID}" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Accept: application/json")
    
    echo $DETAIL_RESPONSE | jq '.'
    STUDENT_ID=$(echo $DETAIL_RESPONSE | jq -r '.data.students[0].id // empty')
    echo ""
    
    # 6. Collect Homework
    if [ ! -z "$STUDENT_ID" ]; then
        echo "6. Collect Homework from Student..."
        COLLECT_RESPONSE=$(curl -s -X POST "${BASE_URL}/teacher/homework/${HOMEWORK_ID}/collect" \
          -H "Authorization: Bearer ${TOKEN}" \
          -H "Content-Type: application/json" \
          -H "Accept: application/json" \
          -d "{\"student_id\": \"${STUDENT_ID}\"}")
        
        echo $COLLECT_RESPONSE | jq '.'
        echo ""
        
        # 7. Verify Collection
        echo "7. Verify Homework Collected..."
        curl -s -X GET "${BASE_URL}/teacher/homework/${HOMEWORK_ID}" \
          -H "Authorization: Bearer ${TOKEN}" \
          -H "Accept: application/json" | jq '.data.students[] | select(.id == "'${STUDENT_ID}'")'
        echo ""
        
        # 8. Uncollect Homework
        echo "8. Uncollect Homework from Student..."
        UNCOLLECT_RESPONSE=$(curl -s -X POST "${BASE_URL}/teacher/homework/${HOMEWORK_ID}/uncollect" \
          -H "Authorization: Bearer ${TOKEN}" \
          -H "Content-Type: application/json" \
          -H "Accept: application/json" \
          -d "{\"student_id\": \"${STUDENT_ID}\"}")
        
        echo $UNCOLLECT_RESPONSE | jq '.'
        echo ""
        
        # 9. Verify Uncollection
        echo "9. Verify Homework Uncollected..."
        curl -s -X GET "${BASE_URL}/teacher/homework/${HOMEWORK_ID}" \
          -H "Authorization: Bearer ${TOKEN}" \
          -H "Accept: application/json" | jq '.data.students[] | select(.id == "'${STUDENT_ID}'")'
        echo ""
    fi
fi

# 10. Error Cases
echo "10. Test Error Cases..."
echo ""

echo "10a. Create Homework with Invalid Data..."
curl -s -X POST "${BASE_URL}/teacher/homework" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title": ""}' | jq '.'
echo ""

echo "10b. Get Non-existent Homework..."
curl -s -X GET "${BASE_URL}/teacher/homework/invalid-id" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq '.'
echo ""

echo "10c. Collect with Invalid Student ID..."
if [ ! -z "$HOMEWORK_ID" ]; then
    curl -s -X POST "${BASE_URL}/teacher/homework/${HOMEWORK_ID}/collect" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{"student_id": "invalid-id"}' | jq '.'
fi
echo ""

echo "=== Test Complete ==="
