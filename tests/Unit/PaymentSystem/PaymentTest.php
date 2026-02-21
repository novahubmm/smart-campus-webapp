<?php

namespace Tests\Unit\PaymentSystem;

use App\Models\PaymentMethod;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\PaymentFeeDetail;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'payment_number',
            'student_id',
            'invoice_id',
            'payment_method_id',
            'payment_amount',
            'payment_type',
            'payment_months',
            'payment_date',
            'receipt_image_url',
            'status',
            'verified_at',
            'verified_by',
            'rejection_reason',
            'notes',
        ];

        $payment = new Payment();
        
        $this->assertEquals($fillable, $payment->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $payment = Payment::factory()->create([
            'payment_amount' => 100000.50,
            'payment_months' => 3,
            'payment_date' => '2024-03-15',
            'verified_at' => '2024-03-16 10:30:00',
        ]);

        $this->assertIsString($payment->payment_amount);
        $this->assertEquals('100000.50', $payment->payment_amount);
        $this->assertIsInt($payment->payment_months);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $payment->payment_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $payment->verified_at);
    }

    /** @test */
    public function it_belongs_to_an_invoice()
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertInstanceOf(Invoice::class, $payment->invoice);
        $this->assertEquals($invoice->id, $payment->invoice->id);
    }

    /** @test */
    public function it_belongs_to_a_student()
    {
        $student = StudentProfile::factory()->create();
        $payment = Payment::factory()->create(['student_id' => $student->id]);

        $this->assertInstanceOf(StudentProfile::class, $payment->student);
        $this->assertEquals($student->id, $payment->student->id);
    }

    /** @test */
    public function it_belongs_to_a_payment_method()
    {
        $paymentMethod = PaymentMethod::factory()->create();
        $payment = Payment::factory()->create(['payment_method_id' => $paymentMethod->id]);

        $this->assertInstanceOf(PaymentMethod::class, $payment->paymentMethod);
        $this->assertEquals($paymentMethod->id, $payment->paymentMethod->id);
    }

    /** @test */
    public function it_has_many_fee_details()
    {
        $payment = Payment::factory()->create();
        $feeDetail1 = PaymentFeeDetail::factory()->create(['payment_id' => $payment->id]);
        $feeDetail2 = PaymentFeeDetail::factory()->create(['payment_id' => $payment->id]);

        $this->assertCount(2, $payment->feeDetails);
        $this->assertTrue($payment->feeDetails->contains($feeDetail1));
        $this->assertTrue($payment->feeDetails->contains($feeDetail2));
    }

    /** @test */
    public function is_pending_returns_true_when_status_is_pending_verification()
    {
        $payment = Payment::factory()->create(['status' => 'pending_verification']);

        $this->assertTrue($payment->isPending());
    }

    /** @test */
    public function is_pending_returns_false_when_status_is_not_pending_verification()
    {
        $verifiedPayment = Payment::factory()->create(['status' => 'verified']);
        $rejectedPayment = Payment::factory()->create(['status' => 'rejected']);

        $this->assertFalse($verifiedPayment->isPending());
        $this->assertFalse($rejectedPayment->isPending());
    }

    /** @test */
    public function is_verified_returns_true_when_status_is_verified()
    {
        $payment = Payment::factory()->create(['status' => 'verified']);

        $this->assertTrue($payment->isVerified());
    }

    /** @test */
    public function is_verified_returns_false_when_status_is_not_verified()
    {
        $pendingPayment = Payment::factory()->create(['status' => 'pending_verification']);
        $rejectedPayment = Payment::factory()->create(['status' => 'rejected']);

        $this->assertFalse($pendingPayment->isVerified());
        $this->assertFalse($rejectedPayment->isVerified());
    }

    /** @test */
    public function is_rejected_returns_true_when_status_is_rejected()
    {
        $payment = Payment::factory()->create(['status' => 'rejected']);

        $this->assertTrue($payment->isRejected());
    }

    /** @test */
    public function is_rejected_returns_false_when_status_is_not_rejected()
    {
        $pendingPayment = Payment::factory()->create(['status' => 'pending_verification']);
        $verifiedPayment = Payment::factory()->create(['status' => 'verified']);

        $this->assertFalse($pendingPayment->isRejected());
        $this->assertFalse($verifiedPayment->isRejected());
    }

    /** @test */
    public function it_uses_payments_payment_system_table()
    {
        $payment = new Payment();
        
        $this->assertEquals('payments_payment_system', $payment->getTable());
    }
}
