<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Bank',
            'name_mm' => $this->faker->company() . ' ဘဏ်',
            'type' => $this->faker->randomElement(['bank', 'mobile_wallet']),
            'account_number' => $this->faker->numerify('##########'),
            'account_name' => $this->faker->name(),
            'account_name_mm' => $this->faker->name(),
            'logo_url' => $this->faker->imageUrl(200, 200, 'business'),
            'is_active' => true,
            'instructions' => $this->faker->sentence(),
            'instructions_mm' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the payment method is a bank.
     */
    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bank',
        ]);
    }

    /**
     * Indicate that the payment method is a mobile wallet.
     */
    public function mobileWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mobile_wallet',
        ]);
    }

    /**
     * Indicate that the payment method is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the payment method is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
