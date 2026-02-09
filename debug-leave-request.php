<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentProfile;
use App\Models\LeaveRequest;

$studentId = '3a48862e-ed0e-4991-b2c7-5c4953ed7227';
$requestId = 'c586d56a-8184-444c-ab32-fbed2a01ec84';

echo "=== Debug Leave Request API ===\n\n";

// Check student
$student = StudentProfile::find($studentId);
if (!$student) {
    echo "❌ Student not found\n";
    exit(1);
}

echo "✅ Student found: {$student->user->name}\n";
echo "   User ID: {$student->user_id}\n\n";

// Check leave request
$leaveRequest = LeaveRequest::find($requestId);
if (!$leaveRequest) {
    echo "❌ Leave request not found\n";
    exit(1);
}

echo "✅ Leave request found\n";
echo "   ID: {$leaveRequest->id}\n";
echo "   Type: {$leaveRequest->leave_type}\n";
echo "   User ID: {$leaveRequest->user_id}\n";
echo "   User Type: {$leaveRequest->user_type}\n";
echo "   Status: {$leaveRequest->status}\n\n";

// Check if they match
if ($leaveRequest->user_id === $student->user_id) {
    echo "✅ Leave request belongs to student\n";
} else {
    echo "❌ Leave request does NOT belong to student\n";
    echo "   Leave request user_id: {$leaveRequest->user_id}\n";
    echo "   Student user_id: {$student->user_id}\n";
}

echo "\n=== Testing Repository Method ===\n\n";

try {
    $repo = new App\Repositories\Guardian\GuardianLeaveRequestRepository();
    $result = $repo->getLeaveRequestDetailForStudent($requestId, $studentId);
    echo "✅ Repository method works!\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "❌ Repository method failed: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n";
}
