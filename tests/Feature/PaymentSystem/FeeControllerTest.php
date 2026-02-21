<?php

namespace Tests\Feature\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an admin can create a monthly fee category.
     */
    public function test_admin_can_create_monthly_fee_category(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        // Prepare fee data
        $feeData = [
            'name' => 'Tuition Fee',
            'name_mm' => 'စာသင်ကြေး',
            'description' => 'Monthly tuition fee',
            'description_mm' => 'လစဉ်စာသင်ကြေး',
            'amount' => 50000,
            'frequency' => 'monthly',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-12-31',
            'supports_payment_period' => true,
            'is_active' => true,
        ];

        // Make request
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payment-system/fees', $feeData);

        // Assert response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'fee' => [
                        'id',
                        'name',
                        'name_mm',
                        'amount',
                        'frequency',
                        'fee_type',
                        'grade',
                        'batch',
                        'due_date',
                        'supports_payment_period',
                        'is_active',
                    ],
                    'message',
                ],
                'message',
            ]);

        // Assert database
        $this->assertDatabaseHas('fee_structures_payment_system', [
            'name' => 'Tuition Fee',
            'frequency' => 'monthly',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
        ]);
    }

    /**
     * Test that an admin can create a one-time fee category.
     */
    public function test_admin_can_create_one_time_fee_category(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        // Prepare fee data
        $feeData = [
            'name' => 'Sports Fee',
            'name_mm' => 'အားကစားကြေး',
            'description' => 'One-time sports fee for March',
            'amount' => 20000,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 2',
            'batch' => '2024-2025',
            'target_month' => 3,
            'due_date' => '2024-03-31',
            'is_active' => true,
        ];

        // Make request
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payment-system/fees', $feeData);

        // Assert response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'fee' => [
                        'id',
                        'name',
                        'amount',
                        'frequency',
                        'fee_type',
                        'grade',
                        'batch',
                        'target_month',
                        'due_date',
                    ],
                    'message',
                ],
                'message',
            ]);

        // Assert database
        $this->assertDatabaseHas('fee_structures_payment_system', [
            'name' => 'Sports Fee',
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 2',
            'target_month' => 3,
        ]);
    }

    /**
     * Test that validation fails for invalid frequency.
     */
    public function test_validation_fails_for_invalid_frequency(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        // Prepare invalid fee data
        $feeData = [
            'name' => 'Test Fee',
            'amount' => 10000,
            'frequency' => 'invalid_frequency',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-12-31',
        ];

        // Make request
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payment-system/fees', $feeData);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    }

    /**
     * Test that validation fails when target_month is missing for one-time fees.
     */
    public function test_validation_fails_when_target_month_missing_for_one_time_fee(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Create an admin user
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        // Prepare fee data without target_month
        $feeData = [
            'name' => 'Test Fee',
            'amount' => 10000,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-12-31',
        ];

        // Make request
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/payment-system/fees', $feeData);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['target_month']);
    }

    /**
     * Test that non-admin users cannot create fee categories.
     */
    public function test_non_admin_cannot_create_fee_category(): void
    {
        // Create a regular user (not admin)
        $user = User::factory()->create();

        // Prepare fee data
        $feeData = [
            'name' => 'Test Fee',
            'amount' => 10000,
            'frequency' => 'monthly',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-12-31',
        ];

        // Make request
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/payment-system/fees', $feeData);

        // Assert forbidden
        $response->assertStatus(403);
    }
}
