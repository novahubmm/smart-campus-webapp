<?php

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\FeeStructure;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Property 17: Remaining Balance Invoice Generation
 * 
 * For any partial payment that results in remaining_amount > 0, a new invoice should be created
 * with invoice_type="remaining_balance", containing only the fees with remaining balances,
 * and the amounts should match the remaining amounts from the original invoice.
 * 
 * **Validates: Requirements 14.1, 14.2, 14.3, 14.4**
 */
test('Property 17: remaining balance invoice is created with correct fees and amounts', function () {
    $invoiceService = new InvoiceService();
    
    // Create a test student
    $user = User::factory()->create();
    $student = StudentProfile::factory()->create(['user_id' => $user->id]);
    
    // Test with multiple scenarios
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create an original invoice with random number of fees (2-5 fees)
        $feeCount = fake()->numberBetween(2, 5);
        
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'invoice_type' => 'monthly',
            'status' => 'partial',
            'academic_year' => '2024-2025',
        ]);
        
        $totalAmount = 0;
        $totalPaidAmount = 0;
        $feesWithBalance = [];
        $feesFullyPaid = [];
        
        // Create fees with varying payment states
        for ($i = 0; $i < $feeCount; $i++) {
            $feeAmount = fake()->randomFloat(2, 10000, 100000);
            
            // Randomly decide if this fee has remaining balance or is fully paid
            $hasRemainingBalance = fake()->boolean(70); // 70% chance of having remaining balance
            
            if ($hasRemainingBalance) {
                // Partial payment: paid between 10% and 90% of the fee
                $paidAmount = fake()->randomFloat(2, $feeAmount * 0.1, $feeAmount * 0.9);
                $remainingAmount = $feeAmount - $paidAmount;
                $status = 'partial';
                
                $feesWithBalance[] = [
                    'amount' => $feeAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                ];
            } else {
                // Fully paid
                $paidAmount = $feeAmount;
                $remainingAmount = 0;
                $status = 'paid';
                
                $feesFullyPaid[] = [
                    'amount' => $feeAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                ];
            }
            
            $feeStructure = FeeStructure::factory()->create([
                'amount' => $feeAmount,
                'frequency' => 'monthly',
            ]);
            
            InvoiceFee::factory()->create([
                'invoice_id' => $originalInvoice->id,
                'fee_id' => $feeStructure->id,
                'fee_name' => "Test Fee $i",
                'fee_name_mm' => "Test Fee MM $i",
                'amount' => $feeAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'status' => $status,
                'supports_payment_period' => fake()->boolean(),
                'due_date' => now()->addDays(30),
            ]);
            
            $totalAmount += $feeAmount;
            $totalPaidAmount += $paidAmount;
        }
        
        // Update original invoice totals
        $originalInvoice->update([
            'total_amount' => $totalAmount,
            'paid_amount' => $totalPaidAmount,
            'remaining_amount' => $totalAmount - $totalPaidAmount,
        ]);
        
        // Only proceed if there are fees with remaining balance
        if (empty($feesWithBalance)) {
            continue;
        }
        
        // Generate remaining balance invoice
        $remainingBalanceInvoice = $invoiceService->generateRemainingBalanceInvoice($originalInvoice);
        
        // **Requirement 14.1**: Check if remaining balance exists after partial payment
        expect($originalInvoice->remaining_amount)->toBeGreaterThan(0);
        
        // **Requirement 14.2**: Verify invoice_type is "remaining_balance"
        expect($remainingBalanceInvoice->invoice_type)->toBe('remaining_balance');
        
        // **Requirement 14.3**: Verify only fees with remaining amounts are included
        $remainingBalanceFees = $remainingBalanceInvoice->fees;
        expect($remainingBalanceFees->count())->toBe(count($feesWithBalance));
        
        // **Requirement 14.4**: Verify amounts match the remaining amounts from original invoice
        $expectedTotalRemaining = array_sum(array_column($feesWithBalance, 'remaining_amount'));
        // Use toEqualWithDelta for floating point comparison (allow 0.01 difference)
        expect($remainingBalanceInvoice->total_amount)->toEqualWithDelta($expectedTotalRemaining, 0.01);
        expect($remainingBalanceInvoice->remaining_amount)->toEqualWithDelta($expectedTotalRemaining, 0.01);
        expect((float)$remainingBalanceInvoice->paid_amount)->toBe(0.0);
        
        // Verify each fee in the remaining balance invoice
        foreach ($remainingBalanceFees as $index => $newFee) {
            // Find the corresponding original fee with remaining balance
            $matchingOriginalFee = $feesWithBalance[$index] ?? null;
            
            if ($matchingOriginalFee) {
                // The new fee's amount should equal the original fee's remaining amount
                // Use toEqualWithDelta for floating point comparison
                expect($newFee->amount)->toEqualWithDelta($matchingOriginalFee['remaining_amount'], 0.01);
                expect($newFee->remaining_amount)->toEqualWithDelta($matchingOriginalFee['remaining_amount'], 0.01);
                expect((float)$newFee->paid_amount)->toBe(0.0);
                expect($newFee->status)->toBe('unpaid');
            }
        }
        
        // Verify parent_invoice_id links to original invoice
        expect($remainingBalanceInvoice->parent_invoice_id)->toBe($originalInvoice->id);
        
        // Verify status is pending
        expect($remainingBalanceInvoice->status)->toBe('pending');
        
        // Verify student_id and academic_year are preserved
        expect($remainingBalanceInvoice->student_id)->toBe($originalInvoice->student_id);
        expect($remainingBalanceInvoice->academic_year)->toBe($originalInvoice->academic_year);
    }
});

test('Property 17: remaining balance invoice excludes fully paid fees', function () {
    $invoiceService = new InvoiceService();
    
    // Create a test student
    $user = User::factory()->create();
    $student = StudentProfile::factory()->create(['user_id' => $user->id]);
    
    // Test multiple scenarios
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Create an original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'invoice_type' => 'monthly',
            'status' => 'partial',
            'academic_year' => '2024-2025',
        ]);
        
        $totalAmount = 0;
        $totalPaidAmount = 0;
        $partialFeeCount = fake()->numberBetween(1, 3);
        $paidFeeCount = fake()->numberBetween(1, 3);
        
        // Create some partially paid fees
        for ($i = 0; $i < $partialFeeCount; $i++) {
            $feeAmount = fake()->randomFloat(2, 10000, 100000);
            $paidAmount = fake()->randomFloat(2, $feeAmount * 0.1, $feeAmount * 0.9);
            $remainingAmount = $feeAmount - $paidAmount;
            
            $feeStructure = FeeStructure::factory()->create(['amount' => $feeAmount]);
            
            InvoiceFee::factory()->create([
                'invoice_id' => $originalInvoice->id,
                'fee_id' => $feeStructure->id,
                'amount' => $feeAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'status' => 'partial',
            ]);
            
            $totalAmount += $feeAmount;
            $totalPaidAmount += $paidAmount;
        }
        
        // Create some fully paid fees
        for ($i = 0; $i < $paidFeeCount; $i++) {
            $feeAmount = fake()->randomFloat(2, 10000, 100000);
            
            $feeStructure = FeeStructure::factory()->create(['amount' => $feeAmount]);
            
            InvoiceFee::factory()->create([
                'invoice_id' => $originalInvoice->id,
                'fee_id' => $feeStructure->id,
                'amount' => $feeAmount,
                'paid_amount' => $feeAmount,
                'remaining_amount' => 0,
                'status' => 'paid',
            ]);
            
            $totalAmount += $feeAmount;
            $totalPaidAmount += $feeAmount;
        }
        
        // Update original invoice totals
        $originalInvoice->update([
            'total_amount' => $totalAmount,
            'paid_amount' => $totalPaidAmount,
            'remaining_amount' => $totalAmount - $totalPaidAmount,
        ]);
        
        // Generate remaining balance invoice
        $remainingBalanceInvoice = $invoiceService->generateRemainingBalanceInvoice($originalInvoice);
        
        // Verify only partial fees are included (fully paid fees excluded)
        expect($remainingBalanceInvoice->fees->count())->toBe($partialFeeCount);
        
        // Verify no fee in the remaining balance invoice has zero amount
        foreach ($remainingBalanceInvoice->fees as $fee) {
            expect($fee->amount)->toBeGreaterThan(0);
            expect($fee->remaining_amount)->toBeGreaterThan(0);
        }
    }
});

test('Property 17: remaining balance invoice preserves fee metadata', function () {
    $invoiceService = new InvoiceService();
    
    // Create a test student
    $user = User::factory()->create();
    $student = StudentProfile::factory()->create(['user_id' => $user->id]);
    
    // Test multiple scenarios
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Create an original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'invoice_type' => 'monthly',
            'status' => 'partial',
        ]);
        
        $feeCount = fake()->numberBetween(2, 4);
        $totalAmount = 0;
        $totalPaidAmount = 0;
        
        // Create fees with various metadata
        for ($i = 0; $i < $feeCount; $i++) {
            $feeAmount = fake()->randomFloat(2, 10000, 100000);
            $paidAmount = fake()->randomFloat(2, $feeAmount * 0.1, $feeAmount * 0.9);
            $remainingAmount = $feeAmount - $paidAmount;
            $supportsPaymentPeriod = fake()->boolean();
            
            $feeStructure = FeeStructure::factory()->create([
                'amount' => $feeAmount,
                'supports_payment_period' => $supportsPaymentPeriod,
            ]);
            
            InvoiceFee::factory()->create([
                'invoice_id' => $originalInvoice->id,
                'fee_id' => $feeStructure->id,
                'fee_name' => "Fee Name $i",
                'fee_name_mm' => "Fee Name MM $i",
                'amount' => $feeAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'supports_payment_period' => $supportsPaymentPeriod,
                'status' => 'partial',
            ]);
            
            $totalAmount += $feeAmount;
            $totalPaidAmount += $paidAmount;
        }
        
        // Update original invoice totals
        $originalInvoice->update([
            'total_amount' => $totalAmount,
            'paid_amount' => $totalPaidAmount,
            'remaining_amount' => $totalAmount - $totalPaidAmount,
        ]);
        
        // Generate remaining balance invoice
        $remainingBalanceInvoice = $invoiceService->generateRemainingBalanceInvoice($originalInvoice);
        
        // Verify fee metadata is preserved
        $originalFees = $originalInvoice->fees()->where('remaining_amount', '>', 0)->get();
        $newFees = $remainingBalanceInvoice->fees;
        
        expect($newFees->count())->toBe($originalFees->count());
        
        foreach ($newFees as $index => $newFee) {
            $originalFee = $originalFees[$index];
            
            // Verify fee_id is preserved
            expect($newFee->fee_id)->toBe($originalFee->fee_id);
            
            // Verify fee names are preserved
            expect($newFee->fee_name)->toBe($originalFee->fee_name);
            expect($newFee->fee_name_mm)->toBe($originalFee->fee_name_mm);
            
            // Verify supports_payment_period is preserved
            expect($newFee->supports_payment_period)->toBe($originalFee->supports_payment_period);
        }
    }
});
