<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving invoices for a student.
     */
    public function test_can_retrieve_invoices_for_student(): void
    {
        // Create a user with authentication
        $user = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create([
            'status' => 'active',
        ]);

        // Create fee structures
        $fee1 = FeeStructure::factory()->create([
            'name' => 'Tuition Fee',
            'name_mm' => 'စာသင်ကြေး',
            'amount' => 100000,
            'frequency' => 'monthly',
        ]);

        $fee2 = FeeStructure::factory()->create([
            'name' => 'Transportation Fee',
            'name_mm' => 'သယ်ယူပို့ဆောင်ရေးကြေး',
            'amount' => 50000,
            'frequency' => 'monthly',
        ]);

        // Create invoices
        $invoice1 = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 0,
            'remaining_amount' => 150000,
            'status' => 'pending',
            'invoice_type' => 'monthly',
        ]);

        $invoice2 = Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 150000,
            'paid_amount' => 75000,
            'remaining_amount' => 75000,
            'status' => 'partial',
            'invoice_type' => 'monthly',
        ]);

        // Create invoice fees for invoice 1
        InvoiceFee::factory()->create([
            'invoice_id' => $invoice1->id,
            'fee_id' => $fee1->id,
            'fee_name' => $fee1->name,
            'fee_name_mm' => $fee1->name_mm,
            'amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'unpaid',
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $invoice1->id,
            'fee_id' => $fee2->id,
            'fee_name' => $fee2->name,
            'fee_name_mm' => $fee2->name_mm,
            'amount' => 50000,
            'paid_amount' => 0,
            'remaining_amount' => 50000,
            'status' => 'unpaid',
        ]);

        // Create invoice fees for invoice 2
        InvoiceFee::factory()->create([
            'invoice_id' => $invoice2->id,
            'fee_id' => $fee1->id,
            'fee_name' => $fee1->name,
            'fee_name_mm' => $fee1->name_mm,
            'amount' => 100000,
            'paid_amount' => 50000,
            'remaining_amount' => 50000,
            'status' => 'partial',
        ]);

        InvoiceFee::factory()->create([
            'invoice_id' => $invoice2->id,
            'fee_id' => $fee2->id,
            'fee_name' => $fee2->name,
            'fee_name_mm' => $fee2->name_mm,
            'amount' => 50000,
            'paid_amount' => 25000,
            'remaining_amount' => 25000,
            'status' => 'partial',
        ]);

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/payment-system/students/{$student->id}/invoices");

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'invoices' => [
                        '*' => [
                            'id',
                            'invoice_number',
                            'total_amount',
                            'paid_amount',
                            'remaining_amount',
                            'due_date',
                            'status',
                            'invoice_type',
                            'academic_year',
                            'created_at',
                            'fees' => [
                                '*' => [
                                    'id',
                                    'fee_name',
                                    'fee_name_mm',
                                    'amount',
                                    'paid_amount',
                                    'remaining_amount',
                                    'supports_payment_period',
                                    'due_date',
                                    'status',
                                ],
                            ],
                        ],
                    ],
                    'counts' => [
                        'total',
                        'pending',
                        'overdue',
                    ],
                ],
            ]);

        // Assert data
        $data = $response->json('data');
        $this->assertCount(2, $data['invoices']);
        $this->assertEquals(2, $data['counts']['total']);
        $this->assertEquals(1, $data['counts']['pending']);
    }

    /**
     * Test retrieving invoices with status filter.
     */
    public function test_can_filter_invoices_by_status(): void
    {
        // Create a user with authentication
        $user = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create([
            'status' => 'active',
        ]);

        // Create invoices with different statuses
        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'partial',
        ]);

        Invoice::factory()->create([
            'student_id' => $student->id,
            'status' => 'paid',
        ]);

        // Make request with status filter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/payment-system/students/{$student->id}/invoices?status=pending");

        // Assert response
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data['invoices']);
        $this->assertEquals('pending', $data['invoices'][0]['status']);
    }

    /**
     * Test retrieving invoices with academic year filter.
     */
    public function test_can_filter_invoices_by_academic_year(): void
    {
        // Create a user with authentication
        $user = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create([
            'status' => 'active',
        ]);

        // Create invoices with different academic years
        Invoice::factory()->create([
            'student_id' => $student->id,
            'academic_year' => '2023',
        ]);

        Invoice::factory()->create([
            'student_id' => $student->id,
            'academic_year' => '2024',
        ]);

        // Make request with academic year filter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/payment-system/students/{$student->id}/invoices?academic_year=2024");

        // Assert response
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data['invoices']);
        $this->assertEquals('2024', $data['invoices'][0]['academic_year']);
    }

    /**
     * Test validation error for invalid status.
     */
    public function test_validation_error_for_invalid_status(): void
    {
        // Create a user with authentication
        $user = User::factory()->create();

        // Create a student
        $student = StudentProfile::factory()->create();

        // Make request with invalid status
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/payment-system/students/{$student->id}/invoices?status=invalid");

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test authentication is required.
     */
    public function test_authentication_required(): void
    {
        // Create a student
        $student = StudentProfile::factory()->create();

        // Make request without authentication
        $response = $this->getJson("/api/v1/payment-system/students/{$student->id}/invoices");

        // Assert unauthorized
        $response->assertStatus(401);
    }
}
