<?php

/**
 * Example script to create a one-time fee and generate invoices for all students
 * 
 * Usage from smart-campus-webapp directory:
 * php create-one-time-fee-example.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentSystem\FeeStructure;
use App\Jobs\PaymentSystem\GenerateOneTimeFeeInvoicesJob;
use Carbon\Carbon;

echo "========================================\n";
echo "Create One-Time Fee & Generate Invoices\n";
echo "========================================\n\n";

// Example: Create a one-time fee (e.g., Exam Fee, Registration Fee, etc.)
$feeData = [
    'name' => 'Exam Fee',
    'name_mm' => 'စာမေးပွဲ ကြေးငွေ',
    'description' => 'Annual examination fee',
    'description_mm' => 'နှစ်ပတ်လည် စာမေးပွဲ ကြေးငွေ',
    'amount' => 50000, // 50,000 MMK
    'frequency' => 'one_time',
    'fee_type' => 'other',
    'grade' => '0', // Change to your target grade
    'batch' => 'One Grade Demo 2026-2027', // Change to your target batch
    'target_month' => 3, // March (1-12)
    'due_date' => Carbon::now()->addDays(30), // Due in 30 days
    'supports_payment_period' => false, // One-time fees don't support payment periods
    'is_active' => true,
];

echo "Creating one-time fee: {$feeData['name']}\n";
echo "Grade: {$feeData['grade']}\n";
echo "Batch: {$feeData['batch']}\n";
echo "Amount: {$feeData['amount']} MMK\n";
echo "Target Month: " . Carbon::create()->month($feeData['target_month'])->format('F') . "\n";
echo "Due Date: {$feeData['due_date']->format('Y-m-d')}\n\n";

try {
    // Create the fee structure
    $feeStructure = FeeStructure::create($feeData);
    
    echo "✓ Fee structure created successfully! (ID: {$feeStructure->id})\n\n";
    
    // Count students in target grade
    $studentCount = \App\Models\StudentProfile::where('status', 'active')
        ->whereHas('grade', function ($query) use ($feeData) {
            $query->where('level', $feeData['grade']);
        })
        ->count();
    
    echo "Found {$studentCount} active student(s) in grade {$feeData['grade']}\n\n";
    
    if ($studentCount > 0) {
        echo "Dispatching invoice generation job...\n";
        GenerateOneTimeFeeInvoicesJob::dispatch($feeStructure);
        echo "✓ Job dispatched successfully!\n\n";
        echo "Invoices will be generated in the background.\n";
        echo "Check the invoices_payment_system table to see the results.\n";
    } else {
        echo "⚠ No students found in target grade. No invoices will be generated.\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
}

echo "\n========================================\n";
echo "Done!\n";
echo "========================================\n";
