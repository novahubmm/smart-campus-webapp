<?php

/**
 * Test script to manually trigger GenerateOneTimeFeeInvoicesJob
 * 
 * Usage from project root:
 * php test-generate-invoices.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\PaymentSystem\GenerateOneTimeFeeInvoicesJob;
use App\Models\PaymentSystem\FeeStructure;

echo "=== Generate One-Time Fee Invoices Test ===\n\n";

// Get all one-time fees
$oneTimeFees = FeeStructure::where('frequency', 'one_time')
    ->where('is_active', true)
    ->get();

if ($oneTimeFees->isEmpty()) {
    echo "No active one-time fees found.\n";
    echo "Please create a one-time fee first.\n";
    exit(0);
}

echo "Found {$oneTimeFees->count()} one-time fee(s):\n\n";

foreach ($oneTimeFees as $index => $fee) {
    echo ($index + 1) . ". {$fee->name} (ID: {$fee->id})\n";
    echo "   Grade: {$fee->grade}\n";
    echo "   Amount: {$fee->amount} MMK\n";
    echo "   Due Date: {$fee->due_date}\n";
    echo "   Batch: {$fee->batch}\n\n";
}

// Ask user which fee to process
echo "Enter the number of the fee to generate invoices for (or 'all' for all fees): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) === 'all') {
    echo "\nDispatching jobs for all one-time fees...\n";
    foreach ($oneTimeFees as $fee) {
        echo "Dispatching job for: {$fee->name}\n";
        GenerateOneTimeFeeInvoicesJob::dispatch($fee);
    }
    echo "\nAll jobs dispatched successfully!\n";
} elseif (is_numeric($input) && $input > 0 && $input <= $oneTimeFees->count()) {
    $selectedFee = $oneTimeFees[$input - 1];
    echo "\nDispatching job for: {$selectedFee->name}\n";
    GenerateOneTimeFeeInvoicesJob::dispatch($selectedFee);
    echo "Job dispatched successfully!\n";
} else {
    echo "Invalid input. Exiting.\n";
    exit(1);
}

echo "\nNote: The job will run asynchronously if queue is configured.\n";
echo "Check the logs for job execution details.\n";
