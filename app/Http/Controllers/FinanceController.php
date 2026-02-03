<?php

namespace App\Http\Controllers;

use App\DTOs\Finance\FinanceFilterData;
use App\DTOs\Finance\IncomeData;
use App\DTOs\Finance\ExpenseData;
use App\Http\Requests\ExpenseRequest;
use App\Http\Requests\IncomeRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Grade;
use App\Models\Income;
use App\Models\SchoolClass;
use App\Services\FinanceRecordService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly FinanceRecordService $service) {}

    public function index(Request $request): View
    {
        $filter = FinanceFilterData::from($request->all());

        $incomes = $this->service->incomes($filter);
        $expenses = $this->service->expenses($filter);
        $feePayments = $this->service->feePayments($filter);
        $summary = $this->service->profitLossSummary($filter);
        $monthlyBreakdown = $this->service->profitLossByCategory($filter);
        $annualBreakdown = $this->service->profitLossByCategoryForYear($filter->year ?? now()->year);

        return view('finance.index', [
            'filter' => $filter,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'feePayments' => $feePayments,
            'summary' => $summary,
            'monthlyBreakdown' => $monthlyBreakdown,
            'annualBreakdown' => $annualBreakdown,
            'expenseCategories' => ExpenseCategory::orderBy('name')->get(),
            'grades' => Grade::orderBy('level')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function storeIncome(IncomeRequest $request): RedirectResponse
    {
        $data = IncomeData::from($request->validated());
        $income = $this->service->storeIncome($data, $request->user()?->id);

        $this->logCreate('Income', $income->id ?? '', $data->description ?? 'Income record');

        return back()->with('status', __('Income recorded successfully.'));
    }

    public function updateIncome(IncomeRequest $request, Income $income): RedirectResponse
    {
        $data = IncomeData::from($request->validated());
        $this->service->updateIncome($income, $data);

        $this->logUpdate('Income', $income->id, $income->description ?? 'Income record');

        return back()->with('status', __('Income updated successfully.'));
    }

    public function destroyIncome(Income $income): RedirectResponse
    {
        $incomeId = $income->id;
        $incomeDesc = $income->description ?? 'Income record';
        $this->service->deleteIncome($income);

        $this->logDelete('Income', $incomeId, $incomeDesc);

        return back()->with('status', __('Income deleted.'));
    }

    public function storeExpense(ExpenseRequest $request): RedirectResponse
    {
        $data = ExpenseData::from($request->validated());
        $expense = $this->service->storeExpense($data, $request->user()?->id);

        $this->logCreate('Expense', $expense->id ?? '', $data->description ?? 'Expense record');

        return back()->with('status', __('Expense recorded successfully.'));
    }

    public function updateExpense(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $data = ExpenseData::from($request->validated());
        $this->service->updateExpense($expense, $data);

        $this->logUpdate('Expense', $expense->id, $expense->description ?? 'Expense record');

        return back()->with('status', __('Expense updated successfully.'));
    }

    public function destroyExpense(Expense $expense): RedirectResponse
    {
        $expenseId = $expense->id;
        $expenseDesc = $expense->description ?? 'Expense record';
        $this->service->deleteExpense($expense);

        $this->logDelete('Expense', $expenseId, $expenseDesc);

        return back()->with('status', __('Expense deleted.'));
    }
}
