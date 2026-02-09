<?php

namespace App\Interfaces;

use App\DTOs\Finance\ExpenseData;
use App\DTOs\Finance\FinanceFilterData;
use App\DTOs\Finance\IncomeData;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FinanceRecordRepositoryInterface
{
    public function listIncomes(FinanceFilterData $filter): LengthAwarePaginator;

    public function listExpenses(FinanceFilterData $filter): LengthAwarePaginator;

    public function listStudentPayments(FinanceFilterData $filter): LengthAwarePaginator;

    public function createIncome(IncomeData $data, ?string $creatorId = null): Income;

    public function updateIncome(Income $income, IncomeData $data): Income;

    public function deleteIncome(Income $income): void;

    public function createExpense(ExpenseData $data, ?string $creatorId = null): Expense;

    public function updateExpense(Expense $expense, ExpenseData $data): Expense;

    public function deleteExpense(Expense $expense): void;

    public function profitLossSummary(FinanceFilterData $filter): array;

    public function profitLossByCategory(FinanceFilterData $filter): Collection;

    public function profitLossByCategoryForYear(int $year): Collection;

    public function dailyProfitLoss(FinanceFilterData $filter): Collection;
}
