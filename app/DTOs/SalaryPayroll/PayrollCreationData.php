<?php

namespace App\DTOs\SalaryPayroll;

class PayrollCreationData
{
    public function __construct(
        public readonly string $employeeType,
        public readonly string $employeeId,
        public readonly int $year,
        public readonly int $month,
        // Attendance
        public readonly int $workingDays,
        public readonly int $daysPresent,
        public readonly int $leaveDays,
        public readonly int $annualLeave,
        public readonly int $daysAbsent,
        // Salary components
        public readonly float $basicSalary,
        public readonly float $attendanceAllowance,
        public readonly float $loyaltyBonus,
        public readonly float $otherBonus,
        public readonly float $amount,
        public readonly float $totalAmount = 0,
        public readonly float $paidAmount = 0,
        public readonly int $paymentCount = 0,
        // Payment info
        public readonly string $status = 'pending',
        public readonly ?string $paymentMethod = null,
        public readonly ?string $processedBy = null,
    ) {}

    public static function fromPayData(PaySalaryPayrollData $data): self
    {
        return new self(
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
            amount: $data->amount,
            totalAmount: $data->totalAmount,
            paidAmount: $data->amount,
            paymentCount: 1,
            status: 'paid',
            paymentMethod: $data->paymentMethod,
            processedBy: $data->processedBy,
        );
    }
}
