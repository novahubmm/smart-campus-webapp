<?php

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Property 5: Monthly Invoice Fee Type Exclusivity
 * 
 * For any monthly invoice, all fees in that invoice should have frequency "monthly",
 * and no fees should have frequency "one_time".
 * 
 * **Validates: Requirements 3.3**
 */
describe('Property 5: Monthly Invoice Fee Type Exclusivity', function () {
    
    test('monthly invoices contain only monthly fees', function () {
        // Create multiple monthly invoices with various fee configurations
        for ($i = 0; $i < 20; $i++) {
            // Create a monthly invoice
            $invoice = Invoice::factory()->monthly()->create();
            
            // Create random number of fees (1-10) for this invoice
            $feeCount = fake()->numberBetween(1, 10);
            
            for ($j = 0; $j < $feeCount; $j++) {
                // Create monthly fee structures
                $feeStructure = FeeStructure::factory()->monthly()->create();
                
                // Create invoice fee linked to the monthly fee structure
                InvoiceFee::factory()->create([
                    'invoice_id' => $invoice->id,
                    'fee_id' => $feeStructure->id,
                ]);
            }
            
            // Reload the invoice with fees
            $invoice->refresh();
            
            // Property: All fees in a monthly invoice must have frequency "monthly"
            foreach ($invoice->fees as $invoiceFee) {
                $feeStructure = $invoiceFee->feeStructure;
                expect($feeStructure->frequency)
                    ->toBe('monthly', 
                        "Invoice {$invoice->invoice_number} (type: {$invoice->invoice_type}) contains fee {$feeStructure->name} with frequency {$feeStructure->frequency}, expected 'monthly'"
                    );
            }
        }
    });
    
    test('monthly invoices never contain one-time fees', function () {
        // Create multiple monthly invoices
        for ($i = 0; $i < 20; $i++) {
            // Create a monthly invoice
            $invoice = Invoice::factory()->monthly()->create();
            
            // Create random number of monthly fees (1-10)
            $monthlyFeeCount = fake()->numberBetween(1, 10);
            
            for ($j = 0; $j < $monthlyFeeCount; $j++) {
                $feeStructure = FeeStructure::factory()->monthly()->create();
                InvoiceFee::factory()->create([
                    'invoice_id' => $invoice->id,
                    'fee_id' => $feeStructure->id,
                ]);
            }
            
            // Reload the invoice with fees
            $invoice->refresh();
            
            // Property: No fees in a monthly invoice should have frequency "one_time"
            $oneTimeFees = $invoice->fees->filter(function ($invoiceFee) {
                return $invoiceFee->feeStructure->frequency === 'one_time';
            });
            
            expect($oneTimeFees->count())
                ->toBe(0, 
                    "Invoice {$invoice->invoice_number} (type: monthly) contains {$oneTimeFees->count()} one-time fees, expected 0"
                );
        }
    });
    
    test('one-time invoices contain only one-time fees', function () {
        // Create multiple one-time invoices
        for ($i = 0; $i < 20; $i++) {
            // Create a one-time invoice
            $invoice = Invoice::factory()->oneTime()->create();
            
            // One-time invoices should contain only one fee (as per requirements 2.2, 2.3)
            $feeStructure = FeeStructure::factory()->oneTime()->create();
            InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'fee_id' => $feeStructure->id,
            ]);
            
            // Reload the invoice with fees
            $invoice->refresh();
            
            // Property: All fees in a one-time invoice must have frequency "one_time"
            foreach ($invoice->fees as $invoiceFee) {
                $feeStructure = $invoiceFee->feeStructure;
                expect($feeStructure->frequency)
                    ->toBe('one_time', 
                        "Invoice {$invoice->invoice_number} (type: {$invoice->invoice_type}) contains fee {$feeStructure->name} with frequency {$feeStructure->frequency}, expected 'one_time'"
                    );
            }
        }
    });
    
    test('invoice type matches fee frequency', function () {
        // Test the invariant: invoice_type should match the frequency of its fees
        for ($i = 0; $i < 30; $i++) {
            // Randomly choose invoice type
            $invoiceType = fake()->randomElement(['monthly', 'one_time']);
            
            $invoice = Invoice::factory()->create([
                'invoice_type' => $invoiceType,
            ]);
            
            // Create fees matching the invoice type
            $feeCount = $invoiceType === 'monthly' ? fake()->numberBetween(1, 10) : 1;
            
            for ($j = 0; $j < $feeCount; $j++) {
                $feeStructure = FeeStructure::factory()->create([
                    'frequency' => $invoiceType === 'monthly' ? 'monthly' : 'one_time',
                    'target_month' => $invoiceType === 'one_time' ? fake()->numberBetween(1, 12) : null,
                ]);
                
                InvoiceFee::factory()->create([
                    'invoice_id' => $invoice->id,
                    'fee_id' => $feeStructure->id,
                ]);
            }
            
            // Reload the invoice with fees
            $invoice->refresh();
            
            // Property: invoice_type should match fee frequency
            foreach ($invoice->fees as $invoiceFee) {
                $feeStructure = $invoiceFee->feeStructure;
                
                if ($invoice->invoice_type === 'monthly') {
                    expect($feeStructure->frequency)
                        ->toBe('monthly', 
                            "Monthly invoice {$invoice->invoice_number} contains fee with frequency {$feeStructure->frequency}"
                        );
                } elseif ($invoice->invoice_type === 'one_time') {
                    expect($feeStructure->frequency)
                        ->toBe('one_time', 
                            "One-time invoice {$invoice->invoice_number} contains fee with frequency {$feeStructure->frequency}"
                        );
                }
            }
        }
    });
    
    test('no mixed frequency fees in any invoice', function () {
        // Test that no invoice contains both monthly and one-time fees
        for ($i = 0; $i < 20; $i++) {
            // Create an invoice (random type)
            $invoiceType = fake()->randomElement(['monthly', 'one_time']);
            $invoice = Invoice::factory()->create([
                'invoice_type' => $invoiceType,
            ]);
            
            // Create fees matching the invoice type
            $feeCount = $invoiceType === 'monthly' ? fake()->numberBetween(2, 8) : 1;
            
            for ($j = 0; $j < $feeCount; $j++) {
                $feeStructure = FeeStructure::factory()->create([
                    'frequency' => $invoiceType === 'monthly' ? 'monthly' : 'one_time',
                    'target_month' => $invoiceType === 'one_time' ? fake()->numberBetween(1, 12) : null,
                ]);
                
                InvoiceFee::factory()->create([
                    'invoice_id' => $invoice->id,
                    'fee_id' => $feeStructure->id,
                ]);
            }
            
            // Reload the invoice with fees
            $invoice->refresh();
            
            // Property: All fees in an invoice should have the same frequency
            $frequencies = $invoice->fees->map(function ($invoiceFee) {
                return $invoiceFee->feeStructure->frequency;
            })->unique();
            
            expect($frequencies->count())
                ->toBe(1, 
                    "Invoice {$invoice->invoice_number} (type: {$invoice->invoice_type}) contains mixed frequencies: " . $frequencies->implode(', ')
                );
        }
    });
});
