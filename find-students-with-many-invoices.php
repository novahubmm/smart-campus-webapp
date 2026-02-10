<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\PaymentProof;
use Carbon\Carbon;

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Finding Students with More Than 5 Unpaid Invoices in February 2026 ===\n\n";

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

echo "Total rejected proof invoice IDs: " . count($rejectedProofInvoiceIds) . "\n\n";

// Query all unpaid invoices
$unpaidInvoices = Invoice::with([
    'student.user', 
    'student.grade', 
    'student.classModel'
])
->where(function ($query) use ($rejectedProofInvoiceIds) {
    $query->whereIn('status', ['unpaid', 'sent'])
          ->orWhereIn('id', $rejectedProofInvoiceIds);
})
->whereBetween('invoice_date', [$monthStart, $monthEnd])
->whereHas('student', function ($q) {
    $q->where('status', 'active');
})
->get();

// Group by student
$invoicesByStudent = $unpaidInvoices->groupBy('student_id');

echo "Students with unpaid invoices:\n\n";

$studentsWithMany = [];

foreach ($invoicesByStudent as $studentId => $invoices) {
    $count = $invoices->count();
    $student = $invoices->first()->student;
    
    if ($count > 5) {
        $studentsWithMany[] = [
            'student' => $student,
            'count' => $count,
            'invoices' => $invoices
        ];
    }
}

if (empty($studentsWithMany)) {
    echo "✓ No students found with more than 5 unpaid invoices\n";
} else {
    echo "Found " . count($studentsWithMany) . " students with more than 5 unpaid invoices:\n\n";
    
    foreach ($studentsWithMany as $data) {
        $student = $data['student'];
        $count = $data['count'];
        $invoices = $data['invoices'];
        
        echo "Student: {$student->user->name} ({$student->student_identifier})\n";
        echo "  Grade: {$student->grade->level} / Class: {$student->classModel->name}\n";
        echo "  Unpaid Invoices: {$count}\n";
        
        // Check for rejected proofs
        $rejectedProofs = PaymentProof::where('student_id', $student->id)
            ->where('status', 'rejected')
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->count();
        
        if ($rejectedProofs > 0) {
            echo "  ❌ Has {$rejectedProofs} rejected payment proof(s)\n";
        }
        
        echo "  Invoices:\n";
        foreach ($invoices as $inv) {
            $hasRejectedProof = in_array($inv->id, $rejectedProofInvoiceIds);
            $marker = $hasRejectedProof ? ' ❌' : '';
            echo "    - {$inv->invoice_number}: " . number_format($inv->total_amount, 0) . " MMK ({$inv->status}){$marker}\n";
        }
        
        echo "\n";
    }
}

// Also check for students with rejected proofs that have null fee_ids
echo "\n=== Checking for Rejected Proofs with Null fee_ids ===\n\n";

$brokenProofs = PaymentProof::where('status', 'rejected')
    ->whereBetween('payment_date', [$monthStart, $monthEnd])
    ->whereNull('fee_ids')
    ->with(['student.user'])
    ->get();

if ($brokenProofs->count() > 0) {
    echo "⚠ Found {$brokenProofs->count()} rejected proofs with null fee_ids:\n\n";
    
    foreach ($brokenProofs as $proof) {
        echo "Proof ID: {$proof->id}\n";
        echo "  Student: {$proof->student->user->name} ({$proof->student->student_identifier})\n";
        echo "  Amount: " . number_format($proof->payment_amount, 0) . " MMK\n";
        echo "  Rejection: {$proof->rejection_reason}\n";
        
        // Check for pending_verification invoices
        $pendingInvoices = Invoice::where('student_id', $proof->student_id)
            ->where('status', 'pending_verification')
            ->whereBetween('invoice_date', [$monthStart, $monthEnd])
            ->get();
        
        if ($pendingInvoices->count() > 0) {
            echo "  ⚠ Has {$pendingInvoices->count()} pending_verification invoices that should be linked\n";
        }
        
        echo "\n";
    }
} else {
    echo "✓ No rejected proofs with null fee_ids found\n";
}

echo "\n✓ Analysis complete!\n";
