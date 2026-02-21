<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\StudentProfile;
use App\Models\PaymentMethod;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\FeeStructure;
use App\Services\PaymentSystem\PaymentService;

class TestScenario2 extends Command
{
    protected $signature = 'test:scenario2';
    protected $description = 'Test Scenario 2: Multi-Month Payment (School Fee x 3 Months)';

    public function handle()
    {
        // Force synchronous queue
        config(['queue.default' => 'sync']);
        
        $this->info('--- STARTING SCENARIO 2 TEST ---');

        // 1. Setup
        $student = StudentProfile::first();
        if (!$student) { $this->error('No student found.'); return 1; }
        
        $paymentMethod = PaymentMethod::first() ?? \App\Models\PaymentSystem\PaymentMethod::first();
        if (!$paymentMethod) { $this->error('No payment method found.'); return 1; }

        $this->info("Using Student: {$student->id}");
        
        // 2. Cleanup
        $this->info("Cleaning up old data...");
        $invoices = Invoice::where('student_id', $student->id)->pluck('id');
        if ($invoices->count() > 0) {
            InvoiceFee::whereIn('invoice_id', $invoices)->delete();
            \App\Models\PaymentSystem\Payment::whereIn('invoice_id', $invoices)->delete();
            Invoice::whereIn('id', $invoices)->delete();
        }

        // 3. Create Invoice
        $this->info("Creating invoice manually (School Fee: 10,000)...");
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-SC2-' . time(),
            'student_id' => $student->id,
            'academic_year' => '2025-2026',
            'total_amount' => 47000,
            'paid_amount' => 0,
            'remaining_amount' => 47000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'invoice_type' => 'monthly',
        ]);

        $schoolFeeStruct = FeeStructure::where('name', 'School Fee')->first();
        if (!$schoolFeeStruct) { $this->error('School Fee Structure not found'); return 1; }

        $schoolFee = InvoiceFee::create([
            'invoice_id' => $invoice->id,
            'fee_id' => $schoolFeeStruct->id,
            'fee_name' => 'School Fee',
            'fee_name_mm' => 'ကျောင်းလခ',
            'amount' => 10000,
            'paid_amount' => 0,
            'remaining_amount' => 10000,
            'due_date' => now()->addDays(30),
            'status' => 'unpaid',
            'supports_payment_period' => true,
        ]);

        // Add other fees to match full invoice if needed, but we focus on School Fee
        
        // 4. Simulate Multi-Month Payment
        // 3 Months = 30,000. Discount 5% = 1,500. Total = 28,500.
        $payAmount = 28500; 
        $months = 3;
        
        $this->info("Submitting Payment of {$payAmount} for School Fee (3 Months)...");

        $data = [
            'invoice_id' => $invoice->id,
            'payment_amount' => $payAmount,
            'payment_type' => 'full', // or partial? logicaly it's full + advance
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now()->format('Y-m-d'),
            'payment_months' => $months,
            'receipt_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $schoolFee->id,
                    'paid_amount' => $payAmount,
                    'supports_payment_period' => true
                ]
            ]
        ];

        try {
            $paymentService = app(PaymentService::class);
            $payment = $paymentService->submitPayment($data);
            $this->info("Payment Submitted: {$payment->payment_number}");

            // 5. Verify Payment
            $this->info("Verifying Payment...");
            
            // Mock NotificationService
            $mockNotification = \Mockery::mock(\App\Services\PaymentSystem\NotificationService::class);
            $mockNotification->shouldReceive('notifyGuardianOfVerification')->andReturn(true);
            
            // Instantiate Verification Service manually
            $verificationService = new \App\Services\PaymentSystem\PaymentVerificationService($mockNotification);
            
            $admin = \App\Models\User::first() ?? \App\Models\User::factory()->create();
            
            $verificationService->verifyPayment($payment, $admin);
            $this->info("Payment Verified.");

            // 6. Check Advance Invoices
            $this->info("Checking for Advance Invoices...");
            // Expected: 2 new invoices (Month 2 and Month 3) linked to Parent
            
            $advanceInvoices = Invoice::where('parent_invoice_id', $invoice->id)->get();
            
            if ($advanceInvoices->count() === 2) {
                $this->info("SUCCESS: Found {$advanceInvoices->count()} Advance Invoices.");
                foreach ($advanceInvoices as $advInv) {
                     $this->info(" - Invoice: {$advInv->invoice_number}, Amount: {$advInv->total_amount}, Status: {$advInv->status}");
                     if ($advInv->status !== 'paid') {
                         $this->error("   ERROR: Invoice status is {$advInv->status}, expected 'paid'.");
                     }
                }
            } else {
                $this->error("FAILURE: Expected 2 Advance Invoices, found {$advanceInvoices->count()}.");
            }

        } catch (\Exception $e) {
            $this->error("FAILURE: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        $this->info("--- TEST COMPLETE ---");
        return 0;
    }
}
