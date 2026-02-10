<?php

/**
 * Verify finance dashboard shows salary expenses correctly
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

echo "=== Finance Dashboard Verification ===\n\n";

// 1. Salary expenses
echo "1. Salary Expenses:\n";
$salaryCategory = ExpenseCategory::where('name', 'Salaries')->first();
if ($salaryCategory) {
    $salaryExpenses = Expense::where('expense_category_id', $salaryCategory->id)
        ->whereNull('deleted_at')
        ->get();
    
    echo "   Total salary expense records: {$salaryExpenses->count()}\n";
    echo "   Total amount: " . number_format($salaryExpenses->sum('amount'), 0) . " MMK\n";
    
    // Group by month
    $byMonth = $salaryExpenses->groupBy(function($expense) {
        return $expense->expense_date->format('Y-m');
    });
    
    echo "\n   Breakdown by month:\n";
    foreach ($byMonth as $month => $expenses) {
        $count = $expenses->count();
        $total = $expenses->sum('amount');
        echo "   - {$month}: {$count} payments, " . number_format($total, 0) . " MMK\n";
    }
}

// 2. All expenses
echo "\n2. All Expenses:\n";
$allExpenses = Expense::whereNull('deleted_at')->get();
$expensesByCategory = $allExpenses->groupBy('expense_category_id');

echo "   Total expense records: {$allExpenses->count()}\n";
echo "   Total amount: " . number_format($allExpenses->sum('amount'), 0) . " MMK\n";

echo "\n   Breakdown by category:\n";
foreach ($expensesByCategory as $categoryId => $expenses) {
    $category = ExpenseCategory::find($categoryId);
    $categoryName = $category ? $category->name : 'Unknown';
    $count = $expenses->count();
    $total = $expenses->sum('amount');
    echo "   - {$categoryName}: {$count} records, " . number_format($total, 0) . " MMK\n";
}

// 3. Income
echo "\n3. Income:\n";
$allIncome = Income::whereNull('deleted_at')->get();
echo "   Total income records: {$allIncome->count()}\n";
echo "   Total amount: " . number_format($allIncome->sum('amount'), 0) . " MMK\n";

// 4. Profit/Loss Summary
echo "\n4. Profit/Loss Summary:\n";
$totalIncome = $allIncome->sum('amount');
$totalExpenses = $allExpenses->sum('amount');
$netProfit = $totalIncome - $totalExpenses;

echo "   Total Income: " . number_format($totalIncome, 0) . " MMK\n";
echo "   Total Expenses: " . number_format($totalExpenses, 0) . " MMK\n";
echo "   Net Profit/Loss: " . number_format($netProfit, 0) . " MMK\n";

if ($netProfit >= 0) {
    echo "   Status: ✓ Profit\n";
} else {
    echo "   Status: ⚠ Loss\n";
}

// 5. Current month summary
echo "\n5. Current Month (February 2026):\n";
$currentMonth = now()->format('Y-m');
$currentIncome = Income::whereNull('deleted_at')
    ->whereYear('income_date', now()->year)
    ->whereMonth('income_date', now()->month)
    ->sum('amount');
$currentExpenses = Expense::whereNull('deleted_at')
    ->whereYear('expense_date', now()->year)
    ->whereMonth('expense_date', now()->month)
    ->sum('amount');
$currentSalaryExpenses = Expense::where('expense_category_id', $salaryCategory->id)
    ->whereNull('deleted_at')
    ->whereYear('expense_date', now()->year)
    ->whereMonth('expense_date', now()->month)
    ->sum('amount');

echo "   Income: " . number_format($currentIncome, 0) . " MMK\n";
echo "   Expenses: " . number_format($currentExpenses, 0) . " MMK\n";
echo "   - Salary expenses: " . number_format($currentSalaryExpenses, 0) . " MMK\n";
echo "   Net: " . number_format($currentIncome - $currentExpenses, 0) . " MMK\n";

// 6. Payroll vs Expense verification
echo "\n6. Payroll vs Expense Verification:\n";
$paidPayrolls = Payroll::where('status', 'paid')->count();
$salaryExpenseCount = $salaryExpenses->count();
$paidAmount = Payroll::where('status', 'paid')->sum('amount');
$expenseAmount = $salaryExpenses->sum('amount');

echo "   Paid payrolls: {$paidPayrolls}\n";
echo "   Salary expenses: {$salaryExpenseCount}\n";
echo "   Paid amount: " . number_format($paidAmount, 0) . " MMK\n";
echo "   Expense amount: " . number_format($expenseAmount, 0) . " MMK\n";

if ($paidPayrolls === $salaryExpenseCount && $paidAmount == $expenseAmount) {
    echo "   ✓ Perfect match! Integration working correctly\n";
} else {
    echo "   ⚠ Mismatch detected\n";
}

echo "\n=== Verification Complete ===\n";
echo "\nYou can now view these expenses at: http://192.168.100.114:8088/finance\n";
