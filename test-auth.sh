#!/bin/bash

# Test authentication and teacher attendance endpoints
BASE_URL="http://192.168.100.114:8088/api/v1"

echo "=========================================="
echo "Testing Authentication & Teacher Attendance"
echo "=========================================="
echo ""

# Step 1: Login
echo "1. Testing Login..."
LOGIN_RESPONSE=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "login": "teacher1@smartcampusedu.com",
    "password": "password",
    "device_name": "Test",
    "remember_me": true
  }' \
  "$BASE_URL/auth/login")

echo "Login Response:"
echo "$LOGIN_RESPONSE" | python3 -m json.tool
echo ""

# Extract token
TOKEN=$(echo "$LOGIN_RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data.get('data', {}).get('token', ''))" 2>/dev/null)

if [ -z "$TOKEN" ]; then
    echo "❌ Failed to get token from login response"
    exit 1
fi

echo "✅ Token obtained: ${TOKEN:0:20}..."
echo ""

# Step 2: Test Teacher Attendance Check-In
echo "2. Testing Teacher Attendance Check-In..."
CHECKIN_RESPONSE=$(curl -s -X POST \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "latitude": 16.8661,
    "longitude": 96.1951,
    "device_info": "Test Device",
    "app_version": "1.0.0"
  }' \
  "$BASE_URL/teacher/attendance/check-in")

echo "Check-In Response:"
echo "$CHECKIN_RESPONSE" | python3 -m json.tool
echo ""

# Check status code
if echo "$CHECKIN_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Check-In Successful!"
elif echo "$CHECKIN_RESPONSE" | grep -q '401'; then
    echo "❌ 401 Unauthorized - Token not working"
    echo ""
    echo "Debugging Info:"
    echo "Token: $TOKEN"
    echo ""
    echo "Testing token with profile endpoint..."
    PROFILE_RESPONSE=$(curl -s -X GET \
      -H "Accept: application/json" \
      -H "Authorization: Bearer $TOKEN" \
      "$BASE_URL/auth/profile")
    echo "Profile Response:"
    echo "$PROFILE_RESPONSE" | python3 -m json.tool
else
    echo "⚠️  Unexpected response"
fi

echo ""
echo "=========================================="
echo "Test Complete"
echo "=========================================="
