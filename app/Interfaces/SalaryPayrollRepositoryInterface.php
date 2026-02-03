<?php

namespace App\Interfaces;

use App\DTOs\SalaryPayroll\PayrollCreationData;
use App\DTOs\SalaryPayroll\PayrollFilterData;
use App\DTOs\SalaryPayroll\PayrollStatusUpdateData;
use App\Models\Payroll;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SalaryPayrollRepositoryInterface
{
    public function getPayrollsForPeriod(int $year, int $month): Collection;

    public function listHistory(PayrollFilterData $filter): LengthAwarePaginator;

    public function listAllHistory(PayrollFilterData $filter): Collection;

    public function createPayroll(PayrollCreationData $data): Payroll;

    public function updateStatus(Payroll $payroll, PayrollStatusUpdateData $data): Payroll;

    public function findByEmployeePeriod(string $employeeType, string $employeeId, int $year, int $month): ?Payroll;
}
