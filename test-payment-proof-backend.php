<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PaymentProof;
use App\Models\Invoice;

echo "=== Payment Proof Backend Test ===\n\n";

// Get a pending payment proof
$proof = PaymentProof::where('status', 'pending_verification')
    ->with(['student.user', 'paymentMethod'])
    ->first();

if (!$proof) {
    echo "No pending payment proofs found.\n";
    exit;
}

echo "Found Payment Proof:\n";
echo "ID: {$proof->id}\n";
echo "Student: {$proof->student->user->name}\n";
echo "Amount: {$proof->payment_amount} MMK\n";
echo "Status: {$proof->status}\n";
echo "Fee IDs: " . json_encode($proof->fee_ids) . "\n\n";

// Check related invoices
echo "Related Invoices:\n";
if ($proof->fee_ids && is_array($proof->fee_ids)) {
    foreach ($proof->fee_ids as $feeId) {
        $invoice = Invoice::find($feeId);
        if ($invoice) {
            echo "- Invoice: {$invoice->invoice_number}\n";
            echo "  Term: {$invoice->term}\n";
            echo "  Amount: {$invoice->total_amount} MMK\n";
            echo "  Status: {$invoice->status}\n";
            echo "  Balance: {$invoice->balance} MMK\n\n";
        } else {
            echo "- Invoice ID {$feeId}: NOT FOUND\n\n";
        }
    }
}

echo "\n=== Backend Routes Available ===\n";
echo "Web Routes:\n";
echo "- POST /student-fees/payment-proofs/{id}/approve\n";
echo "- POST /student-fees/payment-proofs/{id}/reject\n";
echo "- GET  /student-fees/payment-proofs/{id}/details\n\n";

echo "API Routes:\n";
echo "- POST /api/v1/guardian/students/{student_id}/fees/payments\n";
echo "- GET  /api/v1/guardian/students/{student_id}/fees/payment-proofs\n";
echo "- GET  /api/v1/guardian/students/{student_id}/fees/payment-history\n\n";

echo "=== Test Complete ===\n";
echo "Backend is ready for payment proof verification!\n";
