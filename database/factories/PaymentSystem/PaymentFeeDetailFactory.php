<?php

namespace Database\Factories\PaymentSystem;

use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\PaymentSystem\PaymentFeeDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSystem\PaymentFeeDetail>
 */
class PaymentFeeDetailFactory extends Factory
{
    protected $model = PaymentFeeDetail::class;

    public function definition(): array
    {
        $fullAmount = $this->faker->randomFloat(2, 10000, 100000);
        $paidAmount = $this->faker->randomFloat(2, 5000, $fullAmount);
        $isPartial = $paidAmount < $fullAmount;

        return [
            'payment_id' => Payment::factory(),
            'invoice_fee_id' => InvoiceFee::factory(),
            'fee_name' => $this->faker->randomElement(['Tuition Fee', 'Transportation Fee', 'Library Fee', 'Lab Fee']),
            'fee_name_mm' => $this->faker->randomElement(['စာသင်ကြေး', 'သယ်ယူပို့ဆောင်ရေးကြေး', 'စာကြည့်တိုက်ကြေး', 'ဓာတ်ခွဲခန်းကြေး']),
            'full_amount' => $fullAmount,
            'paid_amount' => $paidAmount,
            'is_partial' => $isPartial,
        ];
    }

    /**
     * Indicate that the fee detail is a full payment.
     */
    public function fullPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['full_amount'],
            'is_partial' => false,
        ]);
    }

    /**
     * Indicate that the fee detail is a partial payment.
     */
    public function partialPayment(): static
    {
        return $this->state(function (array $attributes) {
            $fullAmount = $attributes['full_amount'];
            $paidAmount = $this->faker->randomFloat(2, 5000, $fullAmount - 1000);
            
            return [
                'paid_amount' => $paidAmount,
                'is_partial' => true,
            ];
        });
    }
}
