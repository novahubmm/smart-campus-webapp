<?php

namespace App\DTOs\SalaryPayroll;

use App\Models\Payroll;
use App\Models\StaffProfile;
use App\Models\TeacherProfile;

class PayrollEmployeeSummaryData
{
    public function __construct(
        public readonly string $employeeType,
        public readonly string $employeeId,
        public readonly ?string $displayEmployeeId,
        public readonly string $name,
        public readonly string $position,
        public readonly ?string $department,
        public readonly float $basicSalary,
        public readonly ?string $hireDate,
        public readonly string $paymentMethod,
        public readonly ?Payroll $payroll = null,
    ) {}

    public static function fromStaff(StaffProfile $profile, ?Payroll $payroll): self
    {
        return new self(
            employeeType: 'staff',
            employeeId: $profile->id,
            displayEmployeeId: $profile->employee_id,
            name: $profile->user?->name ?? __('Staff'),
            position: $profile->position ?? __('Staff'),
            department: $profile->department?->name,
            basicSalary: (float) ($profile->basic_salary ?? 0),
            hireDate: $profile->hire_date?->format('Y-m-d'),
            paymentMethod: 'Cash',
            payroll: $payroll,
        );
    }

    public static function fromTeacher(TeacherProfile $profile, ?Payroll $payroll): self
    {
        return new self(
            employeeType: 'teacher',
            employeeId: $profile->id,
            displayEmployeeId: $profile->employee_id,
            name: $profile->user?->name ?? __('Teacher'),
            position: $profile->position ?? __('Teacher'),
            department: $profile->department?->name,
            basicSalary: (float) ($profile->basic_salary ?? 0),
            hireDate: $profile->hire_date?->format('Y-m-d'),
            paymentMethod: 'Cash',
            payroll: $payroll,
        );
    }

    public function getTotalSalary(): float
    {
        return $this->payroll ? (float) $this->payroll->total_amount : 0;
    }

    public function getPaidAmount(): float
    {
        return $this->payroll ? (float) $this->payroll->paid_amount : 0;
    }

    public function getRemainingAmount(): float
    {
        return $this->getTotalSalary() - $this->getPaidAmount();
    }
}
