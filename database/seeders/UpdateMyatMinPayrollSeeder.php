<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateMyatMinPayrollSeeder extends Seeder
{
    /**
     * Create two separate payment records for OG-STF-003 (Myat Min)
     * - First payment: 100,000 MMK
     * - Second payment: 60,000 MMK
     * Total: 160,000 MMK out of 360,000 MMK
     */
    public function run(): void
    {
        $this->command->info('Creating payment records for OG-STF-003 (Myat Min)...');

        // Find the staff profile with employee_id OG-STF-003
        $staff = StaffProfile::where('employee_id', 'OG-STF-003')->first();

        if (!$staff) {
            $this->command->error('Staff with employee_id OG-STF-003 not found!');
            return;
        }

        $this->command->info("Found staff: {$staff->user->name} (ID: {$staff->employee_id})");

        // Get current year and month
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        // Delete existing payroll records for this staff member in this period
        Payroll::where('employee_type', 'staff')
            ->where('employee_id', $staff->id)
            ->where('year', $year)
            ->where('month', $month)
            ->delete();

        $this->command->info('Deleted existing payroll records');

        // Create first payment record: 100,000 MMK
        $payment1 = Payroll::create([
            'employee_type' => 'staff',
            'employee_id' => $staff->id,
            'year' => $year,
            'month' => $month,
            'working_days' => 22,
            'days_present' => 20,
            'leave_days' => 2,
            'days_absent' => 0,
            'basic_salary' => $staff->basic_salary,
            'attendance_allowance' => 0,
            'loyalty_bonus' => 0,
            'other_bonus' => 0,
            'amount' => 360000, // Total salary
            'total_amount' => 360000, // Total salary
            'paid_amount' => 100000, // First payment
            'payment_count' => 1,
            'is_fully_paid' => false,
            'status' => 'paid',
            'paid_at' => Carbon::parse('2026-02-15 10:30:00'),
            'payment_method' => 'Cash',
            'remark' => 'First payment - 100,000 MMK',
        ]);

        $this->command->info("✓ Created first payment record");
        $this->command->info("  - Payment Date: Feb 15, 2026");
        $this->command->info("  - Amount: 100,000 MMK");

        // Create second payment record: 60,000 MMK
        $payment2 = Payroll::create([
            'employee_type' => 'staff',
            'employee_id' => $staff->id,
            'year' => $year,
            'month' => $month,
            'working_days' => 22,
            'days_present' => 20,
            'leave_days' => 2,
            'days_absent' => 0,
            'basic_salary' => $staff->basic_salary,
            'attendance_allowance' => 0,
            'loyalty_bonus' => 0,
            'other_bonus' => 0,
            'amount' => 360000, // Total salary
            'total_amount' => 360000, // Total salary
            'paid_amount' => 60000, // Second payment
            'payment_count' => 1,
            'is_fully_paid' => false,
            'status' => 'paid',
            'paid_at' => Carbon::parse('2026-02-23 14:15:00'),
            'payment_method' => 'Cash',
            'remark' => 'Second payment - 60,000 MMK',
        ]);

        $this->command->info("✓ Created second payment record");
        $this->command->info("  - Payment Date: Feb 23, 2026");
        $this->command->info("  - Amount: 60,000 MMK");

        $this->command->info("");
        $this->command->info("Summary for {$staff->user->name}:");
        $this->command->info("  - Total Salary: 360,000 MMK");
        $this->command->info("  - Total Paid: 160,000 MMK (100,000 + 60,000)");
        $this->command->info("  - Remaining: 200,000 MMK");
    }
}
