<?php

/**
 * Test script to verify salary payroll expense integration
 * This checks if paid salaries are recorded as expenses in the finance system
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payroll;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

echo "=== Salary Payroll Expense Integration Test ===\n\n";

// 1. Check Salary Category
echo "1. Checking Salary Expense Category:\n";
$salaryCategory = ExpenseCategory::where('name', 'Salaries')->first();
if ($salaryCategory) {
    echo "   ✓ Salary category exists: {$salaryCategory->name} (Code: {$salaryCategory->code})\n";
    echo "   ID: {$salaryCategory->id}\n\n";
} else {
    echo "   ✗ Salary category not found\n\n";
}

// 2. Check paid payrolls
echo "2. Checking Paid Payrolls:\n";
$paidPayrolls = Payroll::where('status', 'paid')->get();
echo "   Total paid payrolls: {$paidPayrolls->count()}\n";
echo "   Total amount paid: " . number_format($paidPayrolls->sum('amount'), 0) . " MMK\n\n";

// 3. Check salary expenses
echo "3. Checking Salary Expenses:\n";
if ($salaryCategory) {
    $salaryExpenses = Expense::where('expense_category_id', $salaryCategory->id)->get();
    echo "   Total salary expenses: {$salaryExpenses->count()}\n";
    echo "   Total expense amount: " . number_format($salaryExpenses->sum('amount'), 0) . " MMK\n\n";
    
    if ($salaryExpenses->count() > 0) {
        echo "   Recent salary expenses:\n";
        foreach ($salaryExpenses->take(5) as $expense) {
            echo "   - {$expense->title}: " . number_format($expense->amount, 0) . " MMK\n";
            echo "     Date: {$expense->expense_date->format('Y-m-d')}\n";
            echo "     Method: {$expense->payment_method}\n";
            echo "     Description: {$expense->description}\n\n";
        }
    }
} else {
    echo "   Cannot check expenses - category not found\n\n";
}

// 4. Check current month payroll status
echo "4. Current Month Payroll Status:\n";
$currentYear = now()->year;
$currentMonth = now()->month;
$currentPayrolls = Payroll::where('year', $currentYear)
    ->where('month', $currentMonth)
    ->get();

echo "   Year: {$currentYear}, Month: {$currentMonth}\n";
echo "   Total employees: {$currentPayrolls->count()}\n";
echo "   Paid: " . $currentPayrolls->where('status', 'paid')->count() . "\n";
echo "   Pending: " . $currentPayrolls->where('status', 'draft')->count() . "\n";
echo "   Total payroll amount: " . number_format($currentPayrolls->sum('amount'), 0) . " MMK\n";
echo "   Paid amount: " . number_format($currentPayrolls->where('status', 'paid')->sum('amount'), 0) . " MMK\n\n";

// 5. Verify integration
echo "5. Integration Verification:\n";
$paidCount = $paidPayrolls->count();
$expenseCount = $salaryCategory ? Expense::where('expense_category_id', $salaryCategory->id)->count() : 0;

if ($paidCount === $expenseCount) {
    echo "   ✓ Perfect match! All {$paidCount} paid salaries have expense records\n";
} else {
    echo "   ⚠ Mismatch detected:\n";
    echo "     Paid payrolls: {$paidCount}\n";
    echo "     Salary expenses: {$expenseCount}\n";
    echo "     Difference: " . abs($paidCount - $expenseCount) . "\n";
}

// 6. Check finance dashboard data
echo "\n6. Finance Dashboard Summary:\n";
$totalIncome = DB::table('incomes')->sum('amount');
$totalExpenses = DB::table('expenses')->whereNull('deleted_at')->sum('amount');
$totalFeePayments = DB::table('fee_payments')->where('status', 'approved')->sum('amount');

echo "   Total Income: " . number_format($totalIncome, 0) . " MMK\n";
echo "   Total Fee Payments: " . number_format($totalFeePayments, 0) . " MMK\n";
echo "   Total Expenses: " . number_format($totalExpenses, 0) . " MMK\n";
echo "   Salary Expenses: " . number_format($salaryExpenses->sum('amount'), 0) . " MMK\n";
echo "   Net Profit/Loss: " . number_format(($totalIncome + $totalFeePayments) - $totalExpenses, 0) . " MMK\n";

echo "\n=== Test Complete ===\n";
