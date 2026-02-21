<?php

namespace Tests\Feature;

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentPromotion;
use App\Models\User;
use App\Services\PaymentSystem\PaymentProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PaymentCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_calculation_with_mixed_months()
    {
        // Create User and StudentProfile
        $user = User::factory()->create();
        $student = \App\Models\StudentProfile::create([
            'user_id' => $user->id,
            'student_id' => 'STU-2025-0001',
            'student_identifier' => 'STU-001-' . uniqid(),
            // Add other required fields if any. Usually minimal is fine if nullable.
        ]);
        
        $this->actingAs($user);

        // Create Invoice
        $invoice = Invoice::create([
            'student_id' => $student->id,
            'invoice_number' => 'TEST-INV-001',
            'total_amount' => 70000,
            'paid_amount' => 0,
            'remaining_amount' => 70000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'academic_year' => '2025-2026',
            'invoice_type' => 'monthly'
        ]);

        // Create Fee Structures
        $schoolFeeStructure = \App\Models\PaymentSystem\FeeStructure::create([
             'name' => 'School Fee',
             'name_mm' => 'School Fee MM',
             'amount' => 50000,
             'fee_type' => 'tuition',
             'frequency' => 'monthly',
             'grade' => 'G-10',
             'batch' => '2025',
             'due_date' => now()->addDays(30),
             'is_active' => true,
        ]);
        
        $busFeeStructure = \App\Models\PaymentSystem\FeeStructure::create([
             'name' => 'Bus Fee',
             'name_mm' => 'Bus Fee MM',
             'amount' => 20000,
             'fee_type' => 'transport',
             'frequency' => 'monthly',
             'grade' => 'G-10',
             'batch' => '2025',
             'due_date' => now()->addDays(30),
             'is_active' => true,
        ]);

        // 1. School Fee (Supports Discount) - 50,000
        $schoolFee = InvoiceFee::create([
            'invoice_id' => $invoice->id,
            'fee_id' => $schoolFeeStructure->id,
            'fee_name' => 'School Fee',
            'fee_name_mm' => 'School Fee MM',
            'amount' => 50000,
            'paid_amount' => 0,
            'remaining_amount' => 50000,
            'due_date' => now()->addDays(30),
        ]);

        // 2. Bus Fee (No Discount) - 20,000
        $otherFee = InvoiceFee::create([
            'invoice_id' => $invoice->id,
            'fee_id' => $busFeeStructure->id,
            'fee_name' => 'Bus Fee',
            'fee_name_mm' => 'Bus Fee MM',
            'amount' => 20000,
            'paid_amount' => 0,
            'remaining_amount' => 20000,
            'due_date' => now()->addDays(30),
        ]);
        
        // Setup Promotion: 3 months = 5% off
        PaymentPromotion::updateOrCreate(
            ['months' => 3],
            [
                'name' => '3 Months Promo',
                'discount_percent' => 5,
                'is_active' => true
            ]
        );
        
        // Need a Payment Method
        $method = \App\Models\PaymentMethod::create([
            'name' => 'Cash',
            'type' => 'bank', // required enum
            'account_number' => '123',
            'account_name' => 'test'
        ]);

        // Payment Data: School Fee for 3 months, Other Fee for 1 month
        $paymentData = [
            'payment_type' => 'full',
            'payment_months' => 1, // Ignored
            'fee_payment_months' => [
                $schoolFee->id => 3,
                $otherFee->id => 1
            ],
            'payment_method_id' => $method->id,
            'payment_date' => now(),
        ];

        // Calculation:
        // School Fee: 50,000 * 3 = 150,000. Discount 5% = 7,500. Net: 142,500.
        // Bus Fee: 20,000 * 1 = 20,000. No discount. Net: 20,000.
        // Total Expected: 162,500.
        // Total Expected Discount: 7,500.

        $service = app(PaymentProcessingService::class);
        $result = $service->processPayment($invoice, $paymentData);

        $this->assertTrue($result['success'], $result['error'] ?? 'Payment failed');
        $this->assertEquals(162500, $result['payment']->payment_amount);
        $this->assertEquals(7500, $result['discount_applied']);
    }
}
