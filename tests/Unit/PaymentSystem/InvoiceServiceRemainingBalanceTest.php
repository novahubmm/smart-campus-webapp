<?php

namespace Tests\Unit\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use App\Services\PaymentSystem\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceRemainingBalanceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceService = new InvoiceService();
    }

    /** @test */
    public function it_creates_remaining_balance_invoice_after_partial_payment()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create fee structures
        $tuitionFee = FeeStructure::factory()->create([
            'name' => 'Tuition',
            'name_mm' => 'စာသင်ကြေး',
            'amount' => 100000,
            'frequency' => 'monthly',
        ]);

        $transportFee = FeeStructure::factory()->create([
            'name' => 'Transportation',
            'name_mm' => 'သယ်ယူပို့ဆောင်ရေးကြေး',
            'amount' => 50000,
            'frequency' => 'monthly',
        ]);

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 60000,
            'remaining_amount' => 90000,
            'status' => 'partial',
            'invoice_type' => 'monthly',
        ]);

        // Create invoice fees with partial payments
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'fee_id' => $tuitionFee->id,
            'fee_name' => 'Tuition',
            'fee_name_mm' => 'စာသင်ကြေး',
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
            'status' => 'partial',
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'fee_id' => $transportFee->id,
            'fee_name' => 'Transportation',
            'fee_name_mm' => 'သယ်ယူပို့ဆောင်ရေးကြေး',
            'amount' => 50000,
            'paid_amount' => 10000,
            'remaining_amount' => 40000,
            'status' => 'partial',
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert invoice was created
        $this->assertInstanceOf(Invoice::class, $remainingBalanceInvoice);
        $this->assertEquals('remaining_balance', $remainingBalanceInvoice->invoice_type);
        $this->assertEquals($originalInvoice->id, $remainingBalanceInvoice->parent_invoice_id);
        $this->assertEquals($student->id, $remainingBalanceInvoice->student_id);
        $this->assertEquals(90000, $remainingBalanceInvoice->total_amount);
        $this->assertEquals(0, $remainingBalanceInvoice->paid_amount);
        $this->assertEquals(90000, $remainingBalanceInvoice->remaining_amount);
        $this->assertEquals('pending', $remainingBalanceInvoice->status);

        // Assert invoice fees were created
        $this->assertCount(2, $remainingBalanceInvoice->fees);

        $tuitionFeeInvoice = $remainingBalanceInvoice->fees->firstWhere('fee_name', 'Tuition');
        $this->assertNotNull($tuitionFeeInvoice);
        $this->assertEquals(50000, $tuitionFeeInvoice->amount);
        $this->assertEquals(0, $tuitionFeeInvoice->paid_amount);
        $this->assertEquals(50000, $tuitionFeeInvoice->remaining_amount);
        $this->assertEquals('unpaid', $tuitionFeeInvoice->status);

        $transportFeeInvoice = $remainingBalanceInvoice->fees->firstWhere('fee_name', 'Transportation');
        $this->assertNotNull($transportFeeInvoice);
        $this->assertEquals(40000, $transportFeeInvoice->amount);
        $this->assertEquals(0, $transportFeeInvoice->paid_amount);
        $this->assertEquals(40000, $transportFeeInvoice->remaining_amount);
        $this->assertEquals('unpaid', $transportFeeInvoice->status);
    }

    /** @test */
    public function it_only_includes_fees_with_remaining_balance()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create fee structures
        $tuitionFee = FeeStructure::factory()->create([
            'name' => 'Tuition',
            'amount' => 100000,
        ]);

        $transportFee = FeeStructure::factory()->create([
            'name' => 'Transportation',
            'amount' => 50000,
        ]);

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 100000,
            'remaining_amount' => 50000,
            'status' => 'partial',
        ]);

        // Create invoice fees - one fully paid, one partial
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'fee_id' => $tuitionFee->id,
            'fee_name' => 'Tuition',
            'amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'fee_id' => $transportFee->id,
            'fee_name' => 'Transportation',
            'amount' => 50000,
            'paid_amount' => 0,
            'remaining_amount' => 50000,
            'status' => 'unpaid',
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert only one fee is included (the one with remaining balance)
        $this->assertCount(1, $remainingBalanceInvoice->fees);
        $this->assertEquals('Transportation', $remainingBalanceInvoice->fees->first()->fee_name);
        $this->assertEquals(50000, $remainingBalanceInvoice->total_amount);
    }

    /** @test */
    public function it_throws_exception_when_no_fees_have_remaining_balance()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Create fully paid invoice fee
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Expect exception when trying to generate remaining balance invoice
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No fees with remaining balance found');

        $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);
    }

    /** @test */
    public function it_sets_correct_due_date_for_remaining_balance_invoice()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Create invoice fee with remaining balance
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert due date is set (default 30 days from now)
        $this->assertNotNull($remainingBalanceInvoice->due_date);
        $expectedDueDate = now()->addDays(30)->format('Y-m-d');
        $this->assertEquals($expectedDueDate, $remainingBalanceInvoice->due_date->format('Y-m-d'));
    }

    /** @test */
    public function it_generates_unique_invoice_number()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Create invoice fee with remaining balance
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert invoice number is generated and follows format
        $this->assertNotNull($remainingBalanceInvoice->invoice_number);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{4}$/', $remainingBalanceInvoice->invoice_number);
    }

    /** @test */
    public function it_preserves_bilingual_fee_names()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create fee structure
        $fee = FeeStructure::factory()->create([
            'name' => 'Library Fee',
            'name_mm' => 'စာကြည့်တိုက်ကြေး',
        ]);

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 10000,
            'paid_amount' => 5000,
            'remaining_amount' => 5000,
        ]);

        // Create invoice fee with bilingual names
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'fee_id' => $fee->id,
            'fee_name' => 'Library Fee',
            'fee_name_mm' => 'စာကြည့်တိုက်ကြေး',
            'amount' => 10000,
            'paid_amount' => 5000,
            'remaining_amount' => 5000,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert bilingual names are preserved
        $fee = $remainingBalanceInvoice->fees->first();
        $this->assertEquals('Library Fee', $fee->fee_name);
        $this->assertEquals('စာကြည့်တိုက်ကြေး', $fee->fee_name_mm);
    }

    /** @test */
    public function it_preserves_supports_payment_period_flag()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Create invoice fee with payment period support
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
            'supports_payment_period' => true,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert supports_payment_period flag is preserved
        $this->assertTrue($remainingBalanceInvoice->fees->first()->supports_payment_period);
    }

    /** @test */
    public function it_links_to_parent_invoice()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Create invoice fee
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert parent_invoice_id is set correctly
        $this->assertEquals($originalInvoice->id, $remainingBalanceInvoice->parent_invoice_id);
    }

    /** @test */
    public function it_preserves_academic_year()
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Create original invoice with specific academic year
        $originalInvoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'academic_year' => '2024-2025',
            'total_amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Create invoice fee
        InvoiceFee::factory()->create([
            'invoice_id' => $originalInvoice->id,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        // Generate remaining balance invoice
        $remainingBalanceInvoice = $this->invoiceService->generateRemainingBalanceInvoice($originalInvoice);

        // Assert academic year is preserved
        $this->assertEquals('2024-2025', $remainingBalanceInvoice->academic_year);
    }
}
