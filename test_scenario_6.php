<?php

use App\Models\StudentProfile;
use App\Models\PaymentMethod;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\InvoiceFee;
use App\Services\PaymentSystem\InvoiceService;
use App\Services\PaymentSystem\PaymentService;
use App\Services\PaymentSystem\PaymentVerificationService;
use App\Services\PaymentSystem\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mockery as m;

echo "--- STARTING SCENARIO 6 TEST (With Mocks) ---\n";

// 1. Setup Student & Payment Method
$student = StudentProfile::first();
if (!$student) { echo "ERROR: No student found.\n"; exit; }
$paymentMethod = \App\Models\PaymentMethod::first() ?? \App\Models\PaymentSystem\PaymentMethod::first();
if (!$paymentMethod) { echo "ERROR: No payment method found.\n"; exit; }

echo "Using Student: {$student->id}\n";

// 2. Cleanup
echo "Cleaning up old data...\n";
$invoices = Invoice::where('student_id', $student->id)->pluck('id');
if ($invoices->count() > 0) {
    InvoiceFee::whereIn('invoice_id', $invoices)->delete();
    Payment::whereIn('invoice_id', $invoices)->delete();
    Invoice::whereIn('id', $invoices)->delete();
}
echo "Cleanup done.\n";

// 3. Manually Create Invoice
echo "Creating invoice manually...\n";
$invoice = Invoice::create([
    'invoice_number' => 'INV-TEST-' . time(),
    'student_id' => $student->id,
    'academic_year' => '2025-2026',
    'total_amount' => 47000,
    'paid_amount' => 0,
    'remaining_amount' => 47000,
    'due_date' => now()->addDays(30),
    'status' => 'pending',
    'invoice_type' => 'monthly',
]);

// Create Fees
$schoolFee = InvoiceFee::create([
    'invoice_id' => $invoice->id,
    'fee_name' => 'School Fee',
    'fee_name_mm' => 'ကျောင်းလခ',
    'amount' => 10000,
    'paid_amount' => 0,
    'remaining_amount' => 10000,
    'due_date' => now()->addDays(30),
    'status' => 'unpaid',
    'supports_payment_period' => true,
]);

InvoiceFee::create([
    'invoice_id' => $invoice->id,
    'fee_name' => 'Transportation Fee',
    'amount' => 25000,
    'paid_amount' => 0,
    'remaining_amount' => 25000,
    'due_date' => now()->addDays(30),
    'status' => 'unpaid',
]);

InvoiceFee::create([
    'invoice_id' => $invoice->id,
    'fee_name' => 'Book Fee',
    'amount' => 12000,
    'paid_amount' => 0,
    'remaining_amount' => 12000,
    'due_date' => now()->addDays(30),
    'status' => 'unpaid',
]);

echo "Invoice Created: {$invoice->invoice_number}\n";

// 5. Simulate Partial Payment
$payAmount = 5000;
echo "Submitting Partial Payment of {$payAmount} for School Fee...\n";

$data = [
    'invoice_id' => $invoice->id,
    'payment_amount' => $payAmount,
    'payment_type' => 'partial',
    'payment_method_id' => $paymentMethod->id,
    'payment_date' => now()->format('Y-m-d'),
    'receipt_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
    'fee_payment_details' => [
        [
            'invoice_fee_id' => $schoolFee->id,
            'paid_amount' => $payAmount
        ]
    ]
];

try {
    // Mock NotificationService
    $mockNotification = m::mock(NotificationService::class);
    $mockNotification->shouldReceive('notifyGuardianOfVerification')->andReturn(true);
    
    // Inject mock into PaymentSystem services via app instance binding or just instantiate manually
    // Since verifyPayment uses $this->notificationService injected in constructor, manual instantiation is safer here
    // But PaymentService also uses NotificationService maybe? No, checking constructor...
    // PaymentService doesn't seem to use notification service based on verification code I saw earlier.
    
    $paymentService = app(PaymentService::class);
    $payment = $paymentService->submitPayment($data);
    echo "Payment Submitted: {$payment->payment_number}\n";
    
    // 6. Verify Payment
    echo "Verifying Payment...\n";
    
    // Manually instantiate Verification Service with mocked notification
    $verificationService = new PaymentVerificationService($mockNotification);
    
    $admin = User::first() ?? User::factory()->create();
    
    $verificationService->verifyPayment($payment, $admin);
    echo "Payment Verified.\n";
    
    // 7. Check Remaining Balance Invoice
    $invoice->refresh();
    echo "Original Invoice Status: {$invoice->status}\n";
    
    $remainingInvoice = Invoice::where('parent_invoice_id', $invoice->id)->first();
    
    if ($remainingInvoice) {
        echo "SUCCESS: Remaining Balance Invoice Found!\n";
        echo "New Invoice Number: {$remainingInvoice->invoice_number}\n";
        echo "New Invoice Amount: {$remainingInvoice->total_amount}\n";
        foreach($remainingInvoice->fees as $remFee) {
            echo " - {$remFee->fee_name}: {$remFee->amount}\n";
        }
    } else {
        echo "FAILURE: Remaining Balance Invoice NOT created.\n";
    }
    
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
//    echo $e->getTraceAsString();
}

echo "--- TEST COMPLETE ---\n";
