#!/bin/bash

# Payment API Complete Test Suite
# Generated: 2026-02-10 17:31:52

TOKEN="9562693d-b03a-4071-9903-0edd3c03dbe8|eTyqCTqpPn4DGn2beokunrWEjPGg0nlyHCNAyTGG96746694"
BASE_URL="http://192.168.100.114:8088/api/v1"
STUDENT_ID="b0ae26d7-0cb6-42db-9e90-4a057d27c50b"
PAYMENT_METHOD_ID="019c46ab-d358-7343-980e-71148f85b465"
INVOICE_ID="019c4720-124f-719a-a4d4-976772777885"

echo "═══════════════════════════════════════════════════════════════"
echo "🧪 Running Payment API Test Suite"
echo "═══════════════════════════════════════════════════════════════"
echo ""

echo "TEST 1: Get Fee Structure"
curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .
echo ""

echo "TEST 2: Get Payment Options"
curl -s -X GET "${BASE_URL}/guardian/payment-options" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .
echo ""

echo "TEST 3: Get Payment Methods"
curl -s -X GET "${BASE_URL}/guardian/payment-methods" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .
echo ""

echo "TEST 4: Submit Single Payment"
curl -s -X POST "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/payments" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice_ids": ["'${INVOICE_ID}'"],
    "payment_method_id": "'${PAYMENT_METHOD_ID}'",
    "payment_amount": 50000.00,
    "payment_months": 1,
    "payment_date": "2026-02-10",
    "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=",
    "notes": "Test payment"
  }' | jq .
echo ""

echo "TEST 5: Get Payment History"
curl -s -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/payment-history" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Accept: application/json" | jq .
echo ""

echo "═══════════════════════════════════════════════════════════════"
echo "✅ Test suite completed!"
echo "═══════════════════════════════════════════════════════════════"
