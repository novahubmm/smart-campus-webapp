<?php

use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// Helper function to create a test student
function createTestStudent(): StudentProfile
{
    $user = User::create([
        'name' => 'Test Student',
        'email' => 'student' . uniqid() . '@test.com',
        'password' => bcrypt('password'),
    ]);
    
    return StudentProfile::create([
        'user_id' => $user->id,
        'student_id' => 'STU-' . uniqid(),
        'student_identifier' => 'STU-' . uniqid(),
    ]);
}

test('can create an invoice with all required fields', function () {
    $invoice = Invoice::factory()->create([
        'invoice_number' => 'INV-123456',
        'academic_year' => '2024-2025',
        'total_amount' => 500000.00,
        'paid_amount' => 0,
        'remaining_amount' => 500000.00,
        'due_date' => '2024-03-31',
        'status' => 'pending',
        'invoice_type' => 'monthly',
    ]);

    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->invoice_number)->toBe('INV-123456')
        ->and($invoice->academic_year)->toBe('2024-2025')
        ->and($invoice->total_amount)->toBe('500000.00')
        ->and($invoice->paid_amount)->toBe('0.00')
        ->and($invoice->remaining_amount)->toBe('500000.00')
        ->and($invoice->status)->toBe('pending')
        ->and($invoice->invoice_type)->toBe('monthly');
});

test('uses invoices_payment_system table', function () {
    $invoice = new Invoice();

    expect($invoice->getTable())->toBe('invoices_payment_system');
});

test('uses UUID for primary key', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->id)->toBeString()
        ->and(strlen($invoice->id))->toBe(36); // UUID length
});

test('amounts are cast to decimal with 2 decimal places', function () {
    $invoice = Invoice::factory()->create([
        'total_amount' => 123456.789,
        'paid_amount' => 50000.123,
        'remaining_amount' => 73456.666,
    ]);

    expect($invoice->total_amount)->toBe('123456.79')
        ->and($invoice->paid_amount)->toBe('50000.12')
        ->and($invoice->remaining_amount)->toBe('73456.67');
});

test('due_date is cast to date', function () {
    $invoice = Invoice::factory()->create([
        'due_date' => '2024-03-31',
    ]);

    expect($invoice->due_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('has fees relationship', function () {
    $invoice = Invoice::factory()->create();
    InvoiceFee::factory()->count(3)->create(['invoice_id' => $invoice->id]);

    expect($invoice->fees)->toHaveCount(3)
        ->and($invoice->fees->first())->toBeInstanceOf(InvoiceFee::class);
});

test('has payments relationship', function () {
    $invoice = Invoice::factory()->create();
    
    // Create a payment method first
    $paymentMethod = DB::table('payment_methods')->insertGetId([
        'id' => \Illuminate\Support\Str::uuid(),
        'name' => 'Test Bank',
        'name_mm' => 'Test Bank MM',
        'type' => 'bank',
        'account_number' => '1234567890',
        'account_name' => 'Test Account',
        'is_active' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Get the payment method ID
    $paymentMethodId = DB::table('payment_methods')->where('name', 'Test Bank')->value('id');
    
    // Create payment
    Payment::create([
        'payment_number' => 'PAY-123456',
        'student_id' => $invoice->student_id,
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethodId,
        'payment_amount' => 50000,
        'payment_type' => 'partial',
        'payment_months' => 1,
        'payment_date' => now(),
        'status' => 'pending_verification',
    ]);

    expect($invoice->payments)->toHaveCount(1)
        ->and($invoice->payments->first())->toBeInstanceOf(Payment::class);
});

test('has student relationship', function () {
    // Create a user first
    $user = User::create([
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'password' => bcrypt('password'),
    ]);
    
    $student = StudentProfile::create([
        'user_id' => $user->id,
        'student_id' => 'STU-001',
        'student_identifier' => 'STU-001',
    ]);
    
    $invoice = Invoice::factory()->create(['student_id' => $student->id]);

    expect($invoice->student)->toBeInstanceOf(StudentProfile::class)
        ->and($invoice->student->id)->toBe($student->id);
});

test('isOverdue returns true when due date has passed and remaining amount is greater than zero', function () {
    $invoice = Invoice::factory()->create([
        'due_date' => now()->subDays(5),
        'remaining_amount' => 100000,
    ]);

    expect($invoice->isOverdue())->toBeTrue();
});

test('isOverdue returns false when due date has not passed', function () {
    $invoice = Invoice::factory()->create([
        'due_date' => now()->addDays(5),
        'remaining_amount' => 100000,
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('isOverdue returns false when invoice is fully paid even if due date has passed', function () {
    $invoice = Invoice::factory()->create([
        'due_date' => now()->subDays(5),
        'remaining_amount' => 0,
    ]);

    expect($invoice->isOverdue())->toBeFalse();
});

test('isFullyPaid returns true when remaining amount is zero', function () {
    $invoice = Invoice::factory()->paid()->create();

    expect($invoice->isFullyPaid())->toBeTrue()
        ->and($invoice->remaining_amount)->toBe('0.00');
});

test('isFullyPaid returns false when remaining amount is greater than zero', function () {
    $invoice = Invoice::factory()->pending()->create();

    expect($invoice->isFullyPaid())->toBeFalse()
        ->and($invoice->remaining_amount)->toBeGreaterThan(0);
});

test('canAcceptPartialPayment returns false when invoice is fully paid', function () {
    $invoice = Invoice::factory()->paid()->create();

    expect($invoice->canAcceptPartialPayment())->toBeFalse();
});

test('canAcceptPartialPayment returns false when all fees are overdue', function () {
    $invoice = Invoice::factory()->pending()->create();
    
    // Create overdue fees
    InvoiceFee::factory()->count(3)->create([
        'invoice_id' => $invoice->id,
        'due_date' => now()->subDays(5),
        'status' => 'unpaid',
    ]);

    expect($invoice->canAcceptPartialPayment())->toBeFalse();
});

test('canAcceptPartialPayment returns true when invoice is not fully paid and has non-overdue fees', function () {
    $invoice = Invoice::factory()->pending()->create();
    
    // Create mix of overdue and non-overdue fees
    InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'due_date' => now()->subDays(5),
        'status' => 'unpaid',
    ]);
    
    InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'due_date' => now()->addDays(10),
        'status' => 'unpaid',
    ]);

    expect($invoice->canAcceptPartialPayment())->toBeTrue();
});

test('canAcceptPartialPayment returns true when all fees are not overdue', function () {
    $invoice = Invoice::factory()->pending()->create();
    
    // Create non-overdue fees
    InvoiceFee::factory()->count(3)->create([
        'invoice_id' => $invoice->id,
        'due_date' => now()->addDays(10),
        'status' => 'unpaid',
    ]);

    expect($invoice->canAcceptPartialPayment())->toBeTrue();
});

test('pending invoice has zero paid amount', function () {
    $invoice = Invoice::factory()->pending()->create();

    expect($invoice->status)->toBe('pending')
        ->and($invoice->paid_amount)->toBe('0.00')
        ->and($invoice->remaining_amount)->toBe($invoice->total_amount);
});

test('partial invoice has some paid amount', function () {
    $invoice = Invoice::factory()->partial()->create();

    expect($invoice->status)->toBe('partial')
        ->and($invoice->paid_amount)->toBeGreaterThan(0)
        ->and($invoice->paid_amount)->toBeLessThan($invoice->total_amount)
        ->and($invoice->remaining_amount)->toBeGreaterThan(0);
});

test('paid invoice has full paid amount', function () {
    $invoice = Invoice::factory()->paid()->create();

    expect($invoice->status)->toBe('paid')
        ->and($invoice->paid_amount)->toBe($invoice->total_amount)
        ->and($invoice->remaining_amount)->toBe('0.00');
});

test('overdue invoice has past due date', function () {
    $invoice = Invoice::factory()->overdue()->create();

    expect($invoice->status)->toBe('overdue')
        ->and($invoice->due_date->isPast())->toBeTrue();
});

test('monthly invoice type is set correctly', function () {
    $invoice = Invoice::factory()->monthly()->create();

    expect($invoice->invoice_type)->toBe('monthly');
});

test('one-time invoice type is set correctly', function () {
    $invoice = Invoice::factory()->oneTime()->create();

    expect($invoice->invoice_type)->toBe('one_time');
});

test('remaining balance invoice has parent invoice', function () {
    $parentInvoice = Invoice::factory()->create();
    $invoice = Invoice::factory()->create([
        'invoice_type' => 'remaining_balance',
        'parent_invoice_id' => $parentInvoice->id,
    ]);

    expect($invoice->invoice_type)->toBe('remaining_balance')
        ->and($invoice->parent_invoice_id)->toBe($parentInvoice->id);
});
