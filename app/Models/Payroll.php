<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payroll extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_type',
        'employee_id',
        'year',
        'month',
        // Attendance
        'working_days',
        'days_present',
        'leave_days',
        'annual_leave',
        'days_absent',
        // Salary components
        'basic_salary',
        'attendance_allowance',
        'loyalty_bonus',
        'other_bonus',
        'amount', // total salary
        // Payment info
        'status',
        'processed_by',
        'paid_at',
        'payment_method',
        'reference',
        'receptionist_id',
        'receptionist_name',
        'remark',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'working_days' => 'integer',
        'days_present' => 'integer',
        'leave_days' => 'integer',
        'annual_leave' => 'integer',
        'days_absent' => 'integer',
        'basic_salary' => 'decimal:0',
        'attendance_allowance' => 'decimal:0',
        'loyalty_bonus' => 'decimal:0',
        'other_bonus' => 'decimal:0',
        'amount' => 'decimal:0',
        'paid_at' => 'datetime',
    ];

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getTotalSalaryAttribute(): float
    {
        return (float) $this->basic_salary + (float) $this->attendance_allowance + (float) $this->loyalty_bonus + (float) $this->other_bonus;
    }
}
