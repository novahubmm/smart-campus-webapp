<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentMethod;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_submits_a_full_payment_successfully()
    {
        // Arrange: Create test data
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        $fee = FeeStructure::factory()->create([
            'amount' => 100000,
            'frequency' => 'monthly',
            'supports_payment_period' => true,
        ]);

        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'pending',
        ]);

        $invoiceFee = InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'fee_id' => $fee->id,
            'amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'unpaid',
        ]);

        $receiptImage = UploadedFile::fake()->image('receipt.jpg', 800, 600)->size(1024);

        // Act: Submit payment
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => (int) $invoice->id,
            'payment_method_id' => (int) $paymentMethod->id,
            'payment_amount' => 100000,
            'payment_type' => 'full',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $receiptImage,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => (int) $invoiceFee->id,
                    'paid_amount' => 100000,
                ],
            ],
        ]);

        // Assert: Check response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment_id',
                    'payment_number',
                    'status',
                    'submitted_at',
                    'verification_eta',
                    'verification_eta_mm',
                    'receipt_url',
                    'payment_details' => [
                        'payment_amount',
                        'payment_type',
                        'payment_months',
                        'payment_date',
                        'payment_method',
                        'fees',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'pending_verification',
                    'payment_details' => [
                        'payment_amount' => 100000,
                        'payment_type' => 'full',
                        'payment_months' => 1,
                    ],
                ],
            ]);

        // Assert: Check database
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'invoice_id' => $invoice->id,
            'payment_amount' => 100000,
            'payment_type' => 'full',
            'status' => 'pending_verification',
        ]);

        // Assert: Check invoice updated
        $invoice->refresh();
        $this->assertEquals(100000, $invoice->paid_amount);
        $this->assertEquals(0, $invoice->remaining_amount);
        $this->assertEquals('paid', $invoice->status);
    }

    /** @test */
    public function it_submits_a_partial_payment_successfully()
    {
        // Arrange: Create test data
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        $fee = FeeStructure::factory()->create([
            'amount' => 100000,
            'frequency' => 'monthly',
            'supports_payment_period' => false,
            'due_date' => now()->addDays(30),
        ]);

        $invoice = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $invoiceFee = InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'fee_id' => $fee->id,
            'amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        $receiptImage = UploadedFile::fake()->image('receipt.png', 800, 600)->size(2048);

        // Act: Submit partial payment
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 50000,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $receiptImage,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => 50000,
                ],
            ],
        ]);

        // Assert: Check response
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'pending_verification',
                    'payment_details' => [
                        'payment_amount' => 50000,
                        'payment_type' => 'partial',
                    ],
                ],
            ]);

        // Assert: Check database
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'payment_amount' => 50000,
            'payment_type' => 'partial',
        ]);

        // Assert: Check invoice updated
        $invoice->refresh();
        $this->assertEquals(50000, $invoice->paid_amount);
        $this->assertEquals(50000, $invoice->remaining_amount);
        $this->assertEquals('partial', $invoice->status);
    }

    /** @test */
    public function it_rejects_payment_with_invalid_receipt_format()
    {
        // Arrange
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->id]);
        $invoiceFee = InvoiceFee::factory()->create(['invoice_id' => $invoice->id]);

        $invalidReceipt = UploadedFile::fake()->create('receipt.pdf', 1024);

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 50000,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $invalidReceipt,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => 50000,
                ],
            ],
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receipt_image']);
    }

    /** @test */
    public function it_rejects_payment_with_oversized_receipt()
    {
        // Arrange
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->id]);
        $invoiceFee = InvoiceFee::factory()->create(['invoice_id' => $invoice->id]);

        // Create 6MB image (exceeds 5MB limit)
        $oversizedReceipt = UploadedFile::fake()->image('receipt.jpg')->size(6144);

        // Act
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 50000,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $oversizedReceipt,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => 50000,
                ],
            ],
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['receipt_image']);
    }

    /** @test */
    public function it_rejects_payment_below_minimum_amount()
    {
        // Arrange
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->id]);
        $invoiceFee = InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'remaining_amount' => 100000,
        ]);

        $receiptImage = UploadedFile::fake()->image('receipt.jpg');

        // Act: Try to submit payment below minimum (< 10,000 MMK)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 5000,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $receiptImage,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => 5000,
                ],
            ],
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_amount']);
    }

    /** @test */
    public function it_rejects_payment_with_empty_fee_list()
    {
        // Arrange
        $user = User::factory()->create();
        $student = StudentProfile::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->id]);

        $receiptImage = UploadedFile::fake()->image('receipt.jpg');

        // Act: Try to submit payment with empty fee list
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/payment-system/students/{$student->id}/payments/submit", [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 50000,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now()->format('Y-m-d'),
            'receipt_image' => $receiptImage,
            'fee_payment_details' => [],
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['fee_payment_details']);
    }
}
