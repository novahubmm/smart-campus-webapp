<?php

namespace App\Http\Requests\SalaryPayroll;

use App\Interfaces\SalaryPayrollRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;

class PayPayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage salary and payroll') ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_type' => ['required', 'in:staff,teacher'],
            'employee_id' => ['required', 'uuid'],
            'year' => ['nullable', 'integer', 'min:2000'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            // Attendance
            'working_days' => ['nullable', 'integer', 'min:0', 'max:31'],
            'days_present' => ['nullable', 'integer', 'min:0', 'max:31'],
            'leave_days' => ['nullable', 'integer', 'min:0', 'max:31'],
            'annual_leave' => ['nullable', 'integer', 'min:0', 'max:31'],
            'days_absent' => ['nullable', 'integer', 'min:0', 'max:31'],
            // Salary components
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'attendance_allowance' => ['nullable', 'numeric', 'min:0'],
            'loyalty_bonus' => ['nullable', 'numeric', 'min:0'],
            'other_bonus' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $repository = app(SalaryPayrollRepositoryInterface::class);
                    $pending = $repository->findByEmployeePeriod(
                        $this->input('employee_type'),
                        $this->input('employee_id'),
                        $this->input('year', now()->year),
                        $this->input('month', now()->month)
                    );
                    
                    $paidSoFar = $pending ? $pending->paid_amount : 0;
                    $totalAmount = $this->input('total_amount');
                    $remainingAmount = $totalAmount - $paidSoFar;
                    
                    if ($value > $remainingAmount) {
                        $fail("Payment amount cannot exceed remaining amount of {$remainingAmount}");
                    }
                },
            ],
            // Payment info
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'receptionist_id' => ['nullable', 'string', 'max:255'],
            'receptionist_name' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
