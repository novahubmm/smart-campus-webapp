<?php

namespace Database\Seeders\Demo;

use App\Models\Payroll;
use App\Models\Setting;
use App\Models\StaffProfile;
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
        $this->command->info('Creating Student Fee Records...');

        // First, ensure fee types exist
        $feeTypes = $this->createFeeTypes();
        
        // Set grade monthly fees (price_per_month)
        $this->setGradeMonthlyFees();
        
        // Create fee structures for Transportation Fee only
        $this->createFeeStructures($feeTypes);
        
        // Create invoices for February 2026
        $this->createInvoices($studentProfiles, $feeTypes);
    }
    
    private function setGradeMonthlyFees(): void
    {
        // Set monthly tuition fee for each grade
        $monthlyFees = [
            0 => 80000,   // Kindergarten
            1 => 85000,   // Grade 1
            2 => 90000,   // Grade 2
            3 => 95000,   // Grade 3
            4 => 100000,  // Grade 4
            5 => 105000,  // Grade 5
            6 => 110000,  // Grade 6
            7 => 115000,  // Grade 7
            8 => 120000,  // Grade 8
            9 => 130000,  // Grade 9
            10 => 140000, // Grade 10
            11 => 150000, // Grade 11
            12 => 160000, // Grade 12
        ];
        
        $grades = \App\Models\Grade::all();
        foreach ($grades as $grade) {
            $fee = $monthlyFees[$grade->level] ?? 100000;
            $grade->update(['price_per_month' => $fee]);
        }
    }
    
    private function createFeeTypes(): array
    {
        $feeTypeData = [
            [
                'code' => 'TUITION',
                'name' => 'School Fees (Monthly Recurring)',
                'name_mm' => 'ကျောင်းလခ (လစဉ်)',
                'description' => 'Monthly tuition fee for academic instruction and school facilities',
                'is_mandatory' => true,
                'status' => true,
            ],
            [
                'code' => 'TRANSPORT',
                'name' => 'Transportation Fee',
                'name_mm' => 'ယာဉ်စီးခ',
                'description' => 'Monthly school bus transportation fee for students',
                'is_mandatory' => true,
                'status' => true,
            ],
        ];
        
        $feeTypes = [];
        foreach ($feeTypeData as $data) {
            $feeTypes[$data['code']] = \App\Models\FeeType::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
        
        return $feeTypes;
    }
    
    private function createFeeStructures(array $feeTypes): void
    {
        $batch = \App\Models\Batch::where('name', '2025-2026')->first();
        if (!$batch) {
            return;
        }
        
        $grades = \App\Models\Grade::all();
        
        // Transportation Fee amounts per grade level
        $transportFees = [
            0 => 30000,   // Kindergarten
            1 => 30000,   // Grade 1
            2 => 30000,   // Grade 2
            3 => 30000,   // Grade 3
            4 => 35000,   // Grade 4
            5 => 35000,   // Grade 5
            6 => 35000,   // Grade 6
            7 => 35000,   // Grade 7
            8 => 40000,   // Grade 8
            9 => 40000,   // Grade 9
            10 => 40000,  // Grade 10
            11 => 40000,  // Grade 11
            12 => 40000,  // Grade 12
        ];
        
        // Only create fee structures for Transportation Fee
        foreach ($grades as $grade) {
            $amount = $transportFees[$grade->level] ?? 30000;
            
            if (!isset($feeTypes['TRANSPORT'])) continue;
            
            \App\Models\FeeStructure::updateOrCreate(
                [
                    'grade_id' => $grade->id,
                    'batch_id' => $batch->id,
                    'fee_type_id' => $feeTypes['TRANSPORT']->id,
                ],
                [
                    'amount' => $amount,
                    'frequency' => 'monthly',
                    'status' => true,
                ]
            );
        }
    }
    
    private function createInvoices(array $studentProfiles, array $feeTypes): void
    {
        $batch = \App\Models\Batch::where('name', '2025-2026')->first();
        if (!$batch) {
            return;
        }
        
        $invoiceMonth = Carbon::parse('2026-02-01'); // February 2026
        $dueDate = $invoiceMonth->copy()->endOfMonth();
        
        $invoiceCount = 0;
        foreach ($studentProfiles as $student) {
            $gradeId = $student->grade->id ?? null;
            if (!$gradeId) {
                continue;
            }
            
            // Get the grade with price_per_month
            $grade = \App\Models\Grade::find($gradeId);
            if (!$grade) {
                continue;
            }
            
            // 1. Create invoice for School Fees (Monthly Recurring) using grade's price_per_month
            if ($grade->price_per_month > 0 && isset($feeTypes['TUITION'])) {
                $invoiceCount++;
                
                $tuitionInvoice = \App\Models\Invoice::create([
                    'invoice_number' => 'INV-' . $invoiceMonth->format('Ym') . '-' . str_pad($invoiceCount, 5, '0', STR_PAD_LEFT),
                    'student_id' => $student->id,
                    'invoice_date' => $invoiceMonth,
                    'due_date' => $dueDate,
                    'total_amount' => $grade->price_per_month,
                    'paid_amount' => 0,
                    'balance' => $grade->price_per_month,
                    'status' => 'unpaid',
                ]);
                
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $tuitionInvoice->id,
                    'fee_type_id' => $feeTypes['TUITION']->id,
                    'description' => 'School Fees (Monthly Recurring) - ' . $invoiceMonth->format('F Y'),
                    'quantity' => 1,
                    'unit_price' => $grade->price_per_month,
                    'amount' => $grade->price_per_month,
                ]);
            }
            
            // 2. Create invoice for Transportation Fee from fee structures
            $transportStructure = \App\Models\FeeStructure::where('grade_id', $gradeId)
                ->where('batch_id', $batch->id)
                ->where('fee_type_id', $feeTypes['TRANSPORT']->id)
                ->where('status', true)
                ->with('feeType')
                ->first();
            
            if ($transportStructure) {
                $invoiceCount++;
                
                $transportInvoice = \App\Models\Invoice::create([
                    'invoice_number' => 'INV-' . $invoiceMonth->format('Ym') . '-' . str_pad($invoiceCount, 5, '0', STR_PAD_LEFT),
                    'student_id' => $student->id,
                    'invoice_date' => $invoiceMonth,
                    'due_date' => $dueDate,
                    'total_amount' => $transportStructure->amount,
                    'paid_amount' => 0,
                    'balance' => $transportStructure->amount,
                    'status' => 'unpaid',
                ]);
                
                \App\Models\InvoiceItem::create([
                    'invoice_id' => $transportInvoice->id,
                    'fee_type_id' => $transportStructure->fee_type_id,
                    'description' => $transportStructure->feeType->name . ' - ' . $invoiceMonth->format('F Y'),
                    'quantity' => 1,
                    'unit_price' => $transportStructure->amount,
                    'amount' => $transportStructure->amount,
                ]);
            }
        }
        
        $this->command->info("  Created {$invoiceCount} invoices for February 2026");
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
            'school_name' => 'ယာခင်းရှင်သာ ကိုယ်ပိုင်အထက်တန်းကျောင်း',
            'school_email' => 'info@ykst.edu.mm',
            'school_phone' => '09-443089656, 09-797353346, 09-688989656',
            'school_address' => 'ရန်ကုန်တိုင်းဒေသကြီး၊ မြောက်ဒဂုံမြို့နယ်',
            'principal_name' => $principal?->user?->name ?? 'Principal',
            'setup_completed_school_info' => true,
            'setup_completed_academic' => true,
            'setup_completed_event_and_announcements' => true,
            'setup_completed_time_table_and_attendance' => true,
            'setup_completed_finance' => true,
        ]);
    }
}
