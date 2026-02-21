<?php

namespace Database\Factories\PaymentSystem;

use App\Models\PaymentMethod;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSystem\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-' . $this->faker->unique()->numerify('######'),
            'student_id' => function () {
                // Create a test student for the payment
                $user = User::firstOrCreate(
                    ['email' => 'test.student@example.com'],
                    [
                        'name' => 'Test Student',
                        'password' => bcrypt('password'),
                    ]
                );
                
                $student = StudentProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id' => 'STU-TEST-001',
                        'student_identifier' => 'STU-TEST-001',
                    ]
                );
                
                return $student->id;
            },
            'invoice_id' => Invoice::factory(),
            'payment_method_id' => function () {
                return PaymentMethod::firstOrCreate(
                    ['name' => 'Test Bank'],
                    [
                        'name_mm' => 'Test Bank MM',
                        'type' => 'bank',
                        'account_number' => '1234567890',
                        'account_name' => 'Test Account',
                        'is_active' => true,
                        'sort_order' => 1,
                    ]
                )->id;
            },
            'payment_amount' => $this->faker->randomFloat(2, 10000, 500000),
            'payment_type' => $this->faker->randomElement(['full', 'partial']),
            'payment_months' => $this->faker->randomElement([1, 3, 6, 12]),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'receipt_image_url' => $this->faker->imageUrl(640, 480, 'receipt'),
            'status' => 'pending_verification',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the payment is pending verification.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_verification',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the payment is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'verified_by' => function () {
                return User::firstOrCreate(
                    ['email' => 'admin@example.com'],
                    [
                        'name' => 'Admin User',
                        'password' => bcrypt('password'),
                    ]
                )->id;
            },
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the payment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the payment is a full payment.
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'full',
        ]);
    }

    /**
     * Indicate that the payment is a partial payment.
     */
    public function partial(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => 'partial',
        ]);
    }
}
