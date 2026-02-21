<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that payment options can be retrieved successfully.
     */
    public function test_can_retrieve_payment_options(): void
    {
        // Create payment options
        PaymentOption::factory()->create([
            'months' => 1,
            'discount_percent' => 0,
            'label' => '1 Month',
            'label_mm' => '၁ လ',
            'badge' => null,
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::factory()->create([
            'months' => 3,
            'discount_percent' => 5,
            'label' => '3 Months',
            'label_mm' => '၃ လ',
            'badge' => '5% OFF',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PaymentOption::factory()->create([
            'months' => 6,
            'discount_percent' => 10,
            'label' => '6 Months',
            'label_mm' => '၆ လ',
            'badge' => '10% OFF',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PaymentOption::factory()->create([
            'months' => 12,
            'discount_percent' => 15,
            'label' => '12 Months',
            'label_mm' => '၁၂ လ',
            'badge' => '15% OFF',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_options' => [
                        '*' => [
                            'id',
                            'months',
                            'discount_percent',
                            'label',
                            'label_mm',
                            'badge',
                            'is_default',
                            'sort_order',
                        ],
                    ],
                    'default_months',
                    'max_months',
                    'note',
                    'note_mm',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'default_months' => 1,
                    'max_months' => 12,
                ],
            ]);

        // Assert that we have 4 payment options
        $this->assertCount(4, $response->json('data.payment_options'));
    }

    /**
     * Test that only active payment options are returned.
     */
    public function test_only_active_payment_options_are_returned(): void
    {
        // Create active payment options
        PaymentOption::factory()->create([
            'months' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::factory()->create([
            'months' => 3,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Create inactive payment option
        PaymentOption::factory()->create([
            'months' => 6,
            'is_active' => false,
            'sort_order' => 3,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200);

        // Assert that only 2 active options are returned
        $this->assertCount(2, $response->json('data.payment_options'));
    }

    /**
     * Test that payment options are ordered by sort_order.
     */
    public function test_payment_options_are_ordered_by_sort_order(): void
    {
        // Create payment options in random order
        PaymentOption::factory()->create([
            'months' => 12,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        PaymentOption::factory()->create([
            'months' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::factory()->create([
            'months' => 6,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PaymentOption::factory()->create([
            'months' => 3,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200);

        // Get the payment options from response
        $options = $response->json('data.payment_options');

        // Assert that options are ordered by sort_order
        $this->assertEquals(1, $options[0]['sort_order']);
        $this->assertEquals(2, $options[1]['sort_order']);
        $this->assertEquals(3, $options[2]['sort_order']);
        $this->assertEquals(4, $options[3]['sort_order']);

        // Assert that months are in correct order
        $this->assertEquals(1, $options[0]['months']);
        $this->assertEquals(3, $options[1]['months']);
        $this->assertEquals(6, $options[2]['months']);
        $this->assertEquals(12, $options[3]['months']);
    }

    /**
     * Test that default_months is correctly identified.
     */
    public function test_default_months_is_correctly_identified(): void
    {
        // Create payment options with 3 months as default
        PaymentOption::factory()->create([
            'months' => 1,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::factory()->create([
            'months' => 3,
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PaymentOption::factory()->create([
            'months' => 6,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'default_months' => 3,
                ],
            ]);
    }

    /**
     * Test that max_months is correctly calculated.
     */
    public function test_max_months_is_correctly_calculated(): void
    {
        // Create payment options with different months
        PaymentOption::factory()->create([
            'months' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::factory()->create([
            'months' => 3,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PaymentOption::factory()->create([
            'months' => 6,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'max_months' => 6,
                ],
            ]);
    }

    /**
     * Test that bilingual notes are included in response.
     */
    public function test_bilingual_notes_are_included(): void
    {
        // Create a payment option
        PaymentOption::factory()->create([
            'months' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Make request
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('data.note', 'Payment periods only apply to monthly fees')
            ->assertJsonPath('data.note_mm', 'ငွေပေးချေမှုကာလများသည် လစဉ်ကြေးများအတွက်သာ သက်ဆိုင်ပါသည်');
    }

    /**
     * Test that endpoint returns empty array when no payment options exist.
     */
    public function test_returns_empty_array_when_no_payment_options_exist(): void
    {
        // Make request without creating any payment options
        $response = $this->getJson('/api/v1/payment-system/payment-options');

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'payment_options' => [],
                    'default_months' => 1,
                    'max_months' => 12,
                ],
            ]);
    }
}
