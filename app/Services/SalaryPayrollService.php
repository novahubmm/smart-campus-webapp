<?php

namespace App\Services;

use App\DTOs\SalaryPayroll\PayrollFilterData;
use App\DTOs\SalaryPayroll\PayrollStatusUpdateData;
use App\DTOs\SalaryPayroll\PaySalaryPayrollData;
use App\DTOs\SalaryPayroll\PayrollCreationData;
use App\DTOs\SalaryPayroll\PayrollEmployeeSummaryData;
use App\Interfaces\SalaryPayrollRepositoryInterface;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\StaffProfile;
use App\Models\TeacherProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SalaryPayrollService
{
    public function __construct(private readonly SalaryPayrollRepositoryInterface $repository) {}

    public function getCurrentMonthEntries(int $year, int $month): Collection
    {
        $payrolls = $this->repository->getPayrollsForPeriod($year, $month);

        $staff = StaffProfile::with(['department', 'user:id,name'])
            ->where('status', 'active')
            ->get();
        $teachers = TeacherProfile::with(['department', 'user:id,name'])
            ->where('status', 'active')
            ->get();

        $entries = collect();

        foreach ($staff as $profile) {
            $key = $this->key('staff', $profile->id);
            $entries->push(PayrollEmployeeSummaryData::fromStaff($profile, $payrolls[$key] ?? null));
        }

        foreach ($teachers as $profile) {
            $key = $this->key('teacher', $profile->id);
            $entries->push(PayrollEmployeeSummaryData::fromTeacher($profile, $payrolls[$key] ?? null));
        }

        return $entries;
    }

    public function getHistory(PayrollFilterData $filter): LengthAwarePaginator
    {
        return $this->repository->listHistory($filter);
    }

    public function pay(PaySalaryPayrollData $data): Payroll
    {
        $existing = $this->repository->findByEmployeePeriod(
            $data->employeeType,
            $data->employeeId,
            $data->year,
            $data->month
        );

        if ($existing) {
            // Update existing payroll with new values
            $existing->working_days = $data->workingDays;
            $existing->days_present = $data->daysPresent;
            $existing->leave_days = $data->leaveDays;
            $existing->annual_leave = $data->annualLeave;
            $existing->days_absent = $data->daysAbsent;
            $existing->basic_salary = $data->basicSalary;
            $existing->attendance_allowance = $data->attendanceAllowance;
            $existing->loyalty_bonus = $data->loyaltyBonus;
            $existing->other_bonus = $data->otherBonus;
            $existing->amount = $data->amount;
            $existing->save();

            $statusData = PayrollStatusUpdateData::fromPayData($data);
            $payroll = $this->repository->updateStatus($existing, $statusData);

            // Create expense entry for salary payment
            $this->createSalaryExpense($payroll, $data);

            return $payroll;
        }

        // Create new payroll
        $creationData = PayrollCreationData::fromPayData($data);
        $payroll = $this->repository->createPayroll($creationData);

        $statusData = PayrollStatusUpdateData::fromPayData($data);
        $payroll = $this->repository->updateStatus($payroll, $statusData);

        // Create expense entry for salary payment
        $this->createSalaryExpense($payroll, $data);

        return $payroll;
    }

    private function createSalaryExpense(Payroll $payroll, PaySalaryPayrollData $data): void
    {
        // Find or create "Salaries" expense category
        $salaryCategory = ExpenseCategory::firstOrCreate(
            ['name' => 'Salaries'],
            ['code' => 'SAL', 'description' => 'Employee salary payments']
        );

        // Get employee name
        $employeeName = $data->employeeType === 'teacher'
            ? TeacherProfile::with('user')->find($data->employeeId)?->user?->name
            : StaffProfile::with('user')->find($data->employeeId)?->user?->name;

        $monthName = \Carbon\Carbon::createFromDate($data->year, $data->month, 1)->format('F Y');

        // Generate expense number
        $expenseCount = Expense::withTrashed()->count() + 1;
        $expenseNumber = 'EXP-' . Str::padLeft((string) $expenseCount, 5, '0');

        // Create expense entry
        Expense::create([
            'expense_number' => $expenseNumber,
            'expense_category_id' => $salaryCategory->id,
            'title' => "Salary Payment - {$employeeName}",
            'description' => "Salary payment for {$monthName}. Payroll ID: {$payroll->id}",
            'amount' => $data->amount,
            'expense_date' => now(),
            'payment_method' => $this->mapPaymentMethod($data->paymentMethod),
            'status' => true,
            'created_by' => $data->receptionistId,
            'notes' => $data->remark,
        ]);
    }

    private function mapPaymentMethod(?string $method): string
    {
        return match ($method) {
            'Bank Transfer' => 'bank_transfer',
            'KBZ Pay' => 'kbz_pay',
            'Wave Pay' => 'wave_pay',
            'Check' => 'check',
            default => 'cash',
        };
    }

    private function key(string $type, string $id): string
    {
        return "{$type}|{$id}";
    }
}
