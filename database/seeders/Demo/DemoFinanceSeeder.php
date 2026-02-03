<?php

namespace Database\Seeders\Demo;

use App\Models\Payroll;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\StudentFee;
use Carbon\Carbon;

class DemoFinanceSeeder extends DemoBaseSeeder
{
    public function run(array $studentProfiles, array $teacherProfiles, array $staffProfiles): void
    {
        $this->createStudentFees($studentProfiles);
        $this->createPayrollRecords($teacherProfiles, $staffProfiles);
        $this->updateSettings();
    }

    private function createStudentFees(array $studentProfiles): void
    {
        $this->command->info('Creating Student Fee Records (1,170)...');

        $dueDate = Carbon::now()->endOfMonth();

        foreach ($studentProfiles as $student) {
            $amount = $student->grade->price_per_month ?? rand(5000, 20000);

            $rand = rand(1, 100);
            if ($rand <= 60) {
                $status = 'paid';
                $amountPaid = $amount;
            } elseif ($rand <= 90) {
                $status = 'pending';
                $amountPaid = 0;
            } else {
                $status = 'partial';
                $amountPaid = rand(1000, $amount - 1000);
            }

            StudentFee::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'amount_due' => $amount,
                'amount_paid' => $amountPaid,
                'balance' => $amount - $amountPaid,
                'due_date' => $dueDate,
                'status' => $status,
            ]);
        }
    }

    private function createPayrollRecords(array $teacherProfiles, array $staffProfiles): void
    {
        $this->command->info('Creating Payroll Records (88)...');

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $workingDaysInMonth = 22;

        foreach ($teacherProfiles as $teacher) {
            $daysPresent = rand(18, 22);
            $leaveDays = rand(0, 2);
            $daysAbsent = max(0, $workingDaysInMonth - $daysPresent - $leaveDays);

            $basicSalary = $teacher->basic_salary;
            $attendanceAllowance = $daysPresent >= 20 ? 50000 : 0;
            $loyaltyBonus = rand(0, 100000);
            $totalAmount = $basicSalary + $attendanceAllowance + $loyaltyBonus;

            Payroll::create([
                'employee_type' => 'teacher',
                'employee_id' => $teacher->id,
                'year' => $year,
                'month' => $month,
                'working_days' => $workingDaysInMonth,
                'days_present' => $daysPresent,
                'leave_days' => $leaveDays,
                'days_absent' => $daysAbsent,
                'basic_salary' => $basicSalary,
                'attendance_allowance' => $attendanceAllowance,
                'loyalty_bonus' => $loyaltyBonus,
                'other_bonus' => 0,
                'amount' => $totalAmount,
                'status' => rand(0, 1) ? 'pending' : 'paid',
            ]);
        }

        foreach ($staffProfiles as $staff) {
            $daysPresent = rand(18, 22);
            $leaveDays = rand(0, 2);
            $daysAbsent = max(0, $workingDaysInMonth - $daysPresent - $leaveDays);

            $basicSalary = $staff->basic_salary;
            $attendanceAllowance = $daysPresent >= 20 ? 50000 : 0;
            $loyaltyBonus = rand(0, 50000);
            $totalAmount = $basicSalary + $attendanceAllowance + $loyaltyBonus;

            Payroll::create([
                'employee_type' => 'staff',
                'employee_id' => $staff->id,
                'year' => $year,
                'month' => $month,
                'working_days' => $workingDaysInMonth,
                'days_present' => $daysPresent,
                'leave_days' => $leaveDays,
                'days_absent' => $daysAbsent,
                'basic_salary' => $basicSalary,
                'attendance_allowance' => $attendanceAllowance,
                'loyalty_bonus' => $loyaltyBonus,
                'other_bonus' => 0,
                'amount' => $totalAmount,
                'status' => rand(0, 1) ? 'pending' : 'paid',
            ]);
        }
    }

    private function updateSettings(): void
    {
        $this->command->info('Updating Settings...');

        $principal = StaffProfile::where('position', 'Principal')->first();

        Setting::first()?->update([
            'school_name' => 'Smart Campus International School',
            'school_email' => 'info@smartcampusedu.com',
            'school_phone' => '+95 9 123 456 789',
            'school_address' => 'No. 123, University Avenue, Yangon, Myanmar',
            'principal_name' => $principal?->user?->name ?? 'Principal',
            'setup_completed_school_info' => true,
            'setup_completed_academic' => true,
            'setup_completed_event_and_announcements' => true,
            'setup_completed_time_table_and_attendance' => true,
            'setup_completed_finance' => true,
        ]);
    }
}
