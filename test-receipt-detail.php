<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Payment;

// Find a guardian user
$guardian = User::whereHas('guardianProfile')->first();

if (!$guardian) {
    echo "No guardian found\n";
    exit(1);
}

echo "Guardian: {$guardian->name} (ID: {$guardian->id})\n";

// Get their students
$students = $guardian->guardianProfile->students;
echo "Students: " . $students->count() . "\n";

if ($students->isEmpty()) {
    echo "No students found for this guardian\n";
    exit(1);
}

$student = $students->first();
echo "Student: {$student->user->name} (ID: {$student->id})\n";

// Find a payment for this student
$payment = Payment::where('student_id', $student->id)
    ->where('status', true)
    ->first();

if (!$payment) {
    echo "No completed payment found for this student\n";
    
    // Show all payments
    $allPayments = Payment::where('student_id', $student->id)->get();
    echo "All payments for student:\n";
    foreach ($allPayments as $p) {
        echo "  - ID: {$p->id}, Status: " . ($p->status ? 'completed' : 'pending') . ", Amount: {$p->amount}\n";
    }
    exit(1);
}

echo "\nPayment found:\n";
echo "  ID: {$payment->id}\n";
echo "  Amount: {$payment->amount}\n";
echo "  Status: " . ($payment->status ? 'completed' : 'pending') . "\n";
echo "  Payment Date: {$payment->payment_date}\n";

// Test the repository method
try {
    $repository = new \App\Repositories\Guardian\GuardianFeeRepository();
    $receipt = $repository->generateReceipt($payment->id, $student);
    
    echo "\nReceipt generated successfully:\n";
    echo json_encode($receipt, JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "\nError generating receipt: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
