<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can retrieve all active payment methods.
     * 
     * Validates: Requirements 6.1, 6.5
     */
    public function test_can_retrieve_all_active_payment_methods(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create active payment methods
        $activeMethod1 = PaymentMethod::factory()->create([
            'name' => 'KBZ Bank',
            'name_mm' => 'ကေဘီဇက်ဘဏ်',
            'type' => 'bank',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $activeMethod2 = PaymentMethod::factory()->create([
            'name' => 'Wave Money',
            'name_mm' => 'ဝေ့ဗ်မနီ',
            'type' => 'mobile_wallet',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Create inactive payment method (should not be returned)
        PaymentMethod::factory()->create([
            'name' => 'Inactive Bank',
            'is_active' => false,
            'sort_order' => 3,
        ]);

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-system/payment-methods');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_methods' => [
                        '*' => [
                            'id',
                            'name',
                            'name_mm',
                            'type',
                            'account_number',
                            'account_name',
                            'account_name_mm',
                            'logo_url',
                            'instructions',
                            'instructions_mm',
                            'sort_order',
                        ],
                    ],
                    'total',
                ],
                'message',
            ]);

        // Assert only active methods are returned
        $response->assertJsonCount(2, 'data.payment_methods');
        $response->assertJsonPath('data.total', 2);
    }

    /**
     * Test that payment methods are ordered by sort_order.
     * 
     * Validates: Requirement 6.4
     */
    public function test_payment_methods_are_ordered_by_sort_order(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create payment methods with different sort orders
        PaymentMethod::factory()->create([
            'name' => 'Third Method',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PaymentMethod::factory()->create([
            'name' => 'First Method',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentMethod::factory()->create([
            'name' => 'Second Method',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-system/payment-methods');

        // Assert response
        $response->assertStatus(200);

        // Assert order
        $methods = $response->json('data.payment_methods');
        $this->assertEquals('First Method', $methods[0]['name']);
        $this->assertEquals('Second Method', $methods[1]['name']);
        $this->assertEquals('Third Method', $methods[2]['name']);
    }

    /**
     * Test that payment methods can be filtered by type.
     * 
     * Validates: Requirement 6.3
     */
    public function test_can_filter_payment_methods_by_type(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create payment methods of different types
        PaymentMethod::factory()->create([
            'name' => 'KBZ Bank',
            'type' => 'bank',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentMethod::factory()->create([
            'name' => 'Wave Money',
            'type' => 'mobile_wallet',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        PaymentMethod::factory()->create([
            'name' => 'CB Bank',
            'type' => 'bank',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Make request with type filter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-system/payment-methods?type=bank');

        // Assert response
        $response->assertStatus(200);

        // Assert only bank methods are returned
        $methods = $response->json('data.payment_methods');
        $this->assertCount(2, $methods);
        $this->assertEquals('bank', $methods[0]['type']);
        $this->assertEquals('bank', $methods[1]['type']);
    }

    /**
     * Test that all payment method fields are included in response.
     * 
     * Validates: Requirement 6.2
     */
    public function test_payment_method_response_includes_all_fields(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a payment method with all fields
        $method = PaymentMethod::factory()->create([
            'name' => 'KBZ Bank',
            'name_mm' => 'ကေဘီဇက်ဘဏ်',
            'type' => 'bank',
            'account_number' => '1234567890',
            'account_name' => 'School Account',
            'account_name_mm' => 'ကျောင်းအကောင့်',
            'logo_url' => 'https://example.com/logo.png',
            'instructions' => 'Transfer to this account',
            'instructions_mm' => 'ဤအကောင့်သို့ လွှဲပြောင်းပါ',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-system/payment-methods');

        // Assert response includes all fields
        $response->assertStatus(200)
            ->assertJsonPath('data.payment_methods.0.id', $method->id)
            ->assertJsonPath('data.payment_methods.0.name', 'KBZ Bank')
            ->assertJsonPath('data.payment_methods.0.name_mm', 'ကေဘီဇက်ဘဏ်')
            ->assertJsonPath('data.payment_methods.0.type', 'bank')
            ->assertJsonPath('data.payment_methods.0.account_number', '1234567890')
            ->assertJsonPath('data.payment_methods.0.account_name', 'School Account')
            ->assertJsonPath('data.payment_methods.0.account_name_mm', 'ကျောင်းအကောင့်')
            ->assertJsonPath('data.payment_methods.0.logo_url', 'https://example.com/logo.png')
            ->assertJsonPath('data.payment_methods.0.instructions', 'Transfer to this account')
            ->assertJsonPath('data.payment_methods.0.instructions_mm', 'ဤအကောင့်သို့ လွှဲပြောင်းပါ')
            ->assertJsonPath('data.payment_methods.0.sort_order', 1);
    }

    /**
     * Test that unauthenticated users cannot access payment methods.
     */
    public function test_unauthenticated_users_cannot_access_payment_methods(): void
    {
        // Create a payment method
        PaymentMethod::factory()->create([
            'is_active' => true,
        ]);

        // Make request without authentication
        $response = $this->getJson('/api/v1/payment-system/payment-methods');

        // Assert unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test that empty result is returned when no active payment methods exist.
     */
    public function test_returns_empty_when_no_active_payment_methods(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create only inactive payment methods
        PaymentMethod::factory()->create([
            'is_active' => false,
        ]);

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-system/payment-methods');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonPath('data.payment_methods', [])
            ->assertJsonPath('data.total', 0);
    }
}
