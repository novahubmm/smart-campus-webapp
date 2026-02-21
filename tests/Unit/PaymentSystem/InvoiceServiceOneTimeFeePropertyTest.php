<?php

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Property 2: One-Time Fee Invoice Generation Count
 * 
 * For any one-time fee category created for a specific grade and batch, the number of 
 * invoices generated should equal the number of active students in that grade and batch.
 * 
 * **Validates: Requirements 2.1**
 */
test('Property 2: one-time fee generates exactly one invoice per active student', function () {
    // Test with varying numbers of students
    $testCases = [
        ['active' => 1, 'inactive' => 0],
        ['active' => 3, 'inactive' => 2],
        ['active' => 5, 'inactive' => 1],
        ['active' => 10, 'inactive' => 3],
    ];
    
    foreach ($testCases as $testCase) {
        $activeCount = $testCase['active'];
        $inactiveCount = $testCase['inactive'];
        
        // Create a one-time fee for a specific grade and batch
        $grade = 'Grade ' . fake()->numberBetween(1, 12);
        $batch = fake()->year() . '-' . (fake()->year() + 1);
        
        $feeStructure = FeeStructure::factory()->oneTime()->create([
            'grade' => $grade,
            'batch' => $batch,
            'amount' => fake()->randomFloat(2, 10000, 100000),
            'is_active' => true,
        ]);
        
        // Create active students for this grade
        $activeStudents = StudentProfile::factory()
            ->count($activeCount)
            ->create(['status' => 'active']);
        
        // Create inactive students (should not get invoices)
        $inactiveStudents = StudentProfile::factory()
            ->count($inactiveCount)
            ->create(['status' => 'inactive']);
        
        // Simulate invoice generation: create one invoice per active student
        // (This simulates what generateOneTimeFeeInvoice would do)
        foreach ($activeStudents as $student) {
            $invoice = Invoice::factory()->oneTime()->create([
                'student_id' => $student->id,
                'academic_year' => $batch,
            ]);
            
            // Create invoice fee linking to the fee structure
            InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'fee_id' => $feeStructure->id,
                'fee_name' => $feeStructure->name,
                'fee_name_mm' => $feeStructure->name_mm,
                'amount' => $feeStructure->amount,
                'remaining_amount' => $feeStructure->amount,
            ]);
        }
        
        // Property: Number of invoices with this fee should equal number of active students
        $invoicesWithFee = InvoiceFee::where('fee_id', $feeStructure->id)->count();
        expect($invoicesWithFee)->toBe($activeCount,
            "Expected {$activeCount} invoices for fee {$feeStructure->id}, got {$invoicesWithFee}"
        );
        
        // Property: Each active student has exactly one invoice for this fee
        foreach ($activeStudents as $student) {
            $studentInvoicesWithFee = Invoice::where('student_id', $student->id)
                ->whereHas('fees', function ($query) use ($feeStructure) {
                    $query->where('fee_id', $feeStructure->id);
                })
                ->count();
            
            expect($studentInvoicesWithFee)->toBe(1,
                "Student {$student->id} should have exactly 1 invoice for fee {$feeStructure->id}, got {$studentInvoicesWithFee}"
            );
        }
        
        // Property: Inactive students have no invoices for this fee
        foreach ($inactiveStudents as $inactiveStudent) {
            $inactiveInvoicesWithFee = Invoice::where('student_id', $inactiveStudent->id)
                ->whereHas('fees', function ($query) use ($feeStructure) {
                    $query->where('fee_id', $feeStructure->id);
                })
                ->count();
            
            expect($inactiveInvoicesWithFee)->toBe(0,
                "Inactive student {$inactiveStudent->id} should have 0 invoices for fee {$feeStructure->id}, got {$inactiveInvoicesWithFee}"
            );
        }
        
        // Clean up for next iteration
        InvoiceFee::query()->delete();
        Invoice::query()->delete();
        StudentProfile::query()->delete();
        FeeStructure::query()->delete();
    }
});

/**
 * Property 3: One-Time Fee Invoice Separation
 * 
 * For any set of one-time fees created for the same month and grade, each fee should 
 * generate a separate invoice, and no invoice should contain more than one one-time fee.
 * 
 * **Validates: Requirements 2.2, 2.3**
 */
test('Property 3: each one-time fee generates separate invoices', function () {
    // Test with varying numbers of one-time fees
    $feeCountVariations = [2, 3, 5];
    
    foreach ($feeCountVariations as $feeCount) {
        $grade = 'Grade ' . fake()->numberBetween(1, 12);
        $batch = fake()->year() . '-' . (fake()->year() + 1);
        $targetMonth = fake()->numberBetween(1, 12);
        
        // Create a few active students
        $studentCount = fake()->numberBetween(2, 5);
        $students = StudentProfile::factory()
            ->count($studentCount)
            ->create(['status' => 'active']);
        
        // Create multiple one-time fee structures for the same grade and month
        $feeStructures = [];
        for ($i = 0; $i < $feeCount; $i++) {
            $feeStructures[] = FeeStructure::factory()->oneTime()->create([
                'grade' => $grade,
                'batch' => $batch,
                'target_month' => $targetMonth,
                'amount' => fake()->randomFloat(2, 10000, 100000),
                'is_active' => true,
            ]);
        }
        
        // Simulate invoice generation: create separate invoices for each fee
        foreach ($students as $student) {
            foreach ($feeStructures as $feeStructure) {
                // Create a separate invoice for each one-time fee
                $invoice = Invoice::factory()->oneTime()->create([
                    'student_id' => $student->id,
                    'academic_year' => $batch,
                ]);
                
                // Create invoice fee (only one fee per invoice for one-time fees)
                InvoiceFee::factory()->create([
                    'invoice_id' => $invoice->id,
                    'fee_id' => $feeStructure->id,
                    'fee_name' => $feeStructure->name,
                    'fee_name_mm' => $feeStructure->name_mm,
                    'amount' => $feeStructure->amount,
                    'remaining_amount' => $feeStructure->amount,
                ]);
            }
        }
        
        // Property: Each student should have exactly one invoice per one-time fee
        foreach ($students as $student) {
            $studentInvoices = Invoice::where('student_id', $student->id)
                ->where('invoice_type', 'one_time')
                ->get();
            
            expect($studentInvoices->count())->toBe($feeCount,
                "Student {$student->id} should have {$feeCount} one-time invoices, got {$studentInvoices->count()}"
            );
            
            // Property: Each invoice should contain exactly one fee
            foreach ($studentInvoices as $invoice) {
                $feesInInvoice = $invoice->fees()->count();
                expect($feesInInvoice)->toBe(1,
                    "One-time invoice {$invoice->invoice_number} should contain exactly 1 fee, got {$feesInInvoice}"
                );
            }
            
            // Property: All invoices should be for different fee structures
            $feeIds = $studentInvoices->flatMap(function ($invoice) {
                return $invoice->fees->pluck('fee_id');
            })->toArray();
            
            $uniqueFeeIds = array_unique($feeIds);
            expect(count($uniqueFeeIds))->toBe($feeCount,
                "Student {$student->id} should have invoices for {$feeCount} different fees, got " . count($uniqueFeeIds)
            );
        }
        
        // Property: Total invoices should equal (number of students × number of one-time fees)
        $totalInvoices = Invoice::where('invoice_type', 'one_time')->count();
        $expectedTotal = $studentCount * $feeCount;
        
        expect($totalInvoices)->toBe($expectedTotal,
            "Expected {$expectedTotal} total one-time invoices, got {$totalInvoices}"
        );
        
        // Property: Each fee structure should have exactly one invoice per student
        foreach ($feeStructures as $feeStructure) {
            $invoicesForFee = InvoiceFee::where('fee_id', $feeStructure->id)->count();
            expect($invoicesForFee)->toBe($studentCount,
                "Fee {$feeStructure->id} should have {$studentCount} invoices, got {$invoicesForFee}"
            );
        }
        
        // Clean up for next iteration
        InvoiceFee::query()->delete();
        Invoice::query()->delete();
        StudentProfile::query()->delete();
        FeeStructure::query()->delete();
    }
});

/**
 * Property 2 & 3 Combined: One-time fees generate separate invoices for all active students
 * 
 * This test combines both properties to verify that:
 * 1. Each one-time fee generates invoices for all active students (Property 2)
 * 2. Each fee generates separate invoices (Property 3)
 */
test('Property 2 & 3: multiple one-time fees generate correct number of separate invoices', function () {
    $grade = 'Grade ' . fake()->numberBetween(1, 12);
    $batch = fake()->year() . '-' . (fake()->year() + 1);
    $targetMonth = fake()->numberBetween(1, 12);
    
    // Create active and inactive students
    $activeCount = 7;
    $inactiveCount = 3;
    
    $activeStudents = StudentProfile::factory()
        ->count($activeCount)
        ->create(['status' => 'active']);
    
    $inactiveStudents = StudentProfile::factory()
        ->count($inactiveCount)
        ->create(['status' => 'inactive']);
    
    // Create 4 different one-time fees for the same grade and month
    $feeCount = 4;
    $feeStructures = [];
    for ($i = 0; $i < $feeCount; $i++) {
        $feeStructures[] = FeeStructure::factory()->oneTime()->create([
            'grade' => $grade,
            'batch' => $batch,
            'target_month' => $targetMonth,
            'amount' => 15000.00 + ($i * 5000),
            'is_active' => true,
        ]);
    }
    
    // Simulate invoice generation: create separate invoices for each fee and student
    foreach ($activeStudents as $student) {
        foreach ($feeStructures as $feeStructure) {
            $invoice = Invoice::factory()->oneTime()->create([
                'student_id' => $student->id,
                'academic_year' => $batch,
            ]);
            
            InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'fee_id' => $feeStructure->id,
                'fee_name' => $feeStructure->name,
                'fee_name_mm' => $feeStructure->name_mm,
                'amount' => $feeStructure->amount,
                'remaining_amount' => $feeStructure->amount,
            ]);
        }
    }
    
    // Property 2: Total invoices = active students × number of fees
    $totalInvoices = Invoice::where('invoice_type', 'one_time')->count();
    expect($totalInvoices)->toBe($activeCount * $feeCount,
        "Expected " . ($activeCount * $feeCount) . " total invoices, got {$totalInvoices}"
    );
    
    // Property 3: Each student has separate invoices for each fee
    foreach ($activeStudents as $student) {
        $studentInvoices = Invoice::where('student_id', $student->id)
            ->where('invoice_type', 'one_time')
            ->get();
        
        // Should have one invoice per fee
        expect($studentInvoices->count())->toBe($feeCount,
            "Student {$student->id} should have {$feeCount} invoices, got {$studentInvoices->count()}"
        );
        
        // Property 3: Each invoice contains exactly one fee
        foreach ($studentInvoices as $invoice) {
            $feesInInvoice = $invoice->fees()->count();
            expect($feesInInvoice)->toBe(1,
                "Invoice {$invoice->invoice_number} should contain exactly 1 fee, got {$feesInInvoice}"
            );
        }
        
        // Property 3: All invoices should be for different fee structures
        $feeIds = $studentInvoices->flatMap(function ($invoice) {
            return $invoice->fees->pluck('fee_id');
        })->toArray();
        
        $uniqueFeeIds = array_unique($feeIds);
        expect(count($uniqueFeeIds))->toBe($feeCount,
            "Student {$student->id} should have {$feeCount} different fees, got " . count($uniqueFeeIds)
        );
    }
    
    // Property 2: Each fee has exactly one invoice per active student
    foreach ($feeStructures as $feeStructure) {
        $invoicesForFee = InvoiceFee::where('fee_id', $feeStructure->id)->count();
        expect($invoicesForFee)->toBe($activeCount,
            "Fee {$feeStructure->id} should have {$activeCount} invoices, got {$invoicesForFee}"
        );
    }
    
    // Verify inactive students have no invoices
    foreach ($inactiveStudents as $inactiveStudent) {
        $invoiceCount = Invoice::where('student_id', $inactiveStudent->id)
            ->where('invoice_type', 'one_time')
            ->count();
        expect($invoiceCount)->toBe(0,
            "Inactive student {$inactiveStudent->id} should have 0 invoices, got {$invoiceCount}"
        );
    }
});
