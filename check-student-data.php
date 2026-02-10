<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\PaymentProof;
use Carbon\Carbon;

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "=== Checking Student G0-A-003 ===\n\n";

    // Find student
    $student = StudentProfile::where('student_identifier', 'G0-A-003')->first();

    if (!$student) {
        echo "Student not found!\n";
        exit(1);
    }

    echo "Student ID: {$student->id}\n";
    echo "Student Name: {$student->user->name}\n\n";

    // Check February 2026 invoices
    $monthStart = Carbon::parse('2026-02-01')->startOfMonth();
    $monthEnd = $monthStart->copy()->endOfMonth();

    $invoices = Invoice::where('student_id', $student->id)
        ->whereBetween('invoice_date', [$monthStart, $monthEnd])
        ->get();

    echo "February 2026 Invoices: {$invoices->count()}\n";
    foreach ($invoices as $inv) {
        echo "  - {$inv->invoice_number}: {$inv->status}, " . number_format($inv->total_amount) . " MMK\n";
    }
    echo "\n";

    // Check all payment proofs
    $proofs = PaymentProof::where('student_id', $student->id)->get();
    echo "Total Payment Proofs: {$proofs->count()}\n";
    foreach ($proofs as $proof) {
        echo "  - ID: {$proof->id}, Status: {$proof->status}, Amount: " . number_format($proof->payment_amount) . " MMK\n";
        echo "    Date: {$proof->payment_date}, Fee IDs: " . json_encode($proof->fee_ids) . "\n";
        if ($proof->status === 'rejected') {
            echo "    ❌ REJECTED: {$proof->rejection_reason}\n";
        }
    }
    echo "\n";

    // Check rejected proofs in Feb 2026
    $rejectedProofs = PaymentProof::where('student_id', $student->id)
        ->where('status', 'rejected')
        ->whereBetween('payment_date', [$monthStart, $monthEnd])
        ->get();

    echo "Rejected Proofs in Feb 2026: {$rejectedProofs->count()}\n";
    foreach ($rejectedProofs as $proof) {
        echo "  - Proof ID: {$proof->id}\n";
        echo "    Invoice IDs: " . json_encode($proof->fee_ids) . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
