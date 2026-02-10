<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentProfile;
use App\Repositories\Guardian\GuardianPaymentRepository;

echo "=== Testing Payment Promotions API ===\n\n";

// Get a student
$student = StudentProfile::with(['user', 'grade'])->first();

if (!$student) {
    echo "❌ No student found in database\n";
    exit(1);
}

echo "✓ Testing with student: {$student->user->name} (ID: {$student->id})\n\n";

// Test payment options endpoint
$repository = new GuardianPaymentRepository();
$paymentOptions = $repository->getPaymentOptions();

echo "=== Payment Options Response ===\n";
echo json_encode($paymentOptions, JSON_PRETTY_PRINT) . "\n\n";

// Verify structure
if (isset($paymentOptions['options']) && is_array($paymentOptions['options'])) {
    echo "✓ Payment options returned successfully\n";
    echo "✓ Found " . count($paymentOptions['options']) . " payment duration options\n\n";
    
    foreach ($paymentOptions['options'] as $option) {
        $badge = $option['badge'] ?? 'No discount';
        echo "  - {$option['months']} month(s): {$option['discount_percent']}% discount ({$badge})\n";
    }
} else {
    echo "❌ Invalid payment options structure\n";
}

echo "\n=== Test Complete ===\n";
