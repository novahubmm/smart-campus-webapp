<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Finance\PaymentProofService;
use App\Models\PaymentProof;

echo "=== Testing Payment Proof Details Endpoint ===\n\n";

// Get the payment proof ID from the error log
$paymentProofId = '019c4736-7d5a-7348-8e3e-0889d2b3fb0c';

echo "1. Checking if payment proof exists...\n";
$paymentProof = PaymentProof::find($paymentProofId);

if (!$paymentProof) {
    echo "❌ Payment proof not found!\n";
    exit(1);
}

echo "✓ Payment proof found\n";
echo "  - Student: {$paymentProof->student->user->name}\n";
echo "  - Amount: {$paymentProof->payment_amount} MMK\n";
echo "  - Status: {$paymentProof->status}\n";
echo "  - Date: {$paymentProof->payment_date}\n";
echo "  - Invoice IDs: " . json_encode($paymentProof->fee_ids) . "\n\n";

echo "2. Testing PaymentProofService::getPaymentProofDetails()...\n";
try {
    $service = app(PaymentProofService::class);
    $details = $service->getPaymentProofDetails($paymentProofId);
    
    echo "✓ Service method executed successfully\n";
    echo "  - Student: {$details['student']['name']}\n";
    echo "  - Amount: {$details['payment_amount']} MMK\n";
    echo "  - Payment Method: {$details['payment_method']}\n";
    echo "  - Number of Invoices: " . count($details['invoices']) . "\n";
    
    if (!empty($details['invoices'])) {
        echo "\n  Invoices:\n";
        foreach ($details['invoices'] as $invoice) {
            echo "    - {$invoice['invoice_number']}: {$invoice['amount']} MMK ({$invoice['fee_type']})\n";
        }
    }
    
    echo "\n✓ Payment proof details loaded successfully!\n";
    echo "\n3. Full response structure:\n";
    echo json_encode($details, JSON_PRETTY_PRINT) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
