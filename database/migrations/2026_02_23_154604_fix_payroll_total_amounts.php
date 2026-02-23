<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix payroll records where total_amount was incorrectly set to remaining amount
     * instead of the original total salary.
     * 
     * The issue: When a partial payment was made, the system created a new "pending" 
     * payroll record with total_amount set to the remaining amount instead of the 
     * original total salary.
     * 
     * The fix: For each employee/period, find all payroll records and ensure they 
     * all have the same total_amount (the original total salary).
     */
    public function up(): void
    {
        // Get all payroll records grouped by employee and period
        $payrolls = DB::table('payrolls')
            ->select('employee_type', 'employee_id', 'year', 'month')
            ->groupBy('employee_type', 'employee_id', 'year', 'month')
            ->get();

        foreach ($payrolls as $group) {
            // Get all records for this employee/period
            $records = DB::table('payrolls')
                ->where('employee_type', $group->employee_type)
                ->where('employee_id', $group->employee_id)
                ->where('year', $group->year)
                ->where('month', $group->month)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($records->count() <= 1) {
                continue; // No issue if there's only one record
            }

            // The first record should have the correct total_amount
            $correctTotalAmount = $records->first()->total_amount;
            $correctBasicSalary = $records->first()->basic_salary;
            $correctAttendanceAllowance = $records->first()->attendance_allowance;
            $correctLoyaltyBonus = $records->first()->loyalty_bonus;
            $correctOtherBonus = $records->first()->other_bonus;

            // Calculate the correct total from components
            $calculatedTotal = $correctBasicSalary + $correctAttendanceAllowance + $correctLoyaltyBonus + $correctOtherBonus;
            
            // Use the calculated total if it's different from stored total
            $finalTotalAmount = $calculatedTotal > 0 ? $calculatedTotal : $correctTotalAmount;

            // Update all records in this group to have the same total_amount
            foreach ($records as $record) {
                if ($record->total_amount != $finalTotalAmount || $record->amount != $finalTotalAmount) {
                    DB::table('payrolls')
                        ->where('id', $record->id)
                        ->update([
                            'total_amount' => $finalTotalAmount,
                            'amount' => $finalTotalAmount,
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we don't know the original incorrect values
    }
};
