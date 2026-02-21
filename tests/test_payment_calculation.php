<?php

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentPromotion;
use App\Services\PaymentSystem\PaymentProcessingService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock Auth
$user = User::first();
Auth::login($user);

// Create a mock invoice with fees
$invoice = Invoice::create([
    'student_id' => 1, // Assuming studentexists
    'invoice_number' => 'TEST-INV-001',
    'total_amount' => 100000,
    'paid_amount' => 0,
    'remaining_amount' => 100000,
    'due_date' => now()->addDays(30),
    'status' => 'unpaid',
    'academic_year' => '2025-2026',
    'invoice_type' => 'monthly'
]);

// 1. School Fee (Supports Discount)
$schoolFee = InvoiceFee::create([
    'invoice_id' => $invoice->id,
    'fee_type_id' => 1,
    'fee_name' => 'School Fee',
    'fee_name_mm' => 'School Fee MM',
    'amount' => 50000,
    'paid_amount' => 0,
    'remaining_amount' => 50000,
    'due_date' => now()->addDays(30),
]);

// 2. Other Fee (No Discount)
$otherFee = InvoiceFee::create([
    'invoice_id' => $invoice->id,
    'fee_type_id' => 2,
    'fee_name' => 'Bus Fee',
    'fee_name_mm' => 'Bus Fee MM',
    'amount' => 20000,
    'paid_amount' => 0,
    'remaining_amount' => 20000,
    'due_date' => now()->addDays(30),
]);

// Setup Promotion: 3 months = 5% off
PaymentPromotion::updateOrCreate(
    ['months' => 3],
    ['discount_percent' => 5, 'is_active' => true, 'name' => '3 Months Promo']
);

// Payment Data: School Fee for 3 months, Other Fee for 1 month
$paymentData = [
    'payment_type' => 'full',
    'payment_months' => 1, // Global default, ignored for specific fees
    'fee_payment_months' => [
        $schoolFee->id => 3,
        $otherFee->id => 1
    ],
    'payment_method_id' => 1,
    'payment_date' => now(),
];

// Calculation Expectation:
// School Fee: 50,000 * 3 = 150,000
// Discount: 5% of 150,000 = 7,500
// School Fee Final: 142,500

// Other Fee: 20,000 * 1 = 20,000
// Discount: 0
// Other Fee Final: 20,000

// Total Payment: 162,500
// Total Discount: 7,500

echo "Starting Test...\n";
$service = app(PaymentProcessingService::class);
$result = $service->processPayment($invoice, $paymentData);

if ($result['success']) {
    $payment = $result['payment'];
    echo "Payment ID: " . $payment->id . "\n";
    echo "Total Amount Paid: " . $payment->payment_amount . "\n";
    echo "Discount Applied: " . $result['discount_applied'] . "\n";
    
    if ($payment->payment_amount == 162500 && $result['discount_applied'] == 7500) {
        echo "SUCCESS: Calculation matches expectation.\n";
    } else {
        echo "FAILURE: Calculation Mismatch!\n";
        echo "Expected: 162500, Got: " . $payment->payment_amount . "\n";
        echo "Expected Discount: 7500, Got: " . $result['discount_applied'] . "\n";
    }
} else {
    echo "Error: " . $result['error'] . "\n";
}

// Cleanup
$invoice->delete(); // Cascades delete
