<?php

namespace App\Repositories;

use App\DTOs\Finance\ExpenseData;
use App\DTOs\Finance\FinanceFilterData;
use App\DTOs\Finance\IncomeData;
use App\Interfaces\FinanceRecordRepositoryInterface;
use App\Models\Expense;
use App\Models\Income;
use App\Models\PaymentSystem\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FinanceRecordRepository implements FinanceRecordRepositoryInterface
{
    public function listIncomes(FinanceFilterData $filter): LengthAwarePaginator
    {
        $query = Income::query()
            ->with(['invoice', 'grade', 'classModel', 'paymentMethod'])
            ->latest('income_date');

        $this->applyCommonFilters($query, $filter, 'income_date');

        if ($filter->category) {
            $query->where('category', $filter->category);
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('income_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filter->perPage);
    }

    public function listExpenses(FinanceFilterData $filter): LengthAwarePaginator
    {
        $query = Expense::query()
            ->with(['category', 'paymentMethod'])
            ->latest('expense_date');

        $this->applyCommonFilters($query, $filter, 'expense_date');

        if ($filter->category) {
            $query->whereHas('category', function (Builder $builder) use ($filter) {
                $builder->where('name', $filter->category)->orWhere('code', $filter->category);
            });
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('expense_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filter->perPage);
    }

    public function listStudentPayments(FinanceFilterData $filter): LengthAwarePaginator
    {
        $query = Payment::query()
            ->with(['invoice.student.user', 'invoice.student.grade', 'invoice.student.classModel', 'paymentMethod'])
            ->where('status', 'verified')
            ->whereHas('invoice', function ($q) {
                $q->whereNotNull('batch_id');
            })
            ->latest('payment_date');

        $this->applyCommonFilters($query, $filter, 'payment_date');

        if ($filter->paymentMethod) {
            $query->where('payment_method_id', $filter->paymentMethod);
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('invoice.student.user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice.student', function (Builder $studentQuery) use ($search) {
                        $studentQuery->where('student_identifier', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($filter->perPage);
    }

    public function createIncome(IncomeData $data, ?string $creatorId = null): Income
    {
        $income = Income::create([
            'income_number' => $this->nextIncomeNumber(),
            'title' => $data->title,
            'category' => $data->category ?? 'Other',
            'description' => $data->description,
            'amount' => $data->amount,
            'income_date' => $data->income_date,
            'payment_method_id' => $data->payment_method_id,
            'reference_number' => $data->reference_number,
            'invoice_id' => $data->invoice_id,
            'grade_id' => $data->grade_id,
            'class_id' => $data->class_id,
            'created_by' => $creatorId,
            'status' => $data->status,
            'notes' => $data->notes,
        ]);

        return $income->fresh(['invoice', 'grade', 'classModel']);
    }

    public function updateIncome(Income $income, IncomeData $data): Income
    {
        $income->update([
            'title' => $data->title,
            'category' => $data->category ?? 'Other',
            'description' => $data->description,
            'amount' => $data->amount,
            'income_date' => $data->income_date,
            'payment_method_id' => $data->payment_method_id,
            'reference_number' => $data->reference_number,
            'invoice_id' => $data->invoice_id,
            'grade_id' => $data->grade_id,
            'class_id' => $data->class_id,
            'status' => $data->status,
            'notes' => $data->notes,
        ]);

        return $income->fresh(['invoice', 'grade', 'classModel']);
    }

    public function deleteIncome(Income $income): void
    {
        $income->delete();
    }

    public function createExpense(ExpenseData $data, ?string $creatorId = null): Expense
    {
        $expense = Expense::create([
            'expense_number' => $this->nextExpenseNumber(),
            'expense_category_id' => $data->expense_category_id,
            'title' => $data->title,
            'description' => $data->description,
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'payment_method_id' => $data->payment_method_id,
            'vendor_name' => $data->vendor_name,
            'invoice_number' => $data->invoice_number,
            'receipt_file' => $data->receipt_file,
            'notes' => $data->notes,
            'status' => $data->status,
            'created_by' => $creatorId,
        ]);

        return $expense->fresh(['category']);
    }

    public function updateExpense(Expense $expense, ExpenseData $data): Expense
    {
        $expense->update([
            'expense_category_id' => $data->expense_category_id,
            'title' => $data->title,
            'description' => $data->description,
            'amount' => $data->amount,
            'expense_date' => $data->expense_date,
            'payment_method_id' => $data->payment_method_id,
            'vendor_name' => $data->vendor_name,
            'invoice_number' => $data->invoice_number,
            'receipt_file' => $data->receipt_file,
            'notes' => $data->notes,
            'status' => $data->status,
        ]);

        return $expense->fresh(['category']);
    }

    public function deleteExpense(Expense $expense): void
    {
        $expense->delete();
    }

    public function profitLossSummary(FinanceFilterData $filter): array
    {
        $manualIncome = $this->sumIncome($filter);
        $studentFeeIncome = $this->sumStudentFees($filter);
        $totalIncome = $manualIncome + $studentFeeIncome;
        $totalExpenses = $this->sumExpenses($filter);

        return [
            'manual_income' => $manualIncome,
            'student_fee_income' => $studentFeeIncome,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net' => $totalIncome - $totalExpenses,
        ];
    }

    public function profitLossByMonth(FinanceFilterData $filter): Collection
    {
        [$year, $month] = [$filter->year, $filter->month];

        // Get daily income breakdown
        $dailyIncome = Income::query()
            ->selectRaw('DATE(income_date) as date, SUM(amount) as total')
            ->when($year, fn($q) => $q->whereYear('income_date', $year))
            ->when($month, fn($q) => $q->whereMonth('income_date', $month))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get daily student fee payments
        $dailyFees = \App\Models\PaymentSystem\Payment::query()
            ->where('status', 'verified')
            ->whereHas('invoice', function ($q) {
                $q->whereNotNull('batch_id');
            })
            ->selectRaw('DATE(payment_date) as date, SUM(payment_amount) as total')
            ->when($year, fn($q) => $q->whereYear('payment_date', $year))
            ->when($month, fn($q) => $q->whereMonth('payment_date', $month))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get daily expenses
        $dailyExpenses = Expense::query()
            ->selectRaw('DATE(expense_date) as date, SUM(amount) as total')
            ->when($year, fn($q) => $q->whereYear('expense_date', $year))
            ->when($month, fn($q) => $q->whereMonth('expense_date', $month))
            ->groupBy('date')
            ->pluck('total', 'date');

        // Merge all dates
        $allDates = collect($dailyIncome->keys())
            ->merge($dailyFees->keys())
            ->merge($dailyExpenses->keys())
            ->unique()
            ->sort()
            ->values();

        // Build daily breakdown
        $result = collect();
        foreach ($allDates as $date) {
            $income = (float) ($dailyIncome[$date] ?? 0);
            $fees = (float) ($dailyFees[$date] ?? 0);
            $totalIncome = $income + $fees;
            $expenses = (float) ($dailyExpenses[$date] ?? 0);
            $net = $totalIncome - $expenses;

            $result[$date] = [
                'income' => $totalIncome,
                'expenses' => $expenses,
                'net' => $net,
            ];
        }

        return $result->sortByDesc(function ($item, $key) {
            return $key; // Sort by date descending
        });
    }

    public function profitLossByCategoryForYear(int $year): Collection
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");
        
        // Use appropriate SQL function based on database driver
        $monthExpression = $connection === 'sqlite' 
            ? "strftime('%m', income_date)" 
            : 'MONTH(income_date)';
        
        $monthExpressionPayment = $connection === 'sqlite' 
            ? "strftime('%m', payment_date)" 
            : 'MONTH(payment_date)';
        
        $monthExpressionExpense = $connection === 'sqlite' 
            ? "strftime('%m', expense_date)" 
            : 'MONTH(expense_date)';

        // Get monthly income breakdown
        $monthlyIncome = Income::query()
            ->selectRaw("{$monthExpression} as month, SUM(amount) as total")
            ->whereYear('income_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Get monthly student fee payments
        $monthlyFees = \App\Models\PaymentSystem\Payment::query()
            ->where('status', 'verified')
            ->whereHas('invoice', function ($q) {
                $q->whereNotNull('batch_id');
            })
            ->selectRaw("{$monthExpressionPayment} as month, SUM(payment_amount) as total")
            ->whereYear('payment_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Get monthly expenses
        $monthlyExpenses = Expense::query()
            ->selectRaw("{$monthExpressionExpense} as month, SUM(amount) as total")
            ->whereYear('expense_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Build monthly breakdown for the year
        $result = collect();
        for ($month = 1; $month <= 12; $month++) {
            // SQLite returns month as '01', '02', etc., so we need to handle both formats
            $monthKey = $connection === 'sqlite' ? str_pad($month, 2, '0', STR_PAD_LEFT) : $month;
            
            $income = (float) ($monthlyIncome[$monthKey] ?? 0);
            $fees = (float) ($monthlyFees[$monthKey] ?? 0);
            $totalIncome = $income + $fees;
            $expenses = (float) ($monthlyExpenses[$monthKey] ?? 0);
            $net = $totalIncome - $expenses;

            // Only include months with data
            if ($totalIncome > 0 || $expenses > 0) {
                $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
                $result[$monthName] = [
                    'income' => $totalIncome,
                    'expenses' => $expenses,
                    'net' => $net,
                ];
            }
        }

        return $result;
    }

    public function dailyProfitLoss(FinanceFilterData $filter): Collection
    {
        [$year, $month] = [$filter->year, $filter->month];

        // Get income by category
        $incomeByCategory = Income::query()
            ->selectRaw('COALESCE(category, "Other") as category, SUM(amount) as total')
            ->when($year, fn($q) => $q->whereYear('income_date', $year))
            ->when($month, fn($q) => $q->whereMonth('income_date', $month))
            ->groupBy('category')
            ->pluck('total', 'category');

        // Get student fee payments by fee type using the payment system tables
        $feesByType = \App\Models\PaymentSystem\Payment::query()
            ->where('payments_payment_system.status', 'verified')
            ->join('payment_fee_details', 'payments_payment_system.id', '=', 'payment_fee_details.payment_id')
            ->join('invoice_fees', 'payment_fee_details.invoice_fee_id', '=', 'invoice_fees.id')
            ->join('fee_structures_payment_system', 'invoice_fees.fee_id', '=', 'fee_structures_payment_system.id')
            ->selectRaw('fee_structures_payment_system.name as fee_type, SUM(payment_fee_details.paid_amount) as total')
            ->when($year, fn($q) => $q->whereYear('payments_payment_system.payment_date', $year))
            ->when($month, fn($q) => $q->whereMonth('payments_payment_system.payment_date', $month))
            ->groupBy('fee_structures_payment_system.name')
            ->pluck('total', 'fee_type');

        // Get expenses by category
        $expensesByCategory = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category, SUM(expenses.amount) as total')
            ->when($year, fn($q) => $q->whereYear('expenses.expense_date', $year))
            ->when($month, fn($q) => $q->whereMonth('expenses.expense_date', $month))
            ->groupBy('expense_categories.name')
            ->pluck('total', 'category');

        // Merge all categories
        $allCategories = collect($incomeByCategory->keys())
            ->merge($feesByType->keys())
            ->merge($expensesByCategory->keys())
            ->unique()
            ->sort()
            ->values();

        // Build breakdown by category
        $result = collect();
        foreach ($allCategories as $category) {
            $income = (float) ($incomeByCategory[$category] ?? 0);
            $fees = (float) ($feesByType[$category] ?? 0);
            $totalIncome = $income + $fees;
            $expenses = (float) ($expensesByCategory[$category] ?? 0);
            $net = $totalIncome - $expenses;

            $result[$category] = [
                'date' => $category, // Keep 'date' key for backward compatibility with view
                'income' => $totalIncome,
                'expenses' => $expenses,
                'net' => $net,
            ];
        }

        return $result->sortBy('date');
    }

    private function applyCommonFilters(Builder $query, FinanceFilterData $filter, string $dateColumn): void
    {
        if ($filter->year) {
            $query->whereYear($dateColumn, $filter->year);
        }

        if ($filter->month) {
            $query->whereMonth($dateColumn, $filter->month);
        }

        if ($filter->paymentMethod) {
            $query->where('payment_method', $filter->paymentMethod);
        }
    }

    private function sumIncome(FinanceFilterData $filter): float
    {
        $query = Income::query();
        $this->applyCommonFilters($query, $filter, 'income_date');

        return (float) $query->sum('amount');
    }

    private function sumStudentFees(FinanceFilterData $filter): float
    {
        $query = Payment::query()
            ->where('status', 'verified')
            ->whereHas('invoice', function ($q) {
                $q->whereNotNull('batch_id');
            });
        $this->applyCommonFilters($query, $filter, 'payment_date');

        return (float) $query->sum('payment_amount');
    }

    private function sumExpenses(FinanceFilterData $filter): float
    {
        $query = Expense::query();
        $this->applyCommonFilters($query, $filter, 'expense_date');

        return (float) $query->sum('amount');
    }

    private function nextIncomeNumber(): string
    {
        $count = Income::withTrashed()->count() + 1;
        return 'INC-' . Str::padLeft((string) $count, 5, '0');
    }

    private function nextExpenseNumber(): string
    {
        $count = Expense::withTrashed()->count() + 1;
        return 'EXP-' . Str::padLeft((string) $count, 5, '0');
    }
}
