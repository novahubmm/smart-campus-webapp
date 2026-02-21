<?php

namespace Database\Factories\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSystem\FeeStructure>
 */
class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition(): array
    {
        $frequency = $this->faker->randomElement(['one_time', 'monthly']);
        $feeType = $this->faker->randomElement([
            'tuition',
            'transportation',
            'library',
            'lab',
            'sports',
            'course_materials',
            'other'
        ]);

        return [
            'name' => $this->faker->words(3, true),
            'name_mm' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'description_mm' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 10000, 500000),
            'frequency' => $frequency,
            'fee_type' => $feeType,
            'grade' => 'Grade ' . $this->faker->numberBetween(1, 12),
            'batch' => $this->faker->year() . '-' . ($this->faker->year() + 1),
            'target_month' => $frequency === 'one_time' ? $this->faker->numberBetween(1, 12) : null,
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'supports_payment_period' => $frequency === 'monthly' ? $this->faker->boolean(70) : false,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the fee is a monthly fee.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
            'target_month' => null,
        ]);
    }

    /**
     * Indicate that the fee is a one-time fee.
     */
    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'one_time',
            'target_month' => $this->faker->numberBetween(1, 12),
            'supports_payment_period' => false,
        ]);
    }

    /**
     * Indicate that the fee supports payment periods.
     */
    public function supportsPaymentPeriod(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
            'supports_payment_period' => true,
            'target_month' => null,
        ]);
    }

    /**
     * Indicate that the fee is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the fee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
