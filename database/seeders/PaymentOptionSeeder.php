<?php

namespace Database\Seeders;

use App\Models\PaymentOption;
use Illuminate\Database\Seeder;

class PaymentOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds payment options with discount rates for different payment periods.
     * 
     * Validates: Requirements 7.1, 7.2
     */
    public function run(): void
    {
        $paymentOptions = [
            [
                'label' => '1 Month',
                'label_mm' => '၁ လ',
                'months' => 1,
                'discount_percent' => 0.00, // 0% discount
                'badge' => null,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'label' => '2 Months',
                'label_mm' => '၂ လ',
                'months' => 2,
                'discount_percent' => 0.00, // 0% discount
                'badge' => null,
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'label' => '3 Months',
                'label_mm' => '၃ လ',
                'months' => 3,
                'discount_percent' => 5.00, // 5% discount
                'badge' => '5% OFF',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'label' => '6 Months',
                'label_mm' => '၆ လ',
                'months' => 6,
                'discount_percent' => 10.00, // 10% discount
                'badge' => '10% OFF',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'label' => '9 Months',
                'label_mm' => '၉ လ',
                'months' => 9,
                'discount_percent' => 15.00, // 15% discount
                'badge' => '15% OFF',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'label' => '12 Months',
                'label_mm' => '၁၂ လ',
                'months' => 12,
                'discount_percent' => 20.00, // 20% discount
                'badge' => '20% OFF',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($paymentOptions as $option) {
            PaymentOption::updateOrCreate(
                ['months' => $option['months']],
                $option
            );
        }
    }
}
