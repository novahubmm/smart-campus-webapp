<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PaymentProof;

echo "=== Payment Proofs ===\n\n";

$proofs = PaymentProof::with(['student.user', 'paymentMethod'])
    ->latest()
    ->get();

if ($proofs->isEmpty()) {
    echo "No payment proofs found.\n";
} else {
    foreach ($proofs as $proof) {
        echo "ID: {$proof->id}\n";
        echo "Student: {$proof->student->user->name} ({$proof->student->student_identifier})\n";
        echo "Amount: {$proof->payment_amount} MMK\n";
        echo "Payment Method: {$proof->paymentMethod->name}\n";
        echo "Payment Date: {$proof->payment_date->format('Y-m-d')}\n";
        echo "Status: {$proof->status}\n";
        echo "Receipt: {$proof->receipt_image}\n";
        echo "Notes: {$proof->notes}\n";
        echo "Fee IDs: " . json_encode($proof->fee_ids) . "\n";
        echo "Submitted: {$proof->created_at->format('Y-m-d H:i:s')}\n";
        echo str_repeat('-', 80) . "\n\n";
    }
}
