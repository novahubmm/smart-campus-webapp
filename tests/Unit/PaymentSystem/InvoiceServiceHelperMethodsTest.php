<?php

namespace Tests\Unit\PaymentSystem;

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceHelperMethodsTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceService();
    }

    /** @test */
    public function it_calculates_status_as_paid_when_remaining_amount_is_zero()
    {
        $invoice = Invoice::factory()->make([
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'due_date' => now()->addDays(10),
        ]);

        $status = $this->service->calculateInvoiceStatus($invoice);

        $this->assertEquals('paid', $status);
    }

    /** @test */
    public function it_calculates_status_as_partial_when_partially_paid()
    {
        $invoice = Invoice::factory()->make([
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
            'due_date' => now()->addDays(10),
        ]);

        $status = $this->service->calculateInvoiceStatus($invoice);

        $this->assertEquals('partial', $status);
    }

    /** @test */
    public function it_calculates_status_as_overdue_when_unpaid_and_past_due_date()
    {
        $invoice = Invoice::factory()->make([
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'due_date' => now()->subDays(5),
        ]);

        $status = $this->service->calculateInvoiceStatus($invoice);

        $this->assertEquals('overdue', $status);
    }

    /** @test */
    public function it_calculates_status_as_pending_when_unpaid_and_not_yet_due()
    {
        $invoice = Invoice::factory()->make([
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'due_date' => now()->addDays(10),
        ]);

        $status = $this->service->calculateInvoiceStatus($invoice);

        $this->assertEquals('pending', $status);
    }

    /** @test */
    public function it_updates_invoice_amounts_based_on_invoice_fees()
    {
        $student = StudentProfile::factory()->create();
        
        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 0,
            'remaining_amount' => 150000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
        ]);

        // Create invoice fees
        InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'paid_amount' => 60000,
            'remaining_amount' => 40000,
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 50000,
            'paid_amount' => 30000,
            'remaining_amount' => 20000,
        ]);

        // Update invoice amounts
        $this->service->updateInvoiceAmounts($invoice);

        // Refresh invoice from database
        $invoice->refresh();

        // Assert amounts are correctly calculated
        $this->assertEquals(90000, $invoice->paid_amount);
        $this->assertEquals(60000, $invoice->remaining_amount);
        $this->assertEquals('partial', $invoice->status);
    }

    /** @test */
    public function it_updates_invoice_status_to_paid_when_all_fees_are_paid()
    {
        $student = StudentProfile::factory()->create();
        
        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 0,
            'remaining_amount' => 150000,
            'due_date' => now()->addDays(30),
            'status' => 'pending',
        ]);

        // Create invoice fees that are fully paid
        InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 50000,
            'paid_amount' => 50000,
            'remaining_amount' => 0,
        ]);

        // Update invoice amounts
        $this->service->updateInvoiceAmounts($invoice);

        // Refresh invoice from database
        $invoice->refresh();

        // Assert invoice is marked as paid
        $this->assertEquals(150000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->remaining_amount);
        $this->assertEquals('paid', $invoice->status);
    }

    /** @test */
    public function it_generates_unique_invoice_number_with_correct_format()
    {
        $invoiceNumber = $this->service->generateInvoiceNumber();

        // Assert format: INV-{YYYYMMDD}-{SEQUENCE}
        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{4}$/', $invoiceNumber);

        // Assert date part matches today
        $expectedDate = now()->format('Ymd');
        $this->assertStringContainsString($expectedDate, $invoiceNumber);
    }

    /** @test */
    public function it_generates_sequential_invoice_numbers()
    {
        $student = StudentProfile::factory()->create();

        // Create first invoice
        $invoice1 = Invoice::factory()->create([
            'student_id' => $student->id,
            'invoice_number' => $this->service->generateInvoiceNumber(),
        ]);

        // Create second invoice
        $invoice2 = Invoice::factory()->create([
            'student_id' => $student->id,
            'invoice_number' => $this->service->generateInvoiceNumber(),
        ]);

        // Extract sequence numbers
        preg_match('/INV-\d{8}-(\d{4})/', $invoice1->invoice_number, $matches1);
        preg_match('/INV-\d{8}-(\d{4})/', $invoice2->invoice_number, $matches2);

        $sequence1 = intval($matches1[1]);
        $sequence2 = intval($matches2[1]);

        // Assert second sequence is greater than first
        $this->assertGreaterThan($sequence1, $sequence2);
    }

    /** @test */
    public function it_updates_invoice_status_to_overdue_when_unpaid_and_past_due()
    {
        $student = StudentProfile::factory()->create();
        
        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        // Create unpaid invoice fee
        InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
        ]);

        // Update invoice amounts
        $this->service->updateInvoiceAmounts($invoice);

        // Refresh invoice from database
        $invoice->refresh();

        // Assert invoice is marked as overdue
        $this->assertEquals('overdue', $invoice->status);
    }
}
