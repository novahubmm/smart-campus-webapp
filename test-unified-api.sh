#!/bin/bash

echo "🧪 Testing Unified API Endpoints"
echo "================================"
echo ""

BASE_URL="http://localhost:8088/api/v1"

# Test 1: Teacher Login
echo "📝 Test 1: Teacher Login"
echo "------------------------"
TEACHER_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "teacher1@smartcampusedu.com",
    "password": "password",
    "device_name": "test_device"
  }')

echo "$TEACHER_RESPONSE" | jq '.'
TEACHER_TOKEN=$(echo "$TEACHER_RESPONSE" | jq -r '.data.token // empty')
USER_TYPE=$(echo "$TEACHER_RESPONSE" | jq -r '.data.user_type // empty')

if [ ! -z "$TEACHER_TOKEN" ]; then
    echo "✅ Teacher login successful! User type: $USER_TYPE"
else
    echo "❌ Teacher login failed"
fi

echo ""
echo "================================"
echo ""

# Test 2: Guardian Login
echo "📝 Test 2: Guardian Login"
echo "------------------------"
GUARDIAN_RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "guardian1@smartcampusedu.com",
    "password": "password",
    "device_name": "test_device"
  }')

echo "$GUARDIAN_RESPONSE" | jq '.'
GUARDIAN_TOKEN=$(echo "$GUARDIAN_RESPONSE" | jq -r '.data.token // empty')
GUARDIAN_USER_TYPE=$(echo "$GUARDIAN_RESPONSE" | jq -r '.data.user_type // empty')

if [ ! -z "$GUARDIAN_TOKEN" ]; then
    echo "✅ Guardian login successful! User type: $GUARDIAN_USER_TYPE"
else
    echo "❌ Guardian login failed"
fi

echo ""
echo "================================"
echo ""

# Test 3: Get Teacher Profile
if [ ! -z "$TEACHER_TOKEN" ]; then
    echo "📝 Test 3: Get Teacher Profile"
    echo "------------------------------"
    curl -s -X GET "$BASE_URL/auth/profile" \
      -H "Authorization: Bearer $TEACHER_TOKEN" \
      -H "Accept: application/json" | jq '.'
    echo ""
fi

echo ""
echo "================================"
echo ""

# Test 4: Get Teacher Dashboard
if [ ! -z "$TEACHER_TOKEN" ]; then
    echo "📝 Test 4: Get Teacher Dashboard"
    echo "--------------------------------"
    curl -s -X GET "$BASE_URL/dashboard" \
      -H "Authorization: Bearer $TEACHER_TOKEN" \
      -H "Accept: application/json" | jq '.'
    echo ""
fi

echo ""
echo "================================"
echo ""

# Test 5: Get Guardian Dashboard
if [ ! -z "$GUARDIAN_TOKEN" ]; then
    echo "📝 Test 5: Get Guardian Dashboard"
    echo "---------------------------------"
    curl -s -X GET "$BASE_URL/dashboard" \
      -H "Authorization: Bearer $GUARDIAN_TOKEN" \
      -H "Accept: application/json" | jq '.'
    echo ""
fi

echo ""
echo "================================"
echo "✅ All tests completed!"
echo "================================"
