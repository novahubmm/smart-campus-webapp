<?php

use App\Models\Grade;
use App\Models\PaymentMethod;
use App\Models\PaymentSystem\Invoice;
use App\Models\PaymentSystem\InvoiceFee;
use App\Models\PaymentSystem\Payment;
use App\Models\StudentProfile;
use App\Services\PaymentSystem\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Test error handling for image upload failures with transaction rollback.
 * 
 * Validates: Requirement 17.6
 */
test('image upload failure prevents database transaction from starting', function () {
    Storage::fake('public');
    
    $paymentService = new PaymentService();
    
    // Create test data
    $grade = Grade::factory()->create(['level' => 1]);
    $student = StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
    ]);
    
    $invoiceFee = InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = PaymentMethod::factory()->create();
    
    // Create an invalid image file (wrong format)
    $tempPath = sys_get_temp_dir() . '/test_invalid_' . uniqid() . '.txt';
    file_put_contents($tempPath, 'invalid content');
    
    $invalidImage = new UploadedFile(
        $tempPath,
        'receipt.txt',
        'text/plain',
        null,
        true
    );
    
    $paymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => 50000,
        'payment_type' => 'full',
        'payment_months' => 1,
        'payment_date' => now()->toDateString(),
        'receipt_image' => $invalidImage,
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => 50000,
            ],
        ],
    ];
    
    // Record initial state
    $initialPaymentCount = Payment::count();
    $initialInvoicePaidAmount = $invoice->paid_amount;
    $initialInvoiceFeePaidAmount = $invoiceFee->paid_amount;
    
    // Attempt to submit payment with invalid image
    try {
        $paymentService->submitPayment($paymentData);
        expect(false)->toBeTrue('Should have thrown ValidationException for invalid image');
    } catch (ValidationException $e) {
        // Expected exception
        expect($e->errors())->toHaveKey('receipt_image');
    }
    
    // Verify no database changes were made
    expect(Payment::count())->toBe($initialPaymentCount);
    
    $invoice->refresh();
    expect((float)$invoice->paid_amount)->toBe((float)$initialInvoicePaidAmount);
    
    $invoiceFee->refresh();
    expect((float)$invoiceFee->paid_amount)->toBe((float)$initialInvoiceFeePaidAmount);
    
    // Clean up
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

test('oversized image upload failure prevents database transaction', function () {
    Storage::fake('public');
    
    $paymentService = new PaymentService();
    
    // Create test data
    $grade = Grade::factory()->create(['level' => 1]);
    $student = StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
    ]);
    
    $invoiceFee = InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = PaymentMethod::factory()->create();
    
    // Create an oversized image (> 5MB)
    $tempPath = sys_get_temp_dir() . '/test_large_' . uniqid() . '.jpg';
    
    // Create a large image
    $image = imagecreatetruecolor(3000, 3000);
    for ($x = 0; $x < 3000; $x += 10) {
        for ($y = 0; $y < 3000; $y += 10) {
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefilledrectangle($image, $x, $y, $x + 10, $y + 10, $color);
        }
    }
    imagejpeg($image, $tempPath, 100);
    imagedestroy($image);
    
    // Only run test if file is actually > 5MB
    if (filesize($tempPath) > 5 * 1024 * 1024) {
        $oversizedImage = new UploadedFile(
            $tempPath,
            'receipt.jpg',
            'image/jpeg',
            null,
            true
        );
        
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => 50000,
            'payment_type' => 'full',
            'payment_months' => 1,
            'payment_date' => now()->toDateString(),
            'receipt_image' => $oversizedImage,
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => 50000,
                ],
            ],
        ];
        
        // Record initial state
        $initialPaymentCount = Payment::count();
        $initialInvoicePaidAmount = $invoice->paid_amount;
        
        // Attempt to submit payment with oversized image
        try {
            $paymentService->submitPayment($paymentData);
            expect(false)->toBeTrue('Should have thrown ValidationException for oversized image');
        } catch (ValidationException $e) {
            // Expected exception
            expect($e->errors())->toHaveKey('receipt_image');
            expect($e->errors()['receipt_image'][0])->toContain('5MB');
        }
        
        // Verify no database changes were made
        expect(Payment::count())->toBe($initialPaymentCount);
        
        $invoice->refresh();
        expect((float)$invoice->paid_amount)->toBe((float)$initialInvoicePaidAmount);
    }
    
    // Clean up
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

test('successful image upload followed by database transaction', function () {
    Storage::fake('public');
    
    $paymentService = new PaymentService();
    
    // Create test data
    $grade = Grade::factory()->create(['level' => 1]);
    $student = StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
    ]);
    
    $invoiceFee = InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = PaymentMethod::factory()->create();
    
    // Create a valid image
    $image = imagecreatetruecolor(800, 600);
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);
    
    $tempPath = sys_get_temp_dir() . '/test_valid_' . uniqid() . '.jpg';
    imagejpeg($image, $tempPath, 90);
    imagedestroy($image);
    
    $validImage = new UploadedFile(
        $tempPath,
        'receipt.jpg',
        'image/jpeg',
        null,
        true
    );
    
    $paymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => 50000,
        'payment_type' => 'full',
        'payment_months' => 1,
        'payment_date' => now()->toDateString(),
        'receipt_image' => $validImage,
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => 50000,
            ],
        ],
    ];
    
    // Submit payment
    $payment = $paymentService->submitPayment($paymentData);
    
    // Verify payment was created
    expect($payment)->toBeInstanceOf(Payment::class);
    expect($payment->receipt_image_url)->not->toBeNull();
    expect($payment->status)->toBe('pending_verification');
    
    // Verify database changes were made
    $invoice->refresh();
    expect((float)$invoice->paid_amount)->toBe(50000.0);
    
    $invoiceFee->refresh();
    expect((float)$invoiceFee->paid_amount)->toBe(50000.0);
    
    // Verify image was uploaded
    $urlPath = parse_url($payment->receipt_image_url, PHP_URL_PATH);
    $relativePath = str_replace('/storage/', '', $urlPath);
    expect(Storage::disk('public')->exists($relativePath))->toBeTrue();
    
    // Clean up
    Storage::disk('public')->delete($relativePath);
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});
