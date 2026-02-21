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
            ->with(['invoice', 'grade', 'classModel'])
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
            ->with(['category'])
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
            'payment_method' => $data->payment_method,
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
            'payment_method' => $data->payment_method,
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
            'payment_method' => $data->payment_method,
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
            'payment_method' => $data->payment_method,
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

    public function profitLossByCategory(FinanceFilterData $filter): Collection
    {
        [$year, $month] = [$filter->year, $filter->month];

        $incomeGroups = Income::query()
            ->selectRaw('COALESCE(category, "Other") as category, SUM(amount) as total')
            ->when($year, fn($q) => $q->whereYear('income_date', $year))
            ->when($month, fn($q) => $q->whereMonth('income_date', $month))
            ->groupBy('category')
            ->pluck('total', 'category');

        $studentFeeTotal = $this->sumStudentFees($filter);
        if ($studentFeeTotal > 0) {
            $incomeGroups['Student Fees'] = $incomeGroups['Student Fees'] ?? 0;
            $incomeGroups['Student Fees'] += $studentFeeTotal;
        }

        $expenseGroups = Expense::query()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as category, SUM(expenses.amount) as total')
            ->when($year, fn($q) => $q->whereYear('expense_date', $year))
            ->when($month, fn($q) => $q->whereMonth('expense_date', $month))
            ->groupBy('expense_categories.name')
            ->pluck('total', 'category');

        $categories = collect($incomeGroups->keys())
            ->merge($expenseGroups->keys())
            ->unique()
            ->values();

        $totalIncome = $incomeGroups->sum();
        $totalExpenses = $expenseGroups->sum();

        $result = collect();
        foreach ($categories as $category) {
            $income = (float) ($incomeGroups[$category] ?? 0);
            $expense = (float) ($expenseGroups[$category] ?? 0);
            $net = $income - $expense;
            $base = max($totalIncome + $totalExpenses, 1);

            $result[$category] = [
                'income' => $income,
                'expenses' => $expense,
                'net' => $net,
                'percentage' => round(($income + $expense) / $base * 100, 1),
            ];
        }

        return $result->sortByDesc('income');
    }

    public function profitLossByCategoryForYear(int $year): Collection
    {
        $filter = new FinanceFilterData(year: $year, month: null, category: null, paymentMethod: null, search: null, perPage: 12);

        return $this->profitLossByCategory($filter);
    }

    public function dailyProfitLoss(FinanceFilterData $filter): Collection
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
        $dailyFees = Payment::query()
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
                'date' => $date,
                'income' => $totalIncome,
                'expenses' => $expenses,
                'net' => $net,
            ];
        }

        return $result->sortByDesc('date');
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
