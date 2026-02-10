#!/bin/bash

# Get student and subject IDs from database
STUDENT_ID=$(php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$student = App\Models\GuardianProfile::first()->students()->first();
echo \$student->id ?? 'none';
")

SUBJECT_ID=$(php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$student = App\Models\GuardianProfile::first()->students()->first();
\$subject = App\Models\GradeSubject::where('grade_id', \$student->grade_id)->first();
echo \$subject->subject_id ?? 'none';
")

echo "Testing Curriculum API Route"
echo "=============================="
echo ""
echo "Student ID: $STUDENT_ID"
echo "Subject ID: $SUBJECT_ID"
echo ""

# Test without authentication (should get 401)
echo "Test 1: Without authentication (expect 401)"
echo "--------------------------------------------"
curl -s -w "\nHTTP Status: %{http_code}\n" \
  -H "Accept: application/json" \
  "http://localhost:8088/api/v1/guardian/students/$STUDENT_ID/curriculum/subjects/$SUBJECT_ID"

echo ""
echo ""

# Get token
echo "Getting authentication token..."
TOKEN=$(php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\$guardian = App\Models\GuardianProfile::first();
\$user = \$guardian->user;
\$token = \$user->createToken('test')->plainTextToken;
echo \$token;
")

echo "Token obtained"
echo ""

# Test with authentication
echo "Test 2: With authentication (expect 200)"
echo "--------------------------------------------"
curl -s -w "\nHTTP Status: %{http_code}\n" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  "http://localhost:8088/api/v1/guardian/students/$STUDENT_ID/curriculum/subjects/$SUBJECT_ID" | jq '.'

echo ""
