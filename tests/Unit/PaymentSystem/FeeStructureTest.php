<?php

use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a fee structure with all required fields', function () {
        $feeStructure = FeeStructure::factory()->create([
            'name' => 'Tuition Fee',
            'name_mm' => 'စာသင်ကြေး',
            'amount' => 100000.00,
            'frequency' => 'monthly',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
            'supports_payment_period' => true,
            'is_active' => true,
        ]);

    expect($feeStructure)->toBeInstanceOf(FeeStructure::class)
        ->and($feeStructure->name)->toBe('Tuition Fee')
        ->and($feeStructure->name_mm)->toBe('စာသင်ကြေး')
        ->and($feeStructure->amount)->toBe('100000.00')
        ->and($feeStructure->frequency)->toBe('monthly')
        ->and($feeStructure->fee_type)->toBe('tuition')
        ->and($feeStructure->grade)->toBe('Grade 1')
        ->and($feeStructure->batch)->toBe('2024-2025')
        ->and($feeStructure->supports_payment_period)->toBeTrue()
        ->and($feeStructure->is_active)->toBeTrue();
});

test('isMonthly returns true for monthly fees', function () {
        $feeStructure = FeeStructure::factory()->monthly()->create();

    expect($feeStructure->isMonthly())->toBeTrue()
        ->and($feeStructure->isOneTime())->toBeFalse();
});

test('isOneTime returns true for one-time fees', function () {
        $feeStructure = FeeStructure::factory()->oneTime()->create();

    expect($feeStructure->isOneTime())->toBeTrue()
        ->and($feeStructure->isMonthly())->toBeFalse();
});

test('supportsPaymentPeriod returns true when supports_payment_period is true', function () {
        $feeStructure = FeeStructure::factory()->supportsPaymentPeriod()->create();

    expect($feeStructure->supportsPaymentPeriod())->toBeTrue()
        ->and($feeStructure->supports_payment_period)->toBeTrue();
});

test('supportsPaymentPeriod returns false when supports_payment_period is false', function () {
    $feeStructure = FeeStructure::factory()->create([
        'supports_payment_period' => false,
    ]);

    expect($feeStructure->supportsPaymentPeriod())->toBeFalse();
});

test('one-time fees should have target_month set', function () {
    $feeStructure = FeeStructure::factory()->oneTime()->create();

    expect($feeStructure->isOneTime())->toBeTrue()
        ->and($feeStructure->target_month)->toBeInt()
        ->and($feeStructure->target_month)->toBeGreaterThanOrEqual(1)
        ->and($feeStructure->target_month)->toBeLessThanOrEqual(12)
        ->and($feeStructure->supports_payment_period)->toBeFalse();
});

test('monthly fees should not have target_month set', function () {
    $feeStructure = FeeStructure::factory()->monthly()->create();

    expect($feeStructure->isMonthly())->toBeTrue()
        ->and($feeStructure->target_month)->toBeNull();
});

test('amount is cast to decimal with 2 decimal places', function () {
    $feeStructure = FeeStructure::factory()->create([
        'amount' => 123456.789,
    ]);

    expect($feeStructure->amount)->toBe('123456.79');
});

test('due_date is cast to date', function () {
    $feeStructure = FeeStructure::factory()->create([
        'due_date' => '2024-03-31',
    ]);

    expect($feeStructure->due_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('supports_payment_period is cast to boolean', function () {
    $feeStructure = FeeStructure::factory()->create([
        'supports_payment_period' => 1,
    ]);

    expect($feeStructure->supports_payment_period)->toBeTrue()
        ->and($feeStructure->supports_payment_period)->toBeBool();
});

test('is_active is cast to boolean', function () {
    $feeStructure = FeeStructure::factory()->create([
        'is_active' => 0,
    ]);

    expect($feeStructure->is_active)->toBeFalse()
        ->and($feeStructure->is_active)->toBeBool();
});

test('uses fee_structures_payment_system table', function () {
    $feeStructure = new FeeStructure();

    expect($feeStructure->getTable())->toBe('fee_structures_payment_system');
});

test('uses UUID for primary key', function () {
    $feeStructure = FeeStructure::factory()->create();

    expect($feeStructure->id)->toBeString()
        ->and(strlen($feeStructure->id))->toBe(36); // UUID length
});
