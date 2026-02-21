<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentMethod;
use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\PaymentFeeDetail;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can verify a pending payment.
     */
    public function test_admin_can_verify_pending_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create([
            'status' => 'active',
        ]);

        // Create a fee structure
        $fee = FeeStructure::factory()->create([
            'name' => 'Tuition Fee',
            'amount' => 100000,
            'frequency' => 'monthly',
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Create invoice fee
        $invoiceFee = InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'fee_id' => $fee->id,
            'amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Create a payment method
        $paymentMethod = PaymentMethod::factory()->create();

        // Create a pending payment
        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 100000,
            'status' => 'pending_verification',
        ]);

        // Create payment fee detail
        PaymentFeeDetail::factory()->create([
            'payment_id' => $payment->id,
            'invoice_fee_id' => $invoiceFee->id,
            'paid_amount' => 100000,
        ]);

        // Make request to verify payment
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/verify");

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment' => [
                        'id',
                        'payment_number',
                        'status',
                        'verified_at',
                        'verified_by',
                    ],
                ],
            ]);

        // Assert payment was verified
        $payment->refresh();
        $this->assertEquals('verified', $payment->status);
        $this->assertNotNull($payment->verified_at);
        $this->assertEquals($admin->id, $payment->verified_by);
    }

    /**
     * Test admin can reject a pending payment.
     */
    public function test_admin_can_reject_pending_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create([
            'status' => 'active',
        ]);

        // Create a fee structure
        $fee = FeeStructure::factory()->create([
            'name' => 'Tuition Fee',
            'amount' => 100000,
            'frequency' => 'monthly',
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Create invoice fee
        $invoiceFee = InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'fee_id' => $fee->id,
            'amount' => 100000,
            'paid_amount' => 100000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        // Create a payment method
        $paymentMethod = PaymentMethod::factory()->create();

        // Create a pending payment
        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 100000,
            'status' => 'pending_verification',
        ]);

        // Create payment fee detail
        PaymentFeeDetail::factory()->create([
            'payment_id' => $payment->id,
            'invoice_fee_id' => $invoiceFee->id,
            'paid_amount' => 100000,
        ]);

        // Make request to reject payment
        $rejectionReason = 'Receipt image is not clear enough';
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", [
                'reason' => $rejectionReason,
            ]);

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment' => [
                        'id',
                        'payment_number',
                        'status',
                        'rejection_reason',
                        'verified_at',
                        'verified_by',
                    ],
                ],
            ]);

        // Assert payment was rejected
        $payment->refresh();
        $this->assertEquals('rejected', $payment->status);
        $this->assertEquals($rejectionReason, $payment->rejection_reason);
        $this->assertNotNull($payment->verified_at);
        $this->assertEquals($admin->id, $payment->verified_by);

        // Assert invoice amounts were rolled back
        $invoice->refresh();
        $this->assertEquals(0, $invoice->paid_amount);
        $this->assertEquals(100000, $invoice->remaining_amount);
        $this->assertEquals('pending', $invoice->status);

        $invoiceFee->refresh();
        $this->assertEquals(0, $invoiceFee->paid_amount);
        $this->assertEquals(100000, $invoiceFee->remaining_amount);
        $this->assertEquals('unpaid', $invoiceFee->status);
    }

    /**
     * Test cannot verify an already verified payment.
     */
    public function test_cannot_verify_already_verified_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a verified payment
        $payment = Payment::factory()->create([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        // Make request to verify payment again
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/verify");

        // Assert error response
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Payment is already verified',
            ]);
    }

    /**
     * Test cannot verify a rejected payment.
     */
    public function test_cannot_verify_rejected_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a rejected payment
        $payment = Payment::factory()->create([
            'status' => 'rejected',
            'rejection_reason' => 'Invalid receipt',
        ]);

        // Make request to verify payment
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/verify");

        // Assert error response
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot verify a rejected payment',
            ]);
    }

    /**
     * Test cannot reject an already verified payment.
     */
    public function test_cannot_reject_verified_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a verified payment
        $payment = Payment::factory()->create([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        // Make request to reject payment
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", [
                'reason' => 'Invalid receipt',
            ]);

        // Assert error response
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot reject a verified payment',
            ]);
    }

    /**
     * Test cannot reject an already rejected payment.
     */
    public function test_cannot_reject_already_rejected_payment(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a rejected payment
        $payment = Payment::factory()->create([
            'status' => 'rejected',
            'rejection_reason' => 'Invalid receipt',
        ]);

        // Make request to reject payment again
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", [
                'reason' => 'Another reason',
            ]);

        // Assert error response
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Payment is already rejected',
            ]);
    }

    /**
     * Test rejection requires a reason.
     */
    public function test_rejection_requires_reason(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a pending payment
        $payment = Payment::factory()->create([
            'status' => 'pending_verification',
        ]);

        // Make request without reason
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", []);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'reason',
                ],
            ]);
    }

    /**
     * Test rejection reason must be at least 10 characters.
     */
    public function test_rejection_reason_minimum_length(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Create a pending payment
        $payment = Payment::factory()->create([
            'status' => 'pending_verification',
        ]);

        // Make request with short reason
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", [
                'reason' => 'Too short',
            ]);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'reason',
                ],
            ]);
    }

    /**
     * Test payment not found returns 404.
     */
    public function test_payment_not_found_returns_404(): void
    {
        // Create an admin user
        $admin = User::factory()->create();

        // Make request with non-existent payment ID
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payment-system/admin/payments/non-existent-id/verify');

        // Assert not found
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Payment not found',
            ]);
    }

    /**
     * Test authentication is required for verification.
     */
    public function test_authentication_required_for_verification(): void
    {
        // Create a pending payment
        $payment = Payment::factory()->create([
            'status' => 'pending_verification',
        ]);

        // Make request without authentication
        $response = $this->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/verify");

        // Assert unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test authentication is required for rejection.
     */
    public function test_authentication_required_for_rejection(): void
    {
        // Create a pending payment
        $payment = Payment::factory()->create([
            'status' => 'pending_verification',
        ]);

        // Make request without authentication
        $response = $this->postJson("/api/v1/payment-system/admin/payments/{$payment->id}/reject", [
            'reason' => 'Invalid receipt',
        ]);

        // Assert unauthorized
        $response->assertStatus(401);
    }
}
