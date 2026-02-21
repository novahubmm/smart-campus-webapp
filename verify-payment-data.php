<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Payment Data Verification ===\n\n";

// Check Payment Methods
echo "1. Payment Methods:\n";
$paymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
foreach ($paymentMethods as $method) {
    echo "   - {$method->name} ({$method->type}) - Active: " . ($method->is_active ? 'Yes' : 'No') . "\n";
}
echo "\n";

// Check if Cash payment method exists
$cashMethod = \App\Models\PaymentMethod::where('name', 'Cash')->first();
if ($cashMethod) {
    echo "✓ Cash payment method exists (ID: {$cashMethod->id})\n\n";
} else {
    echo "✗ Cash payment method NOT found!\n\n";
}

// Check PaymentSystem Payments
echo "2. PaymentSystem Payments (verified):\n";
$payments = \App\Models\PaymentSystem\Payment::where('status', 'verified')
    ->with(['student.user', 'paymentMethod'])
    ->latest('payment_date')
    ->take(10)
    ->get();

if ($payments->count() > 0) {
    foreach ($payments as $payment) {
        $studentName = $payment->student?->user?->name ?? 'N/A';
        $methodName = $payment->paymentMethod?->name ?? 'N/A';
        $amount = number_format($payment->payment_amount, 0);
        $date = $payment->payment_date?->format('M j, Y') ?? 'N/A';
        echo "   - {$payment->payment_number}: {$studentName} - {$amount} MMK via {$methodName} on {$date}\n";
    }
} else {
    echo "   No verified payments found.\n";
}
echo "\n";

// Check statistics
echo "3. Statistics:\n";
$totalVerifiedPayments = \App\Models\PaymentSystem\Payment::where('status', 'verified')->count();
$totalAmount = \App\Models\PaymentSystem\Payment::where('status', 'verified')->sum('payment_amount');
echo "   - Total verified payments: {$totalVerifiedPayments}\n";
echo "   - Total amount collected: " . number_format($totalAmount, 0) . " MMK\n";
echo "\n";

// Check for February 2026 specifically
echo "4. February 2026 Payments:\n";
$febStart = \Carbon\Carbon::parse('2026-02-01')->startOfMonth();
$febEnd = $febStart->copy()->endOfMonth();
$febPayments = \App\Models\PaymentSystem\Payment::where('status', 'verified')
    ->whereBetween('payment_date', [$febStart, $febEnd])
    ->with(['student.user', 'paymentMethod'])
    ->get();

if ($febPayments->count() > 0) {
    $febTotal = $febPayments->sum('payment_amount');
    echo "   - Total payments: {$febPayments->count()}\n";
    echo "   - Total amount: " . number_format($febTotal, 0) . " MMK\n";
    foreach ($febPayments as $payment) {
        $studentName = $payment->student?->user?->name ?? 'N/A';
        $methodName = $payment->paymentMethod?->name ?? 'N/A';
        $amount = number_format($payment->payment_amount, 0);
        echo "     • {$studentName}: {$amount} MMK via {$methodName}\n";
    }
} else {
    echo "   No payments found for February 2026.\n";
}
echo "\n";

echo "=== Verification Complete ===\n";
