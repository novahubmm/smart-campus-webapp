<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Payment;
use App\Models\PaymentProof;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Payment Method ===\n\n";

// Get the most recent payment
$payment = Payment::with(['paymentMethod', 'paymentProof.paymentMethod', 'student.user'])
    ->orderBy('created_at', 'desc')
    ->first();

if (!$payment) {
    echo "No payments found\n";
    exit;
}

echo "Payment Number: {$payment->payment_number}\n";
echo "Student: {$payment->student->user->name}\n";
echo "Amount: " . number_format($payment->amount) . " MMK\n";
echo "Payment Date: {$payment->payment_date}\n\n";

echo "=== Payment Method Fields ===\n";
echo "payment_method (string): " . ($payment->payment_method ?? 'NULL') . "\n";
echo "payment_method_id (FK): " . ($payment->payment_method_id ?? 'NULL') . "\n\n";

if ($payment->paymentMethod) {
    echo "PaymentMethod Relationship:\n";
    echo "  ID: {$payment->paymentMethod->id}\n";
    echo "  Name: {$payment->paymentMethod->name}\n";
    echo "  Type: {$payment->paymentMethod->type}\n";
} else {
    echo "PaymentMethod Relationship: NULL\n";
}

echo "\n";

if ($payment->paymentProof) {
    echo "=== Payment Proof ===\n";
    echo "Proof ID: {$payment->paymentProof->id}\n";
    echo "Proof payment_method_id: {$payment->paymentProof->payment_method_id}\n";
    
    if ($payment->paymentProof->paymentMethod) {
        echo "Proof PaymentMethod:\n";
        echo "  Name: {$payment->paymentProof->paymentMethod->name}\n";
        echo "  Type: {$payment->paymentProof->paymentMethod->type}\n";
    }
}

echo "\n✓ Check complete\n";
