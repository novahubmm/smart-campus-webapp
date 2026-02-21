<?php

use App\Models\Grade;
use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\StudentProfile;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Property 4: Monthly Invoice Generation Completeness
 * 
 * For any month, when monthly invoices are generated, every active student should receive
 * exactly one monthly invoice containing all monthly fee categories for their grade.
 * 
 * **Validates: Requirements 3.1, 3.2**
 */
test('Property 4: every active student receives exactly one monthly invoice', function () {
    $invoiceService = new InvoiceService();
    
    // Run multiple iterations with different scenarios
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Create random number of grades (1-5)
        $numGrades = fake()->numberBetween(1, 5);
        $grades = [];
        
        for ($i = 0; $i < $numGrades; $i++) {
            $grades[] = Grade::factory()->create([
                'level' => $i + 1,
            ]);
        }
        
        // Create random number of students per grade (1-10)
        $studentsByGrade = [];
        foreach ($grades as $grade) {
            $numStudents = fake()->numberBetween(1, 10);
            $students = [];
            
            for ($j = 0; $j < $numStudents; $j++) {
                $students[] = StudentProfile::factory()->create([
                    'grade_id' => $grade->id,
                    'status' => 'active',
                ]);
            }
            
            $studentsByGrade[$grade->id] = $students;
        }
        
        // Create random number of monthly fees per grade (1-5)
        $feesByGrade = [];
        foreach ($grades as $grade) {
            $numFees = fake()->numberBetween(1, 5);
            $fees = [];
            
            for ($k = 0; $k < $numFees; $k++) {
                $fees[] = FeeStructure::factory()->monthly()->active()->create([
                    'grade' => $grade->level,
                    'batch' => '2024-2025',
                    'due_date' => now()->addDays(30),
                ]);
            }
            
            $feesByGrade[$grade->id] = $fees;
        }
        
        // Generate monthly invoices
        $invoicesCreated = $invoiceService->generateMonthlyInvoices();
        
        // Calculate expected number of invoices
        $totalStudents = collect($studentsByGrade)->flatten()->count();
        
        // Property: Every active student should receive exactly one invoice
        expect($invoicesCreated)->toBe($totalStudents);
        
        // Verify each student has exactly one monthly invoice
        foreach ($studentsByGrade as $gradeId => $students) {
            foreach ($students as $student) {
                $invoices = Invoice::where('student_id', $student->id)
                    ->where('invoice_type', 'monthly')
                    ->get();
                
                // Each student should have exactly one monthly invoice
                expect($invoices)->toHaveCount(1);
                
                $invoice = $invoices->first();
                
                // Invoice should contain all monthly fees for the student's grade
                $expectedFeeCount = count($feesByGrade[$gradeId]);
                expect($invoice->fees)->toHaveCount($expectedFeeCount);
                
                // Verify total amount equals sum of all fees
                $expectedTotal = collect($feesByGrade[$gradeId])->sum('amount');
                expect(abs((float)$invoice->total_amount - (float)$expectedTotal))->toBeLessThan(0.01);
            }
        }
        
        // Clean up for next iteration
        Invoice::query()->delete();
        FeeStructure::query()->delete();
        StudentProfile::query()->delete();
        Grade::query()->delete();
    }
});

test('Property 4: students without grades are skipped', function () {
    $invoiceService = new InvoiceService();
    
    // Create students without grades
    $numStudents = fake()->numberBetween(1, 5);
    for ($i = 0; $i < $numStudents; $i++) {
        StudentProfile::factory()->create([
            'grade_id' => null,
            'status' => 'active',
        ]);
    }
    
    // Generate monthly invoices
    $invoicesCreated = $invoiceService->generateMonthlyInvoices();
    
    // No invoices should be created for students without grades
    expect($invoicesCreated)->toBe(0);
    expect(Invoice::count())->toBe(0);
});

test('Property 4: inactive students do not receive invoices', function () {
    $invoiceService = new InvoiceService();
    
    // Create a grade with fees
    $grade = Grade::factory()->create(['level' => 1]);
    FeeStructure::factory()->monthly()->active()->create([
        'grade' => $grade->level,
        'batch' => '2024-2025',
        'due_date' => now()->addDays(30),
    ]);
    
    // Create inactive students
    $numInactiveStudents = fake()->numberBetween(1, 5);
    for ($i = 0; $i < $numInactiveStudents; $i++) {
        StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'inactive',
        ]);
    }
    
    // Create active students
    $numActiveStudents = fake()->numberBetween(1, 5);
    for ($i = 0; $i < $numActiveStudents; $i++) {
        StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
    }
    
    // Generate monthly invoices
    $invoicesCreated = $invoiceService->generateMonthlyInvoices();
    
    // Only active students should receive invoices
    expect($invoicesCreated)->toBe($numActiveStudents);
    expect(Invoice::count())->toBe($numActiveStudents);
});

test('Property 4: grades without monthly fees do not generate invoices', function () {
    $invoiceService = new InvoiceService();
    
    // Create a grade without monthly fees
    $grade = Grade::factory()->create(['level' => 1]);
    
    // Create students for this grade
    $numStudents = fake()->numberBetween(1, 5);
    for ($i = 0; $i < $numStudents; $i++) {
        StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
    }
    
    // Generate monthly invoices
    $invoicesCreated = $invoiceService->generateMonthlyInvoices();
    
    // No invoices should be created for grades without monthly fees
    expect($invoicesCreated)->toBe(0);
    expect(Invoice::count())->toBe(0);
});

test('Property 4: duplicate invoice generation is prevented', function () {
    $invoiceService = new InvoiceService();
    
    // Create a grade with fees
    $grade = Grade::factory()->create(['level' => 1]);
    FeeStructure::factory()->monthly()->active()->create([
        'grade' => $grade->level,
        'batch' => '2024-2025',
        'due_date' => now()->addDays(30),
    ]);
    
    // Create students
    $numStudents = fake()->numberBetween(2, 5);
    for ($i = 0; $i < $numStudents; $i++) {
        StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
    }
    
    // Generate monthly invoices first time
    $firstRun = $invoiceService->generateMonthlyInvoices();
    expect($firstRun)->toBe($numStudents);
    
    // Generate monthly invoices second time (should not create duplicates)
    $secondRun = $invoiceService->generateMonthlyInvoices();
    expect($secondRun)->toBe(0);
    
    // Total invoices should still be equal to number of students
    expect(Invoice::count())->toBe($numStudents);
});

test('Property 4: only monthly fees are included in monthly invoices', function () {
    $invoiceService = new InvoiceService();
    
    // Create a grade
    $grade = Grade::factory()->create(['level' => 1]);
    
    // Create monthly fees
    $numMonthlyFees = fake()->numberBetween(1, 3);
    for ($i = 0; $i < $numMonthlyFees; $i++) {
        FeeStructure::factory()->monthly()->active()->create([
            'grade' => $grade->level,
            'batch' => '2024-2025',
            'due_date' => now()->addDays(30),
        ]);
    }
    
    // Create one-time fees (should not be included)
    $numOneTimeFees = fake()->numberBetween(1, 3);
    for ($i = 0; $i < $numOneTimeFees; $i++) {
        FeeStructure::factory()->oneTime()->active()->create([
            'grade' => $grade->level,
            'batch' => '2024-2025',
            'target_month' => fake()->numberBetween(1, 12),
            'due_date' => now()->addDays(30),
        ]);
    }
    
    // Create a student
    $student = StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    // Generate monthly invoices
    $invoicesCreated = $invoiceService->generateMonthlyInvoices();
    
    expect($invoicesCreated)->toBe(1);
    
    // Get the invoice
    $invoice = Invoice::where('student_id', $student->id)->first();
    
    // Invoice should only contain monthly fees
    expect($invoice->fees)->toHaveCount($numMonthlyFees);
    
    // Verify all fees are monthly
    foreach ($invoice->fees as $invoiceFee) {
        $feeStructure = FeeStructure::find($invoiceFee->fee_id);
        expect($feeStructure->frequency)->toBe('monthly');
    }
});

test('Property 4: invoice amounts are correctly calculated', function () {
    $invoiceService = new InvoiceService();
    
    // Run multiple iterations
    for ($iteration = 0; $iteration < 5; $iteration++) {
        // Create a grade
        $grade = Grade::factory()->create(['level' => $iteration + 1]);
        
        // Create random monthly fees with random amounts
        $numFees = fake()->numberBetween(1, 5);
        $fees = [];
        $expectedTotal = 0;
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 10000, 100000);
            $fees[] = FeeStructure::factory()->monthly()->active()->create([
                'grade' => $grade->level,
                'batch' => '2024-2025',
                'amount' => $amount,
                'due_date' => now()->addDays(30),
            ]);
            $expectedTotal += $amount;
        }
        
        // Create a student
        $student = StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        
        // Generate monthly invoices
        $invoiceService->generateMonthlyInvoices();
        
        // Get the invoice
        $invoice = Invoice::where('student_id', $student->id)->first();
        
        // Verify amounts
        expect(abs((float)$invoice->total_amount - $expectedTotal))->toBeLessThan(0.01);
        expect((float)$invoice->paid_amount)->toBe(0.0);
        expect(abs((float)$invoice->remaining_amount - $expectedTotal))->toBeLessThan(0.01);
        expect($invoice->status)->toBe('pending');
        
        // Clean up for next iteration
        Invoice::query()->delete();
        FeeStructure::query()->delete();
        StudentProfile::query()->delete();
        Grade::query()->delete();
    }
});
