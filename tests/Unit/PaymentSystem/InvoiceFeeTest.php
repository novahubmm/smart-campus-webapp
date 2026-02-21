<?php

use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create an invoice fee with all required fields', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'fee_name' => 'Tuition Fee',
        'fee_name_mm' => 'စာသင်ကြေး',
        'amount' => 100000.00,
        'paid_amount' => 0,
        'remaining_amount' => 100000.00,
        'supports_payment_period' => true,
        'due_date' => '2024-03-31',
        'status' => 'unpaid',
    ]);

    expect($invoiceFee)->toBeInstanceOf(InvoiceFee::class)
        ->and($invoiceFee->fee_name)->toBe('Tuition Fee')
        ->and($invoiceFee->fee_name_mm)->toBe('စာသင်ကြေး')
        ->and($invoiceFee->amount)->toBe('100000.00')
        ->and($invoiceFee->paid_amount)->toBe('0.00')
        ->and($invoiceFee->remaining_amount)->toBe('100000.00')
        ->and($invoiceFee->supports_payment_period)->toBeTrue()
        ->and($invoiceFee->status)->toBe('unpaid');
});

test('uses invoice_fees table', function () {
    $invoiceFee = new InvoiceFee();

    expect($invoiceFee->getTable())->toBe('invoice_fees');
});

test('uses UUID for primary key', function () {
    $invoiceFee = InvoiceFee::factory()->create();

    expect($invoiceFee->id)->toBeString()
        ->and(strlen($invoiceFee->id))->toBe(36); // UUID length
});

test('amounts are cast to decimal with 2 decimal places', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'amount' => 123456.789,
        'paid_amount' => 50000.123,
        'remaining_amount' => 73456.666,
    ]);

    expect($invoiceFee->amount)->toBe('123456.79')
        ->and($invoiceFee->paid_amount)->toBe('50000.12')
        ->and($invoiceFee->remaining_amount)->toBe('73456.67');
});

test('due_date is cast to date', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'due_date' => '2024-03-31',
    ]);

    expect($invoiceFee->due_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('supports_payment_period is cast to boolean', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'supports_payment_period' => true,
    ]);

    expect($invoiceFee->supports_payment_period)->toBeTrue();
    
    $invoiceFee2 = InvoiceFee::factory()->create([
        'supports_payment_period' => false,
    ]);

    expect($invoiceFee2->supports_payment_period)->toBeFalse();
});

test('has invoice relationship', function () {
    $invoice = Invoice::factory()->create();
    $invoiceFee = InvoiceFee::factory()->create(['invoice_id' => $invoice->id]);

    expect($invoiceFee->invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoiceFee->invoice->id)->toBe($invoice->id);
});

test('has feeStructure relationship', function () {
    $feeStructure = FeeStructure::factory()->create();
    $invoiceFee = InvoiceFee::factory()->create(['fee_id' => $feeStructure->id]);

    expect($invoiceFee->feeStructure)->toBeInstanceOf(FeeStructure::class)
        ->and($invoiceFee->feeStructure->id)->toBe($feeStructure->id);
});

test('isOverdue returns true when due date has passed and remaining amount is greater than zero', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'due_date' => now()->subDays(5),
        'remaining_amount' => 50000,
    ]);

    expect($invoiceFee->isOverdue())->toBeTrue();
});

test('isOverdue returns false when due date has not passed', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'due_date' => now()->addDays(5),
        'remaining_amount' => 50000,
    ]);

    expect($invoiceFee->isOverdue())->toBeFalse();
});

test('isOverdue returns false when fee is fully paid even if due date has passed', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'due_date' => now()->subDays(5),
        'remaining_amount' => 0,
    ]);

    expect($invoiceFee->isOverdue())->toBeFalse();
});

test('canAcceptPartialPayment returns false when fee is fully paid', function () {
    $invoiceFee = InvoiceFee::factory()->paid()->create();

    expect($invoiceFee->canAcceptPartialPayment())->toBeFalse();
});

test('canAcceptPartialPayment returns false when due date has passed', function () {
    $invoiceFee = InvoiceFee::factory()->unpaid()->create([
        'due_date' => now()->subDays(5),
    ]);

    expect($invoiceFee->canAcceptPartialPayment())->toBeFalse();
});

test('canAcceptPartialPayment returns true when fee is not fully paid and due date has not passed', function () {
    $invoiceFee = InvoiceFee::factory()->unpaid()->create([
        'due_date' => now()->addDays(10),
    ]);

    expect($invoiceFee->canAcceptPartialPayment())->toBeTrue();
});

test('canAcceptPartialPayment returns true for partially paid fee before due date', function () {
    $invoiceFee = InvoiceFee::factory()->partial()->create([
        'due_date' => now()->addDays(10),
    ]);

    expect($invoiceFee->canAcceptPartialPayment())->toBeTrue();
});

test('unpaid fee has zero paid amount', function () {
    $invoiceFee = InvoiceFee::factory()->unpaid()->create();

    expect($invoiceFee->status)->toBe('unpaid')
        ->and($invoiceFee->paid_amount)->toBe('0.00')
        ->and($invoiceFee->remaining_amount)->toBe($invoiceFee->amount);
});

test('partial fee has some paid amount', function () {
    $invoiceFee = InvoiceFee::factory()->partial()->create();

    expect($invoiceFee->status)->toBe('partial')
        ->and($invoiceFee->paid_amount)->toBeGreaterThan(0)
        ->and($invoiceFee->paid_amount)->toBeLessThan($invoiceFee->amount)
        ->and($invoiceFee->remaining_amount)->toBeGreaterThan(0);
});

test('paid fee has full paid amount', function () {
    $invoiceFee = InvoiceFee::factory()->paid()->create();

    expect($invoiceFee->status)->toBe('paid')
        ->and($invoiceFee->paid_amount)->toBe($invoiceFee->amount)
        ->and($invoiceFee->remaining_amount)->toBe('0.00');
});

test('overdue fee has past due date', function () {
    $invoiceFee = InvoiceFee::factory()->overdue()->create();

    expect($invoiceFee->due_date->isPast())->toBeTrue();
});

test('fee with supports_payment_period true is set correctly', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'supports_payment_period' => true,
    ]);

    expect($invoiceFee->supports_payment_period)->toBeTrue();
});

test('fee with supports_payment_period false is set correctly', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'supports_payment_period' => false,
    ]);

    expect($invoiceFee->supports_payment_period)->toBeFalse();
});

test('remaining amount equals amount minus paid amount', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'amount' => 100000,
        'paid_amount' => 30000,
        'remaining_amount' => 70000,
    ]);

    expect($invoiceFee->remaining_amount)
        ->toBe(bcadd($invoiceFee->amount, -$invoiceFee->paid_amount, 2));
});

test('bilingual fee names are stored correctly', function () {
    $invoiceFee = InvoiceFee::factory()->create([
        'fee_name' => 'Transportation Fee',
        'fee_name_mm' => 'သယ်ယူပို့ဆောင်ရေးကြေး',
    ]);

    expect($invoiceFee->fee_name)->toBe('Transportation Fee')
        ->and($invoiceFee->fee_name_mm)->toBe('သယ်ယူပို့ဆောင်ရေးကြေး');
});

