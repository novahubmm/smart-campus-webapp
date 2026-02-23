<?php

namespace App\DTOs\SalaryPayroll;

class PaySalaryPayrollData
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
        public readonly float $totalAmount, // total salary for the month
        public readonly float $amount, // payment amount for this transaction
        // Payment info
        public readonly ?string $paymentMethod,
        public readonly ?string $reference,
        public readonly ?string $receptionistId,
        public readonly ?string $receptionistName,
        public readonly ?string $remark,
        public readonly ?string $notes,
        public readonly ?string $processedBy,
    ) {}

    public static function from(array $payload): self
    {
        $year = isset($payload['year']) ? (int) $payload['year'] : (int) now()->format('Y');
        $month = isset($payload['month']) ? (int) $payload['month'] : (int) now()->format('n');

        $basicSalary = (float) ($payload['basic_salary'] ?? 0);
        $attendanceAllowance = (float) ($payload['attendance_allowance'] ?? 0);
        $loyaltyBonus = (float) ($payload['loyalty_bonus'] ?? 0);
        $otherBonus = (float) ($payload['other_bonus'] ?? 0);
        $totalAmount = (float) ($payload['total_amount'] ?? ($basicSalary + $attendanceAllowance + $loyaltyBonus + $otherBonus));
        $amount = (float) ($payload['amount'] ?? $totalAmount);

        return new self(
            employeeType: $payload['employee_type'],
            employeeId: $payload['employee_id'],
            year: $year,
            month: $month,
            workingDays: (int) ($payload['working_days'] ?? 21),
            daysPresent: (int) ($payload['days_present'] ?? 0),
            leaveDays: (int) ($payload['leave_days'] ?? 0),
            annualLeave: (int) ($payload['annual_leave'] ?? 0),
            daysAbsent: (int) ($payload['days_absent'] ?? 0),
            basicSalary: $basicSalary,
            attendanceAllowance: $attendanceAllowance,
            loyaltyBonus: $loyaltyBonus,
            otherBonus: $otherBonus,
            totalAmount: $totalAmount,
            amount: $amount,
            paymentMethod: $payload['payment_method'] ?? null,
            reference: $payload['reference'] ?? null,
            receptionistId: $payload['receptionist_id'] ?? null,
            receptionistName: $payload['receptionist_name'] ?? null,
            remark: $payload['remark'] ?? null,
            notes: $payload['notes'] ?? null,
            processedBy: $payload['processed_by'] ?? null,
        );
    }
}
