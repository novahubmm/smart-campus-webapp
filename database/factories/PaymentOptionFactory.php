<?php

namespace Database\Factories;

use App\Models\PaymentOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentOption>
 */
class PaymentOptionFactory extends Factory
{
    protected $model = PaymentOption::class;

    public function definition(): array
    {
        $months = $this->faker->randomElement([1, 3, 6, 12]);
        $discountPercent = match($months) {
            1 => 0,
            3 => 5,
            6 => 10,
            12 => 15,
            default => 0,
        };

        return [
            'months' => $months,
            'discount_percent' => $discountPercent,
            'label' => $months . ' Month' . ($months > 1 ? 's' : ''),
            'label_mm' => $this->convertToMyanmarNumber($months) . ' လ',
            'badge' => $discountPercent > 0 ? $discountPercent . '% OFF' : null,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => $months,
        ];
    }

    /**
     * Convert number to Myanmar numerals.
     */
    private function convertToMyanmarNumber(int $number): string
    {
        $myanmarNumerals = ['၀', '၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉'];
        $digits = str_split((string) $number);
        
        return implode('', array_map(fn($digit) => $myanmarNumerals[(int) $digit], $digits));
    }

    /**
     * Indicate that the payment option is for 1 month.
     */
    public function oneMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'months' => 1,
            'discount_percent' => 0,
            'label' => '1 Month',
            'label_mm' => '၁ လ',
            'badge' => null,
            'sort_order' => 1,
        ]);
    }

    /**
     * Indicate that the payment option is for 3 months.
     */
    public function threeMonths(): static
    {
        return $this->state(fn (array $attributes) => [
            'months' => 3,
            'discount_percent' => 5,
            'label' => '3 Months',
            'label_mm' => '၃ လ',
            'badge' => '5% OFF',
            'sort_order' => 2,
        ]);
    }

    /**
     * Indicate that the payment option is for 6 months.
     */
    public function sixMonths(): static
    {
        return $this->state(fn (array $attributes) => [
            'months' => 6,
            'discount_percent' => 10,
            'label' => '6 Months',
            'label_mm' => '၆ လ',
            'badge' => '10% OFF',
            'sort_order' => 3,
        ]);
    }

    /**
     * Indicate that the payment option is for 12 months.
     */
    public function twelveMonths(): static
    {
        return $this->state(fn (array $attributes) => [
            'months' => 12,
            'discount_percent' => 15,
            'label' => '12 Months',
            'label_mm' => '၁၂ လ',
            'badge' => '15% OFF',
            'sort_order' => 4,
        ]);
    }

    /**
     * Indicate that the payment option is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the payment option is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the payment option is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
