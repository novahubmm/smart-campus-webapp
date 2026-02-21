<?php

namespace App\Services;

use App\DTOs\Finance\ExpenseData;
use App\DTOs\Finance\FinanceFilterData;
use App\DTOs\Finance\IncomeData;
use App\Interfaces\FinanceRecordRepositoryInterface;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FinanceRecordService
{
    public function __construct(private readonly FinanceRecordRepositoryInterface $repository) {}

    public function incomes(FinanceFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listIncomes($filter);
    }

    public function expenses(FinanceFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listExpenses($filter);
    }

    public function feePayments(FinanceFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listStudentPayments($filter);
    }

    public function storeIncome(IncomeData $data, ?string $creatorId = null): Income
    {
        return $this->repository->createIncome($data, $creatorId);
    }

    public function updateIncome(Income $income, IncomeData $data): Income
    {
        return $this->repository->updateIncome($income, $data);
    }

    public function deleteIncome(Income $income): void
    {
        $this->repository->deleteIncome($income);
    }

    public function storeExpense(ExpenseData $data, ?string $creatorId = null): Expense
    {
        return $this->repository->createExpense($data, $creatorId);
    }

    public function updateExpense(Expense $expense, ExpenseData $data): Expense
    {
        return $this->repository->updateExpense($expense, $data);
    }

    public function deleteExpense(Expense $expense): void
    {
        $this->repository->deleteExpense($expense);
    }

    public function profitLossSummary(FinanceFilterData $filter): array
    {
        return $this->repository->profitLossSummary($filter);
    }

    public function profitLossByMonth(FinanceFilterData $filter): Collection
    {
        return $this->repository->profitLossByMonth($filter);
    }

    public function profitLossByCategoryForYear(int $year): Collection
    {
        return $this->repository->profitLossByCategoryForYear($year);
    }

    public function dailyProfitLoss(FinanceFilterData $filter): Collection
    {
        return $this->repository->dailyProfitLoss($filter);
    }
}
