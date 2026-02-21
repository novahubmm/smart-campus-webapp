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

class TestScenario1 extends Command
{
    protected $signature = 'test:scenario1';
    protected $description = 'Test Scenario 1: Standard 1 Month Payment (All Fees)';

    public function handle()
    {
        // Force synchronous queue
        config(['queue.default' => 'sync']);
        
        $this->info('--- STARTING SCENARIO 1 TEST ---');

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
        $this->info("Creating invoice manually (Total: 47,000)...");
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-SC1-' . time(),
            'student_id' => $student->id,
            'academic_year' => '2025-2026',
            'total_amount' => 47000,
            'paid_amount' => 0,
            'remaining_amount' => 47000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'invoice_type' => 'monthly',
        ]);
        
        // Fee Helper
        $createFee = function($name, $amount, $supportsPeriod = false) use ($invoice) {
             $feeStruct = FeeStructure::where('name', 'LIKE', "%$name%")->first();
             // fallback if not found, though we should have it
             $feeId = $feeStruct ? $feeStruct->id : 1; 

             return InvoiceFee::create([
                'invoice_id' => $invoice->id,
                'fee_id' => $feeId, 
                'fee_name' => $name,
                'fee_name_mm' => $name, // Simple fallback
                'amount' => $amount,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
                'supports_payment_period' => $supportsPeriod,
            ]);
        };

        $schoolFee = $createFee('School Fee', 10000, true);
        $transportFee = $createFee('Transportation Fee', 25000, false);
        $bookFee = $createFee('Book Fee', 12000, false);

        
        // 4. Submit Full Payment
        $payAmount = 47000;
        $this->info("Submitting Full Payment of {$payAmount}...");

        $data = [
            'invoice_id' => $invoice->id,
            'payment_amount' => $payAmount,
            'payment_type' => 'full',
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now()->format('Y-m-d'),
            'payment_months' => 1,
            'receipt_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'fee_payment_details' => [
                ['invoice_fee_id' => $schoolFee->id, 'paid_amount' => 10000],
                ['invoice_fee_id' => $transportFee->id, 'paid_amount' => 25000],
                ['invoice_fee_id' => $bookFee->id, 'paid_amount' => 12000],
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
            
            $verificationService = new \App\Services\PaymentSystem\PaymentVerificationService($mockNotification);
            $admin = \App\Models\User::first() ?? \App\Models\User::factory()->create();
            
            $verificationService->verifyPayment($payment, $admin);
            $this->info("Payment Verified.");

            // 6. Checks
            $invoice->refresh();
            $this->info("Invoice Status: " . $invoice->status);
            $this->info("Invoice Remaining: " . $invoice->remaining_amount);

            if ($invoice->status !== 'paid') {
                $this->error("FAILURE: Invoice should be PAID.");
                return 1;
            }

            if ($invoice->remaining_amount != 0) {
                 $this->error("FAILURE: Remaining amount should be 0.");
                 return 1;
            }
            
            // Ensure NO extra invoices (advance or remaining balance)
            $childInvoices = Invoice::where('parent_invoice_id', $invoice->id)->count();
            if ($childInvoices > 0) {
                 $this->error("FAILURE: Should NOT have creation child invoices for standard payment. Found: $childInvoices");
                 return 1;
            }

            $this->info("SUCCESS: Standard Payment Flow Verified.");

        } catch (\Exception $e) {
            $this->error("FAILURE: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        $this->info("--- TEST COMPLETE ---");
        return 0;
    }
}
