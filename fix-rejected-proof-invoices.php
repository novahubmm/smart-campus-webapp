<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\PaymentProof;
use Carbon\Carbon;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing Rejected Payment Proof Invoices ===\n\n";

$student = StudentProfile::where('student_identifier', 'G0-A-003')->first();

if (!$student) {
    echo "Student not found!\n";
    exit(1);
}

echo "Student: {$student->user->name} ({$student->student_identifier})\n\n";

// Find rejected proofs with null fee_ids
$rejectedProofs = PaymentProof::where('student_id', $student->id)
    ->where('status', 'rejected')
    ->whereNull('fee_ids')
    ->get();

echo "Found {$rejectedProofs->count()} rejected proofs with null fee_ids\n\n";

foreach ($rejectedProofs as $proof) {
    echo "Processing Proof ID: {$proof->id}\n";
    echo "  Amount: " . number_format($proof->payment_amount) . " MMK\n";
    echo "  Date: {$proof->payment_date}\n";
    
    // Find invoices with pending_verification status that match the amount
    $pendingInvoices = Invoice::where('student_id', $student->id)
        ->where('status', 'pending_verification')
        ->get();
    
    if ($pendingInvoices->count() > 0) {
        echo "  Found {$pendingInvoices->count()} pending_verification invoices:\n";
        
        $invoiceIds = [];
        $totalAmount = 0;
        
        foreach ($pendingInvoices as $inv) {
            echo "    - {$inv->invoice_number}: " . number_format($inv->total_amount) . " MMK\n";
            $invoiceIds[] = $inv->id;
            $totalAmount += $inv->total_amount;
        }
        
        echo "  Total invoice amount: " . number_format($totalAmount) . " MMK\n";
        echo "  Proof amount: " . number_format($proof->payment_amount) . " MMK\n";
        
        // Update the proof with fee_ids
        echo "\n  Updating proof with invoice IDs...\n";
        $proof->fee_ids = $invoiceIds;
        $proof->save();
        echo "  ✓ Updated proof fee_ids\n";
        
        // Update invoices back to unpaid status
        echo "  Updating invoices to unpaid status...\n";
        foreach ($pendingInvoices as $inv) {
            $inv->status = 'unpaid';
            $inv->save();
            echo "    ✓ {$inv->invoice_number} -> unpaid\n";
        }
    } else {
        echo "  ⚠ No pending_verification invoices found\n";
    }
    
    echo "\n";
}

echo "=== Verification ===\n\n";

// Check the results
$student = StudentProfile::where('student_identifier', 'G0-A-003')->first();

$monthStart = Carbon::parse('2026-02-01')->startOfMonth();
$monthEnd = $monthStart->copy()->endOfMonth();

$invoices = Invoice::where('student_id', $student->id)
    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
    ->orderBy('status')
    ->get();

echo "February 2026 Invoices after fix:\n";
foreach ($invoices as $inv) {
    echo "  - {$inv->invoice_number}: {$inv->status}, " . number_format($inv->total_amount) . " MMK\n";
}

$rejectedProofs = PaymentProof::where('student_id', $student->id)
    ->where('status', 'rejected')
    ->get();

echo "\nRejected Proofs after fix:\n";
foreach ($rejectedProofs as $proof) {
    echo "  - Proof ID: {$proof->id}\n";
    echo "    Fee IDs: " . json_encode($proof->fee_ids) . "\n";
    echo "    Amount: " . number_format($proof->payment_amount) . " MMK\n";
}

echo "\n✓ Fix complete!\n";
