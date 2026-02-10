<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\PaymentProof;
use Carbon\Carbon;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Controller Query for February 2026 ===\n\n";

$selectedMonth = '2026-02';
$monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth();
$monthEnd = $monthStart->copy()->endOfMonth();

// Get rejected payment proof invoice IDs for the selected month
$rejectedProofInvoiceIds = PaymentProof::where('status', 'rejected')
    ->whereBetween('payment_date', [$monthStart, $monthEnd])
    ->get()
    ->pluck('fee_ids')
    ->flatten()
    ->filter()
    ->unique()
    ->toArray();

echo "Rejected proof invoice IDs in Feb 2026: " . count($rejectedProofInvoiceIds) . "\n";
if (!empty($rejectedProofInvoiceIds)) {
    echo "IDs:\n";
    foreach ($rejectedProofInvoiceIds as $id) {
        $inv = Invoice::find($id);
        if ($inv) {
            echo "  - {$inv->invoice_number} ({$id})\n";
        }
    }
}
echo "\n";

// Query unpaid invoices (mimicking controller logic)
$unpaidInvoicesQuery = Invoice::with([
    'student.user', 
    'student.grade', 
    'student.classModel', 
    'feeStructure.feeType'
])
->where(function ($query) use ($rejectedProofInvoiceIds) {
    $query->whereIn('status', ['unpaid', 'sent'])
          ->orWhereIn('id', $rejectedProofInvoiceIds);
})
->whereBetween('invoice_date', [$monthStart, $monthEnd])
->whereHas('student', function ($q) {
    $q->where('status', 'active');
});

// Filter for student G0-A-003
$student = StudentProfile::where('student_identifier', 'G0-A-003')->first();
$unpaidInvoicesQuery->where('student_id', $student->id);

$unpaidInvoices = $unpaidInvoicesQuery->orderBy('invoice_date', 'desc')->get();

echo "Unpaid invoices for G0-A-003 (using controller logic): {$unpaidInvoices->count()}\n\n";

// Get rejected proofs by invoice ID
$rejectedProofsByInvoice = PaymentProof::where('status', 'rejected')
    ->whereBetween('payment_date', [$monthStart, $monthEnd])
    ->with('paymentMethod')
    ->get()
    ->flatMap(function ($proof) {
        return collect($proof->fee_ids)->mapWithKeys(function ($invoiceId) use ($proof) {
            return [$invoiceId => $proof];
        });
    });

echo "Rejected proofs by invoice:\n";
foreach ($rejectedProofsByInvoice as $invoiceId => $proof) {
    $inv = Invoice::find($invoiceId);
    echo "  - Invoice: {$inv->invoice_number} -> Proof: {$proof->id}\n";
    echo "    Rejection: {$proof->rejection_reason}\n";
}
echo "\n";

echo "=== Invoice List (as it will appear in the UI) ===\n\n";

foreach ($unpaidInvoices as $invoice) {
    $hasRejectedProof = isset($rejectedProofsByInvoice[$invoice->id]);
    $rejectedProof = $hasRejectedProof ? $rejectedProofsByInvoice[$invoice->id] : null;
    
    $marker = $hasRejectedProof ? '❌ [REJECTED PROOF]' : '';
    
    echo "{$invoice->invoice_number}\n";
    echo "  Student: {$invoice->student->user->name} ({$invoice->student->student_identifier})\n";
    echo "  Fee Type: {$invoice->feeStructure->feeType->name}\n";
    echo "  Amount: " . number_format($invoice->total_amount, 0) . " MMK\n";
    echo "  Status: {$invoice->status} {$marker}\n";
    
    if ($hasRejectedProof && $rejectedProof->rejection_reason) {
        echo "  Rejection Reason: {$rejectedProof->rejection_reason}\n";
    }
    
    echo "\n";
}

echo "✓ Test complete!\n";
