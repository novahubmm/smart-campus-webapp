<?php

namespace Tests\Unit\PaymentSystem;

use App\Models\PaymentOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentOptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_option_can_be_created(): void
    {
        $paymentOption = PaymentOption::create([
            'months' => 3,
            'discount_percent' => 5.00,
            'label' => '3 Months',
            'label_mm' => '၃ လ',
            'badge' => 'Save 5%',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('payment_options', [
            'months' => 3,
            'discount_percent' => 5.00,
            'label' => '3 Months',
        ]);
    }

    public function test_get_discount_rate_returns_decimal(): void
    {
        $paymentOption = PaymentOption::create([
            'months' => 3,
            'discount_percent' => 5.00,
            'label' => '3 Months',
            'is_active' => true,
        ]);

        $this->assertEquals(0.05, $paymentOption->getDiscountRate());
    }

    public function test_get_discount_rate_for_different_percentages(): void
    {
        $option1 = PaymentOption::create([
            'months' => 1,
            'discount_percent' => 0.00,
            'label' => '1 Month',
            'is_active' => true,
        ]);

        $option2 = PaymentOption::create([
            'months' => 6,
            'discount_percent' => 10.00,
            'label' => '6 Months',
            'is_active' => true,
        ]);

        $option3 = PaymentOption::create([
            'months' => 12,
            'discount_percent' => 15.00,
            'label' => '12 Months',
            'is_active' => true,
        ]);

        $this->assertEquals(0.00, $option1->getDiscountRate());
        $this->assertEquals(0.10, $option2->getDiscountRate());
        $this->assertEquals(0.15, $option3->getDiscountRate());
    }

    public function test_active_scope_filters_active_options(): void
    {
        PaymentOption::create([
            'months' => 1,
            'discount_percent' => 0,
            'label' => 'Active Option',
            'is_active' => true,
        ]);

        PaymentOption::create([
            'months' => 3,
            'discount_percent' => 5,
            'label' => 'Inactive Option',
            'is_active' => false,
        ]);

        $activeOptions = PaymentOption::active()->get();

        $this->assertCount(1, $activeOptions);
        $this->assertEquals('Active Option', $activeOptions->first()->label);
    }

    public function test_ordered_scope_sorts_by_sort_order(): void
    {
        PaymentOption::create([
            'months' => 12,
            'discount_percent' => 15,
            'label' => 'Third',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        PaymentOption::create([
            'months' => 1,
            'discount_percent' => 0,
            'label' => 'First',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PaymentOption::create([
            'months' => 3,
            'discount_percent' => 5,
            'label' => 'Second',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $orderedOptions = PaymentOption::ordered()->get();

        $this->assertEquals('First', $orderedOptions[0]->label);
        $this->assertEquals('Second', $orderedOptions[1]->label);
        $this->assertEquals('Third', $orderedOptions[2]->label);
    }

    public function test_casts_are_applied_correctly(): void
    {
        $paymentOption = PaymentOption::create([
            'months' => '3',
            'discount_percent' => '5.00',
            'label' => 'Test',
            'is_default' => '1',
            'is_active' => '1',
            'sort_order' => '2',
        ]);

        $this->assertIsInt($paymentOption->months);
        $this->assertIsString($paymentOption->discount_percent);
        $this->assertIsBool($paymentOption->is_default);
        $this->assertIsBool($paymentOption->is_active);
        $this->assertIsInt($paymentOption->sort_order);
    }
}
