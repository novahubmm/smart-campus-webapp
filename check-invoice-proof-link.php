<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\PaymentProof;
use Carbon\Carbon;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Invoice-PaymentProof Relationship ===\n\n";

$student = StudentProfile::where('student_identifier', 'G0-A-003')->first();

// Check invoices with pending_verification status
$pendingInvoices = Invoice::where('student_id', $student->id)
    ->where('status', 'pending_verification')
    ->get();

echo "Invoices with pending_verification status: {$pendingInvoices->count()}\n";
foreach ($pendingInvoices as $inv) {
    echo "  Invoice: {$inv->invoice_number}\n";
    echo "    ID: {$inv->id}\n";
    echo "    Status: {$inv->status}\n";
    echo "    Amount: " . number_format($inv->total_amount) . " MMK\n";
    
    // Check if there's a payment_proof_id column
    if (isset($inv->payment_proof_id)) {
        echo "    Payment Proof ID: {$inv->payment_proof_id}\n";
        $proof = PaymentProof::find($inv->payment_proof_id);
        if ($proof) {
            echo "    Proof Status: {$proof->status}\n";
            echo "    Proof Amount: " . number_format($proof->payment_amount) . " MMK\n";
        }
    }
    echo "\n";
}

// Check all rejected proofs and see if we can find related invoices
echo "\n=== Checking Rejected Proofs ===\n";
$rejectedProofs = PaymentProof::where('student_id', $student->id)
    ->where('status', 'rejected')
    ->get();

foreach ($rejectedProofs as $proof) {
    echo "Proof ID: {$proof->id}\n";
    echo "  Amount: " . number_format($proof->payment_amount) . " MMK\n";
    echo "  Date: {$proof->payment_date}\n";
    echo "  Rejection: {$proof->rejection_reason}\n";
    echo "  Fee IDs: " . json_encode($proof->fee_ids) . "\n";
    
    // Try to find invoices that might be linked
    $linkedInvoices = Invoice::where('student_id', $student->id)
        ->where('status', 'pending_verification')
        ->get();
    
    if ($linkedInvoices->count() > 0) {
        echo "  Possible linked invoices (pending_verification):\n";
        foreach ($linkedInvoices as $inv) {
            echo "    - {$inv->invoice_number} ({$inv->id})\n";
        }
    }
    echo "\n";
}

// Check the payment_proofs table structure
echo "\n=== PaymentProof Table Columns ===\n";
$proof = PaymentProof::first();
if ($proof) {
    echo "Columns: " . implode(', ', array_keys($proof->getAttributes())) . "\n";
}

// Check the invoices table structure
echo "\n=== Invoice Table Columns ===\n";
$invoice = Invoice::first();
if ($invoice) {
    $columns = array_keys($invoice->getAttributes());
    echo "Columns: " . implode(', ', $columns) . "\n";
    
    if (in_array('payment_proof_id', $columns)) {
        echo "\n✓ Invoices have payment_proof_id column\n";
    } else {
        echo "\n✗ Invoices do NOT have payment_proof_id column\n";
    }
}
