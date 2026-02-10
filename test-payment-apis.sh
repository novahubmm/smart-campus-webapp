#!/bin/bash

# Payment API Test Script
# Guardian: Ko Nyein Chan
# Student: Maung Kyaw Kyaw
# Generated: 2026-02-10 17:17:19

TOKEN="b6f048c7-6085-40e2-aecc-f607e76aa2c4|R314B9Bd95lopL993raKYhMRVprmhtFSCxTTTuLre615ece1"
BASE_URL="http://192.168.100.114:8088/api/v1"
STUDENT_ID="b0ae26d7-0cb6-42db-9e90-4a057d27c50b"

echo "Testing Payment APIs..."
echo "═══════════════════════════════════════════════════════════════"

echo "1️⃣  Testing Fee Structure API..."
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .

echo -e "\n2️⃣  Testing Payment Options API..."
curl -X GET "${BASE_URL}/guardian/payment-options" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .

echo -e "\n3️⃣  Testing Payment Methods API..."
curl -X GET "${BASE_URL}/guardian/payment-methods" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .

echo -e "\n5️⃣  Testing Payment History API..."
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/payment-history" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .

echo "═══════════════════════════════════════════════════════════════"
echo "✅ All tests completed!"
