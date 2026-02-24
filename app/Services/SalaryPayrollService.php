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
        return \DB::transaction(function () use ($data) {
            // Check for existing pending payroll
            $pending = $this->repository->findByEmployeePeriod(
                $data->employeeType,
                $data->employeeId,
                $data->year,
                $data->month
            );

            // Calculate cumulative paid amount
            $paidSoFar = $pending ? $pending->paid_amount : 0;
            $totalAmount = $data->totalAmount;
            $newPaidAmount = $paidSoFar + $data->amount;
            
            // Validate payment doesn't exceed total
            if ($newPaidAmount > $totalAmount) {
                throw new \InvalidArgumentException(
                    "Payment amount would exceed total salary. Remaining: " . ($totalAmount - $paidSoFar)
                );
            }
            
            // Create new 'paid' transaction record
            $creationData = PayrollCreationData::fromPayData($data);
            $payroll = $this->repository->createPayroll($creationData);

            // Create expense entry for this payment
            $this->createSalaryExpense($payroll, $data);

            // Check if fully paid
            $isFullyPaid = $newPaidAmount >= $totalAmount;
            
            if ($isFullyPaid) {
                // Delete pending record if exists
                if ($pending) {
                    $pending->delete();
                }
            } else {
                // Create/update pending record showing remaining balance
                $remainingAmount = $totalAmount - $newPaidAmount;
                $this->createRemainingPayroll($data, $newPaidAmount, $remainingAmount, $totalAmount);
            }

            return $payroll;
        });
    }

    private function createRemainingPayroll(PaySalaryPayrollData $data, float $paidSoFar, float $remainingAmount, float $totalAmount): void
    {
        // Delete existing pending record if it exists
        $existing = $this->repository->findByEmployeePeriod(
            $data->employeeType,
            $data->employeeId,
            $data->year,
            $data->month
        );
        
        if ($existing) {
            $existing->delete();
        }
        
        // Create new pending record with updated cumulative paid amount
        $creationData = new PayrollCreationData(
            employeeType: $data->employeeType,
            employeeId: $data->employeeId,
            year: $data->year,
            month: $data->month,
            workingDays: $data->workingDays,
            daysPresent: $data->daysPresent,
            leaveDays: $data->leaveDays,
            annualLeave: $data->annualLeave,
            daysAbsent: $data->daysAbsent,
            basicSalary: $data->basicSalary,
            attendanceAllowance: $data->attendanceAllowance,
            loyaltyBonus: $data->loyaltyBonus,
            otherBonus: $data->otherBonus,
            amount: $totalAmount, // Full monthly salary
            totalAmount: $totalAmount, // Full monthly salary (not remaining)
            paidAmount: $paidSoFar, // Cumulative amount paid so far
            paymentCount: 0, // 0 for pending records
            status: 'pending',
            paymentMethod: null,
            processedBy: null,
        );
        
        $this->repository->createPayroll($creationData);
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

        // Get payment method ID by matching the name
        $paymentMethodName = $data->paymentMethod; // e.g., "Cash", "Bank Transfer", "KBZ Pay"
        $paymentMethod = \App\Models\PaymentMethod::where('name', $paymentMethodName)
            ->orWhere('name', 'like', "%{$paymentMethodName}%")
            ->first();

        // Create expense entry using paid_amount from the payroll transaction
        Expense::create([
            'expense_number' => $expenseNumber,
            'expense_category_id' => $salaryCategory->id,
            'title' => "Salary Payment - {$employeeName}",
            'description' => "Salary payment for {$monthName}. Payroll ID: {$payroll->id}",
            'amount' => $payroll->paid_amount, // Use paid_amount from transaction
            'expense_date' => now(),
            'payment_method_id' => $paymentMethod?->id,
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
