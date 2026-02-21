<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestScenario6 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scenario6';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Scenario 6: Partial Payment & Remaining Balance Invoice';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Force synchronous queue for testing
        config(['queue.default' => 'sync']);
        
        $this->info('--- STARTING SCENARIO 6 TEST (Command) ---');

        // 1. Setup Student & Payment Method
        $student = \App\Models\StudentProfile::first();
        if (!$student) { $this->error('No student found.'); return 1; }
        
        $paymentMethod = \App\Models\PaymentMethod::first() ?? \App\Models\PaymentSystem\PaymentMethod::first();
        if (!$paymentMethod) { $this->error('No payment method found.'); return 1; }

        $this->info("Using Student: {$student->id}");
        
        // 2. Cleanup
        $this->info("Cleaning up old data...");
        $invoices = \App\Models\PaymentSystem\Invoice::where('student_id', $student->id)->pluck('id');
        if ($invoices->count() > 0) {
            \App\Models\PaymentSystem\InvoiceFee::whereIn('invoice_id', $invoices)->delete();
            \App\Models\PaymentSystem\Payment::whereIn('invoice_id', $invoices)->delete();
            \App\Models\PaymentSystem\Invoice::whereIn('id', $invoices)->delete();
        }
        $this->info("Cleanup done.");

        // 3. Manually Create Invoice
        $this->info("Creating invoice manually...");
        $invoice = \App\Models\PaymentSystem\Invoice::create([
            'invoice_number' => 'INV-TEST-' . time(),
            'student_id' => $student->id,
            'academic_year' => '2025-2026',
            'total_amount' => 47000,
            'paid_amount' => 0,
            'remaining_amount' => 47000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
            'invoice_type' => 'monthly',
        ]);

        // Fetch Fee Structures to get valid IDs
        $schoolFeeStruct = \App\Models\PaymentSystem\FeeStructure::where('name', 'School Fee')->first();
        $transportFeeStruct = \App\Models\PaymentSystem\FeeStructure::where('name', 'Transportation Fee')->first();
        $bookFeeStruct = \App\Models\PaymentSystem\FeeStructure::where('name', 'Book Fee')->first();

        // Fallback or skip if not found (though seeder should have them)
        if (!$schoolFeeStruct) { $this->error('School Fee Structure not found'); return 1; }

        $schoolFee = \App\Models\PaymentSystem\InvoiceFee::create([
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

        if ($transportFeeStruct) {
            \App\Models\PaymentSystem\InvoiceFee::create([
                'invoice_id' => $invoice->id,
                'fee_id' => $transportFeeStruct->id,
                'fee_name' => 'Transportation Fee',
                'amount' => 25000,
                'paid_amount' => 0,
                'remaining_amount' => 25000,
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
            ]);
        }

        if ($bookFeeStruct) {
            \App\Models\PaymentSystem\InvoiceFee::create([
                'invoice_id' => $invoice->id,
                'fee_id' => $bookFeeStruct->id,
                'fee_name' => 'Book Fee',
                'amount' => 12000,
                'paid_amount' => 0,
                'remaining_amount' => 12000,
                'due_date' => now()->addDays(30),
                'status' => 'unpaid',
            ]);
        }

        $this->info("Invoice Created: {$invoice->invoice_number}");

        // 5. Simulate Partial Payment
        // Requirement: Minimum total payment 10,000 MMK
        $payAmountSchool = 5000;
        $payAmountTrans = 5000;
        $totalPay = $payAmountSchool + $payAmountTrans;
        
        $this->info("Submitting Partial Payment of {$totalPay} (School: {$payAmountSchool}, Trans: {$payAmountTrans})...");

        $data = [
            'invoice_id' => $invoice->id,
            'payment_amount' => $totalPay,
            'payment_type' => 'partial',
            'payment_method_id' => $paymentMethod->id,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $schoolFee->id,
                    'paid_amount' => $payAmountSchool
                ],
                [
                    'invoice_fee_id' => \App\Models\PaymentSystem\InvoiceFee::where('invoice_id', $invoice->id)->where('fee_name', 'Transportation Fee')->first()->id,
                    'paid_amount' => $payAmountTrans
                ]
            ]
        ];

        try {
            $paymentService = app(\App\Services\PaymentSystem\PaymentService::class);
            $payment = $paymentService->submitPayment($data);
            $this->info("Payment Submitted: {$payment->payment_number}");
            
            // 6. Verify Payment
            $this->info("Verifying Payment...");
            
            // Mock NotificationService
            $mockNotification = \Mockery::mock(\App\Services\PaymentSystem\NotificationService::class);
            $mockNotification->shouldReceive('notifyGuardianOfVerification')->andReturn(true);
            
            // Instantiate Verification Service manually
            $verificationService = new \App\Services\PaymentSystem\PaymentVerificationService($mockNotification);
            
            $admin = \App\Models\User::first() ?? \App\Models\User::factory()->create();
            
            $verificationService->verifyPayment($payment, $admin);
            $this->info("Payment Verified.");
            
            // 7. Check Remaining Balance Invoice
            $invoice->refresh();
            $this->info("Original Invoice Status: {$invoice->status}");
            
            // Allow a small delay for job dispatch if async (though sync is likely)
            // sleep(1);

            $remainingInvoice = \App\Models\PaymentSystem\Invoice::where('parent_invoice_id', $invoice->id)->first();
            
            if ($remainingInvoice) {
                $this->info("SUCCESS: Remaining Balance Invoice Found!");
                $this->info("New Invoice Number: {$remainingInvoice->invoice_number}");
                $this->info("New Invoice Amount: {$remainingInvoice->total_amount}");
                $remainingInvoice->load('fees');
                foreach($remainingInvoice->fees as $remFee) {
                    $this->line(" - {$remFee->fee_name}: " . number_format($remFee->amount));
                }
            } else {
                $this->error("FAILURE: Remaining Balance Invoice NOT created.");
            }

        } catch (\Exception $e) {
            $this->error("EXCEPTION: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        $this->info("--- TEST COMPLETE ---");
        return 0;
    }
}
