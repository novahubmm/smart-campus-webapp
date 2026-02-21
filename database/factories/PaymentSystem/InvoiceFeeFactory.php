<?php

namespace Database\Factories\PaymentSystem;

use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSystem\InvoiceFee>
 */
class InvoiceFeeFactory extends Factory
{
    protected $model = InvoiceFee::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10000, 500000);
        $paidAmount = $this->faker->randomFloat(2, 0, $amount);
        $remainingAmount = $amount - $paidAmount;

        return [
            'invoice_id' => Invoice::factory(),
            'fee_id' => FeeStructure::factory(),
            'fee_name' => $this->faker->words(3, true),
            'fee_name_mm' => $this->faker->words(3, true),
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'supports_payment_period' => $this->faker->boolean(70),
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement(['unpaid', 'partial', 'paid']),
        ];
    }

    /**
     * Indicate that the fee is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
            'paid_amount' => 0,
            'remaining_amount' => $attributes['amount'],
        ]);
    }

    /**
     * Indicate that the fee is partially paid.
     */
    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'];
            $paidAmount = $this->faker->randomFloat(2, 5000, $amount - 5000);
            
            return [
                'status' => 'partial',
                'paid_amount' => $paidAmount,
                'remaining_amount' => $amount - $paidAmount,
            ];
        });
    }

    /**
     * Indicate that the fee is fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['amount'],
            'remaining_amount' => 0,
        ]);
    }

    /**
     * Indicate that the fee is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
