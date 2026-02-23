<?php

namespace App\Repositories;

use App\DTOs\SalaryPayroll\PayrollCreationData;
use App\DTOs\SalaryPayroll\PayrollFilterData;
use App\DTOs\SalaryPayroll\PayrollStatusUpdateData;
use App\Interfaces\SalaryPayrollRepositoryInterface;
use App\Models\Payroll;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SalaryPayrollRepository implements SalaryPayrollRepositoryInterface
{
    public function getPayrollsForPeriod(int $year, int $month): Collection
    {
        return Payroll::query()
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'pending') // Only show pending payrolls
            ->get()
            ->keyBy(function (Payroll $payroll) {
                return $this->key($payroll->employee_type, $payroll->employee_id);
            });
    }

    public function listHistory(PayrollFilterData $filter): LengthAwarePaginator
    {
        $query = Payroll::query()
            ->with('processedBy')
            ->where('status', 'paid') // Only show paid invoices in history
            ->latest('paid_at');

        if ($filter->year) {
            $query->where('year', $filter->year);
        }

        if ($filter->month) {
            $query->where('month', $filter->month);
        }

        return $query->paginate($filter->perPage);
    }

    public function listAllHistory(PayrollFilterData $filter): Collection
    {
        $query = Payroll::query()->with('processedBy')->latest('paid_at');

        if ($filter->year) {
            $query->where('year', $filter->year);
        }

        if ($filter->month) {
            $query->where('month', $filter->month);
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        return $query->get();
    }

    public function createPayroll(PayrollCreationData $data): Payroll
    {
        return Payroll::create([
            'employee_type' => $data->employeeType,
            'employee_id' => $data->employeeId,
            'year' => $data->year,
            'month' => $data->month,
            'working_days' => $data->workingDays,
            'days_present' => $data->daysPresent,
            'leave_days' => $data->leaveDays,
            'annual_leave' => $data->annualLeave,
            'days_absent' => $data->daysAbsent,
            'basic_salary' => $data->basicSalary,
            'attendance_allowance' => $data->attendanceAllowance,
            'loyalty_bonus' => $data->loyaltyBonus,
            'other_bonus' => $data->otherBonus,
            'amount' => $data->amount,
            'total_amount' => $data->totalAmount,
            'paid_amount' => $data->paidAmount,
            'payment_count' => $data->paymentCount,
            'status' => $data->status,
            'payment_method' => $data->paymentMethod,
            'processed_by' => $data->processedBy,
            'paid_at' => $data->status === 'paid' ? now() : null,
        ]);
    }

    public function updateStatus(Payroll $payroll, PayrollStatusUpdateData $data): Payroll
    {
        $payroll->status = $data->status;
        $payroll->processed_by = $data->processedBy ?? $payroll->processed_by;
        $payroll->paid_at = $data->paidAt ? $data->paidAt->format('Y-m-d H:i:s') : ($data->status === 'paid' ? now() : $payroll->paid_at);
        $payroll->payment_method = $data->paymentMethod ?? $payroll->payment_method;
        $payroll->reference = $data->reference ?? $payroll->reference;
        $payroll->receptionist_id = $data->receptionistId ?? $payroll->receptionist_id;
        $payroll->receptionist_name = $data->receptionistName ?? $payroll->receptionist_name;
        $payroll->remark = $data->remark ?? $payroll->remark;
        $payroll->notes = $data->notes ?? $payroll->notes;
        $payroll->save();

        return $payroll->fresh('processedBy');
    }

    public function findByEmployeePeriod(string $employeeType, string $employeeId, int $year, int $month): ?Payroll
    {
        return Payroll::query()
            ->where('employee_type', $employeeType)
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', 'pending') // Only find pending records
            ->first();
    }

    private function key(string $type, string $id): string
    {
        return "{$type}|{$id}";
    }
}
