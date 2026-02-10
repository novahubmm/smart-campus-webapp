<?php

/**
 * Create expense records for already-paid salaries
 * This script will retroactively create expense entries for all paid payrolls
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payroll;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\TeacherProfile;
use App\Models\StaffProfile;
use Illuminate\Support\Str;

echo "=== Creating Salary Expenses Retroactively ===\n\n";

// 1. Create or find Salary category
echo "1. Setting up Salary Expense Category:\n";
$salaryCategory = ExpenseCategory::firstOrCreate(
    ['name' => 'Salaries'],
    [
        'code' => 'SAL',
        'description' => 'Employee salary payments',
        'status' => true,
    ]
);
echo "   ✓ Salary category ready: {$salaryCategory->name} (ID: {$salaryCategory->id})\n\n";

// 2. Get all paid payrolls
echo "2. Finding Paid Payrolls:\n";
$paidPayrolls = Payroll::where('status', 'paid')->get();
echo "   Found {$paidPayrolls->count()} paid payrolls\n";
echo "   Total amount: " . number_format($paidPayrolls->sum('amount'), 0) . " MMK\n\n";

// 3. Check existing salary expenses
echo "3. Checking Existing Salary Expenses:\n";
$existingExpenses = Expense::where('expense_category_id', $salaryCategory->id)->get();
echo "   Found {$existingExpenses->count()} existing salary expenses\n\n";

// 4. Create missing expense records
echo "4. Creating Missing Expense Records:\n";
$created = 0;
$skipped = 0;
$errors = 0;

foreach ($paidPayrolls as $payroll) {
    // Check if expense already exists for this payroll
    $exists = Expense::where('expense_category_id', $salaryCategory->id)
        ->where('description', 'LIKE', "%Payroll ID: {$payroll->id}%")
        ->exists();
    
    if ($exists) {
        $skipped++;
        continue;
    }
    
    try {
        // Get employee name
        $employeeName = 'Unknown Employee';
        if ($payroll->employee_type === 'teacher') {
            $profile = TeacherProfile::with('user')->find($payroll->employee_id);
            $employeeName = $profile?->user?->name ?? 'Teacher';
        } else {
            $profile = StaffProfile::with('user')->find($payroll->employee_id);
            $employeeName = $profile?->user?->name ?? 'Staff';
        }
        
        $monthName = \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y');
        
        // Generate expense number
        $expenseCount = Expense::withTrashed()->count() + 1;
        $expenseNumber = 'EXP-' . Str::padLeft((string) $expenseCount, 5, '0');
        
        // Map payment method
        $paymentMethod = match ($payroll->payment_method) {
            'Bank Transfer' => 'bank_transfer',
            'KBZ Pay' => 'kbz_pay',
            'Wave Pay' => 'wave_pay',
            'Check' => 'check',
            default => 'cash',
        };
        
        // Create expense
        Expense::create([
            'expense_number' => $expenseNumber,
            'expense_category_id' => $salaryCategory->id,
            'title' => "Salary Payment - {$employeeName}",
            'description' => "Salary payment for {$monthName}. Payroll ID: {$payroll->id}",
            'amount' => $payroll->amount,
            'expense_date' => $payroll->paid_at ?? now(),
            'payment_method' => $paymentMethod,
            'status' => true,
            'created_by' => $payroll->processed_by,
            'notes' => $payroll->remark,
        ]);
        
        $created++;
        echo "   ✓ Created expense for {$employeeName} - " . number_format($payroll->amount, 0) . " MMK\n";
        
    } catch (\Exception $e) {
        $errors++;
        echo "   ✗ Error creating expense for payroll {$payroll->id}: {$e->getMessage()}\n";
    }
}

echo "\n5. Summary:\n";
echo "   Created: {$created}\n";
echo "   Skipped (already exists): {$skipped}\n";
echo "   Errors: {$errors}\n";
echo "   Total processed: " . ($created + $skipped + $errors) . "\n";

// 6. Verify final state
echo "\n6. Final Verification:\n";
$totalExpenses = Expense::where('expense_category_id', $salaryCategory->id)->count();
$totalPaid = Payroll::where('status', 'paid')->count();
echo "   Paid payrolls: {$totalPaid}\n";
echo "   Salary expenses: {$totalExpenses}\n";

if ($totalPaid === $totalExpenses) {
    echo "   ✓ Perfect! All paid salaries now have expense records\n";
} else {
    echo "   ⚠ Still have mismatch: " . abs($totalPaid - $totalExpenses) . " difference\n";
}

echo "\n=== Complete ===\n";
