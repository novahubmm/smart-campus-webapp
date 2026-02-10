<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\StudentProfile;
use App\Models\PaymentMethod;
use App\Repositories\Guardian\GuardianPaymentRepository;
use App\Services\Finance\PaymentProofService;

echo "=== Testing Payment Submission and Details Loading ===\n\n";

// Get student
$student = StudentProfile::whereHas('user', function($q) {
    $q->where('email', 'manyeinnyein@student.smartcampusedu.com');
})->first();

if (!$student) {
    echo "❌ Student not found!\n";
    exit(1);
}

echo "✓ Student found: {$student->user->name}\n";

// Get fee structure
$repo = app(GuardianPaymentRepository::class);
$feeStructure = $repo->getFeeStructure($student);

echo "✓ Fee structure loaded\n";
echo "  - Monthly fees: " . count($feeStructure['monthly_fees']) . "\n";
echo "  - Additional fees: " . count($feeStructure['additional_fees']) . "\n";
echo "  - Total: {$feeStructure['total_monthly']} MMK\n";

// Get first unpaid invoice
$allFees = array_merge($feeStructure['monthly_fees'], $feeStructure['additional_fees']);
if (empty($allFees)) {
    echo "❌ No unpaid invoices found!\n";
    exit(1);
}

$firstFee = $allFees[0];
echo "\n✓ Using invoice: {$firstFee['invoice_id']}\n";
echo "  - Fee: {$firstFee['name']}\n";
echo "  - Amount: {$firstFee['amount']} MMK\n";

// Get payment method
$paymentMethod = PaymentMethod::where('name', 'KBZ Bank')->first();
if (!$paymentMethod) {
    echo "❌ Payment method not found!\n";
    exit(1);
}

echo "\n✓ Payment method: {$paymentMethod->name}\n";

// Create test payment data
$paymentData = [
    'invoice_ids' => [$firstFee['invoice_id']],
    'payment_method_id' => $paymentMethod->id,
    'payment_amount' => $firstFee['amount'],
    'payment_months' => 1,
    'payment_date' => date('Y-m-d'),
    'receipt_image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlbaWmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKAP/2Q==',
    'notes' => 'Test payment submission - ' . date('Y-m-d H:i:s'),
];

echo "\n2. Submitting payment...\n";
try {
    $result = $repo->submitPayment($student, $paymentData);
    
    echo "✓ Payment submitted successfully\n";
    echo "  - Payment ID: {$result['payment_id']}\n";
    echo "  - Status: {$result['status']}\n";
    
    $paymentProofId = $result['payment_id'];
    
    echo "\n3. Testing PaymentProofService::getPaymentProofDetails()...\n";
    $service = app(PaymentProofService::class);
    $details = $service->getPaymentProofDetails($paymentProofId);
    
    echo "✓ Payment proof details loaded successfully\n";
    echo "  - Student: {$details['student']['name']}\n";
    echo "  - Amount: {$details['payment_amount']} MMK\n";
    echo "  - Payment Method: {$details['payment_method']}\n";
    echo "  - Number of Invoices: " . count($details['invoices']) . "\n";
    
    if (!empty($details['invoices'])) {
        echo "\n  Invoices:\n";
        foreach ($details['invoices'] as $invoice) {
            echo "    - {$invoice['invoice_number']}: {$invoice['amount']} MMK ({$invoice['fee_type']})\n";
        }
    } else {
        echo "\n  ⚠️  Warning: No invoices found in payment proof details\n";
    }
    
    echo "\n✓ All tests passed!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
