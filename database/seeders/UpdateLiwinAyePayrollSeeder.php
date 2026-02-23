<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UpdateLiwinAyePayrollSeeder extends Seeder
{
    /**
     * Update payroll record for OG-STF-002 (Liwin Aye) to show paid salary of 200,000 MMK
     */
    public function run(): void
    {
        $this->command->info('Updating payroll for OG-STF-002 (Liwin Aye)...');

        // Find the staff profile with employee_id OG-STF-002
        $staff = StaffProfile::where('employee_id', 'OG-STF-002')->first();

        if (!$staff) {
            $this->command->error('Staff with employee_id OG-STF-002 not found!');
            return;
        }

        $this->command->info("Found staff: {$staff->user->name} (ID: {$staff->employee_id})");

        // Get current year and month
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        // Find or create payroll record for this staff member
        $payroll = Payroll::where('employee_type', 'staff')
            ->where('employee_id', $staff->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$payroll) {
            $this->command->error('Payroll record not found for this staff member!');
            return;
        }

        // Ensure total_amount is set correctly to 340,000 MMK
        // Keep paid_amount at 0 (will show as "-" in the UI)
        // Keep status as 'pending'
        $payroll->update([
            'total_amount' => 340000,
            'paid_amount' => 0,
            'payment_count' => 0,
            'status' => 'pending',
            'paid_at' => null,
            'payment_method' => $staff->payment_method ?? 'Cash',
        ]);

        $this->command->info("✓ Updated payroll for {$staff->user->name}");
        $this->command->info("  - Basic Salary: " . number_format($payroll->basic_salary, 0) . " MMK");
        $this->command->info("  - Total Salary: 340,000 MMK");
        $this->command->info("  - Paid Amount: 0 MMK (shows as '-')");
        $this->command->info("  - Status: pending");
    }
}
