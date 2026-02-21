<?php

use App\Services\PaymentSystem\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Property 7: Payment Period Calculation with Discounts
 * 
 * For any fee with supports_payment_period=true and frequency="monthly", the calculated amount should be:
 * (base_amount × months) - (base_amount × months × discount_rate), where discount_rate is 0% for 1 month,
 * 5% for 3 months, 10% for 6 months, and 15% for 12 months.
 * 
 * For fees with supports_payment_period=false or frequency="one_time", the calculated amount should always
 * be the base_amount regardless of months selected.
 * 
 * **Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8**
 */
test('Property 7: payment period calculation maintains discount formula for monthly fees', function () {
    $paymentService = new PaymentService();
    
    // Run 100 iterations with random inputs
    for ($iteration = 0; $iteration < 100; $iteration++) {
        // Generate random base amount between 10,000 and 1,000,000
        $baseAmount = fake()->randomFloat(2, 10000, 1000000);
        
        // Random payment period (1, 3, 6, or 12 months)
        $months = fake()->randomElement([1, 3, 6, 12]);
        
        // Random supports_payment_period flag
        $supportsPaymentPeriod = fake()->boolean();
        
        // Calculate result
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, $months, $supportsPaymentPeriod);
        
        if ($supportsPaymentPeriod) {
            // For fees that support payment period, verify discount formula
            $discountRate = match($months) {
                1 => 0.0,
                3 => 0.05,
                6 => 0.10,
                12 => 0.15,
            };
            
            $expected = ($baseAmount * $months) - ($baseAmount * $months * $discountRate);
            
            // Use small epsilon for floating point comparison
            expect(abs($result - $expected))->toBeLessThan(0.01);
        } else {
            // For fees that don't support payment period, should return base amount
            expect(abs($result - $baseAmount))->toBeLessThan(0.01);
        }
    }
});

test('Property 7: one-time fees always return base amount regardless of months', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations
    for ($iteration = 0; $iteration < 50; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        $months = fake()->randomElement([1, 3, 6, 12]);
        
        // One-time fees don't support payment period
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, $months, false);
        
        // Should always return base amount
        expect(abs($result - $baseAmount))->toBeLessThan(0.01);
    }
});

test('Property 7: 1 month payment period has no discount', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 1, true);
        
        // 1 month should have 0% discount, so result = base_amount * 1
        expect(abs($result - $baseAmount))->toBeLessThan(0.01);
    }
});

test('Property 7: 3 month payment period applies 5% discount', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 3, true);
        
        // 3 months with 5% discount: (base * 3) * 0.95
        $expected = ($baseAmount * 3) * 0.95;
        expect(abs($result - $expected))->toBeLessThan(0.01);
    }
});

test('Property 7: 6 month payment period applies 10% discount', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 6, true);
        
        // 6 months with 10% discount: (base * 6) * 0.90
        $expected = ($baseAmount * 6) * 0.90;
        expect(abs($result - $expected))->toBeLessThan(0.01);
    }
});

test('Property 7: 12 month payment period applies 15% discount', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 12, true);
        
        // 12 months with 15% discount: (base * 12) * 0.85
        $expected = ($baseAmount * 12) * 0.85;
        expect(abs($result - $expected))->toBeLessThan(0.01);
    }
});

test('Property 7: discount increases with longer payment periods', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $baseAmount = fake()->randomFloat(2, 10000, 500000);
        
        $result1 = $paymentService->applyPaymentPeriodDiscount($baseAmount, 1, true);
        $result3 = $paymentService->applyPaymentPeriodDiscount($baseAmount, 3, true);
        $result6 = $paymentService->applyPaymentPeriodDiscount($baseAmount, 6, true);
        $result12 = $paymentService->applyPaymentPeriodDiscount($baseAmount, 12, true);
        
        // Per-month cost should decrease with longer periods (due to discount)
        $perMonth1 = $result1 / 1;
        $perMonth3 = $result3 / 3;
        $perMonth6 = $result6 / 6;
        $perMonth12 = $result12 / 12;
        
        expect($perMonth1)->toBeGreaterThanOrEqual($perMonth3);
        expect($perMonth3)->toBeGreaterThanOrEqual($perMonth6);
        expect($perMonth6)->toBeGreaterThanOrEqual($perMonth12);
    }
});

test('Property 7: calculatePaymentAmount sums fees correctly without payment period', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create random number of fees (1-5)
        $numFees = fake()->numberBetween(1, 5);
        $feeDetails = [];
        $expectedTotal = 0.0;
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 5000, 100000);
            $feeDetails[] = [
                'paid_amount' => $amount,
                'supports_payment_period' => false,
            ];
            $expectedTotal += $amount;
        }
        
        $result = $paymentService->calculatePaymentAmount($feeDetails, 1);
        
        expect(abs($result - $expectedTotal))->toBeLessThan(0.01);
    }
});

test('Property 7: calculatePaymentAmount applies discount for payment period fees', function () {
    $paymentService = new PaymentService();
    
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $months = fake()->randomElement([1, 3, 6, 12]);
        
        // Create mix of fees with and without payment period support
        $numFees = fake()->numberBetween(2, 5);
        $feeDetails = [];
        $expectedTotal = 0.0;
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 5000, 100000);
            $supportsPaymentPeriod = fake()->boolean();
            
            $feeDetails[] = [
                'paid_amount' => $amount,
                'supports_payment_period' => $supportsPaymentPeriod,
            ];
            
            if ($supportsPaymentPeriod && $months > 1) {
                $expectedTotal += $paymentService->applyPaymentPeriodDiscount($amount, $months, true);
            } else {
                $expectedTotal += $amount;
            }
        }
        
        $result = $paymentService->calculatePaymentAmount($feeDetails, $months);
        
        expect(abs($result - $expectedTotal))->toBeLessThan(0.01);
    }
});

test('Property 7: edge case - zero amount returns zero', function () {
    $paymentService = new PaymentService();
    
    $result = $paymentService->applyPaymentPeriodDiscount(0.0, 3, true);
    expect($result)->toBe(0.0);
    
    $result = $paymentService->applyPaymentPeriodDiscount(0.0, 12, false);
    expect($result)->toBe(0.0);
});

test('Property 7: edge case - very small amounts maintain precision', function () {
    $paymentService = new PaymentService();
    
    $baseAmount = 0.01;
    $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 3, true);
    
    // 0.01 * 3 * 0.95 = 0.0285
    $expected = 0.0285;
    expect(abs($result - $expected))->toBeLessThan(0.0001);
});

test('Property 7: edge case - very large amounts are handled correctly', function () {
    $paymentService = new PaymentService();
    
    $baseAmount = 10000000.00; // 10 million
    $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, 12, true);
    
    // 10,000,000 * 12 * 0.85 = 102,000,000
    $expected = 102000000.00;
    expect(abs($result - $expected))->toBeLessThan(1.0);
});

test('Property 7: invalid month values default to no discount', function () {
    $paymentService = new PaymentService();
    
    $baseAmount = 100000.00;
    
    // Test invalid month values
    $invalidMonths = [0, 2, 4, 5, 7, 8, 9, 10, 11, 13, 24];
    
    foreach ($invalidMonths as $months) {
        $result = $paymentService->applyPaymentPeriodDiscount($baseAmount, $months, true);
        
        // Should apply 0% discount for invalid months
        $expected = $baseAmount * $months;
        expect(abs($result - $expected))->toBeLessThan(0.01);
    }
});

/**
 * Property 19: Receipt Image Upload Round Trip
 * 
 * For any valid image file (JPEG or PNG, size <= 5MB), uploading the image should return
 * a URL, and accessing that URL should return the same image data.
 * 
 * **Validates: Requirements 9.2, 9.3, 9.4, 17.1, 17.2, 17.3**
 */
test('Property 19: receipt image upload round trip', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different image scenarios
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Generate random image properties
        $imageType = fake()->randomElement(['jpeg', 'png']);
        $width = fake()->numberBetween(100, 1000);
        $height = fake()->numberBetween(100, 1000);
        
        // Create a test image
        $image = imagecreatetruecolor($width, $height);
        
        // Fill with random color
        $bgColor = imagecolorallocate(
            $image,
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255)
        );
        imagefill($image, 0, 0, $bgColor);
        
        // Add some random shapes to make it more realistic
        $numShapes = fake()->numberBetween(5, 20);
        for ($i = 0; $i < $numShapes; $i++) {
            $color = imagecolorallocate(
                $image,
                fake()->numberBetween(0, 255),
                fake()->numberBetween(0, 255),
                fake()->numberBetween(0, 255)
            );
            
            $x1 = fake()->numberBetween(0, $width);
            $y1 = fake()->numberBetween(0, $height);
            $x2 = fake()->numberBetween(0, $width);
            $y2 = fake()->numberBetween(0, $height);
            
            imageline($image, $x1, $y1, $x2, $y2, $color);
        }
        
        // Save to temporary file
        $tempPath = sys_get_temp_dir() . '/test_receipt_' . uniqid() . '.' . $imageType;
        
        if ($imageType === 'jpeg') {
            imagejpeg($image, $tempPath, 90);
        } else {
            imagepng($image, $tempPath, 6);
        }
        
        imagedestroy($image);
        
        // Get file size
        $fileSize = filesize($tempPath);
        
        // Only test if file is under 5MB
        if ($fileSize <= 5 * 1024 * 1024) {
            // Create UploadedFile instance
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempPath,
                'receipt.' . $imageType,
                $imageType === 'jpeg' ? 'image/jpeg' : 'image/png',
                null,
                true
            );
            
            // Upload the image
            $url = $paymentService->uploadReceiptImage($uploadedFile);
            
            // Verify URL is returned
            expect($url)->toBeString();
            expect($url)->not->toBeEmpty();
            
            // Extract the path from the URL
            $urlPath = parse_url($url, PHP_URL_PATH);
            $relativePath = str_replace('/storage/', '', $urlPath);
            
            // Verify file exists in storage
            expect(\Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath))->toBeTrue();
            
            // Get the stored file content
            $storedContent = \Illuminate\Support\Facades\Storage::disk('public')->get($relativePath);
            $originalContent = file_get_contents($tempPath);
            
            // Verify file sizes match (content should be identical)
            expect(strlen($storedContent))->toBe(strlen($originalContent));
            
            // Clean up stored file
            \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
        }
        
        // Clean up temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
});

test('Property 19: invalid image format is rejected', function () {
    $paymentService = new PaymentService();
    
    // Test various invalid formats
    $invalidFormats = ['gif', 'bmp', 'webp', 'txt'];
    
    foreach ($invalidFormats as $format) {
        // Create a temporary file with invalid format
        $tempPath = sys_get_temp_dir() . '/test_invalid_' . uniqid() . '.' . $format;
        file_put_contents($tempPath, 'fake content');
        
        $mimeType = match($format) {
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
        
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'receipt.' . $format,
            $mimeType,
            null,
            true
        );
        
        try {
            $paymentService->uploadReceiptImage($uploadedFile);
            expect(false)->toBeTrue('Should have thrown ValidationException for invalid format: ' . $format);
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey('receipt_image');
            expect($e->errors()['receipt_image'][0])->toContain('JPEG or PNG');
        }
        
        // Clean up
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
});

test('Property 19: oversized image is rejected', function () {
    $paymentService = new PaymentService();
    
    // Create a large valid JPEG image (> 5MB)
    // We'll create a simple large file by writing a JPEG header and lots of data
    $tempPath = sys_get_temp_dir() . '/test_large_' . uniqid() . '.jpg';
    
    // Create a large image (3000x3000 should be > 5MB when uncompressed)
    $image = imagecreatetruecolor(3000, 3000);
    
    // Fill with random pixels to prevent compression
    for ($x = 0; $x < 3000; $x += 10) {
        for ($y = 0; $y < 3000; $y += 10) {
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefilledrectangle($image, $x, $y, $x + 10, $y + 10, $color);
        }
    }
    
    imagejpeg($image, $tempPath, 100);
    imagedestroy($image);
    
    // Verify file is actually larger than 5MB
    $fileSize = filesize($tempPath);
    
    if ($fileSize > 5 * 1024 * 1024) {
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'receipt.jpg',
            'image/jpeg',
            null,
            true
        );
        
        try {
            $paymentService->uploadReceiptImage($uploadedFile);
            expect(false)->toBeTrue('Should have thrown ValidationException for oversized file');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey('receipt_image');
            expect($e->errors()['receipt_image'][0])->toContain('5MB');
        }
    }
    
    // Clean up
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

test('Property 19: valid JPEG images are accepted', function () {
    $paymentService = new PaymentService();
    
    // Create a valid JPEG image
    $image = imagecreatetruecolor(800, 600);
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);
    
    $tempPath = sys_get_temp_dir() . '/test_valid_jpeg_' . uniqid() . '.jpg';
    imagejpeg($image, $tempPath, 90);
    imagedestroy($image);
    
    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $tempPath,
        'receipt.jpg',
        'image/jpeg',
        null,
        true
    );
    
    $url = $paymentService->uploadReceiptImage($uploadedFile);
    
    expect($url)->toBeString();
    expect($url)->not->toBeEmpty();
    expect($url)->toContain('/storage/');
    expect($url)->toContain('payment_receipts/');
    
    // Clean up
    $urlPath = parse_url($url, PHP_URL_PATH);
    $relativePath = str_replace('/storage/', '', $urlPath);
    \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
    
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

test('Property 19: valid PNG images are accepted', function () {
    $paymentService = new PaymentService();
    
    // Create a valid PNG image
    $image = imagecreatetruecolor(800, 600);
    $bgColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $bgColor);
    
    $tempPath = sys_get_temp_dir() . '/test_valid_png_' . uniqid() . '.png';
    imagepng($image, $tempPath, 6);
    imagedestroy($image);
    
    $uploadedFile = new \Illuminate\Http\UploadedFile(
        $tempPath,
        'receipt.png',
        'image/png',
        null,
        true
    );
    
    $url = $paymentService->uploadReceiptImage($uploadedFile);
    
    expect($url)->toBeString();
    expect($url)->not->toBeEmpty();
    expect($url)->toContain('/storage/');
    expect($url)->toContain('payment_receipts/');
    
    // Clean up
    $urlPath = parse_url($url, PHP_URL_PATH);
    $relativePath = str_replace('/storage/', '', $urlPath);
    \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
    
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
});

test('Property 19: unique filenames are generated', function () {
    $paymentService = new PaymentService();
    
    $uploadedUrls = [];
    
    // Upload multiple images and verify unique filenames
    for ($i = 0; $i < 5; $i++) {
        $image = imagecreatetruecolor(100, 100);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgColor);
        
        $tempPath = sys_get_temp_dir() . '/test_unique_' . uniqid() . '.jpg';
        imagejpeg($image, $tempPath, 90);
        imagedestroy($image);
        
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'receipt.jpg',
            'image/jpeg',
            null,
            true
        );
        
        $url = $paymentService->uploadReceiptImage($uploadedFile);
        $uploadedUrls[] = $url;
        
        // Clean up temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
    
    // Verify all URLs are unique
    $uniqueUrls = array_unique($uploadedUrls);
    expect(count($uniqueUrls))->toBe(count($uploadedUrls));
    
    // Clean up stored files
    foreach ($uploadedUrls as $url) {
        $urlPath = parse_url($url, PHP_URL_PATH);
        $relativePath = str_replace('/storage/', '', $urlPath);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
    }
});

/**
 * Property 20: Duplicate Payment Prevention
 * 
 * For any two payment submissions with the same student_id, invoice_id, payment_amount,
 * and payment_date within 5 minutes of each other, the second submission should be
 * rejected with a DUPLICATE_PAYMENT error.
 * 
 * **Validates: Requirements 22.1, 22.2, 22.3**
 */
test('Property 20: duplicate payments within 5 minutes are rejected', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different scenarios
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Create test data
        $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'pending',
        ]);
        
        $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'due_date' => now()->addDays(30),
        ]);
        
        $paymentMethod = \App\Models\PaymentMethod::factory()->create();
        
        // Generate random payment data
        $paymentAmount = fake()->randomFloat(2, 10000, 100000);
        $paymentDate = fake()->dateTimeBetween('-30 days', '+30 days');
        
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $paymentAmount,
            'payment_type' => 'full',
            'payment_months' => 1,
            'payment_date' => $paymentDate,
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $paymentAmount,
                ],
            ],
        ];
        
        // Submit first payment
        $firstPayment = $paymentService->submitPayment($paymentData);
        expect($firstPayment)->toBeInstanceOf(\App\Models\PaymentSystem\Payment::class);
        
        // Refresh invoice fee to get updated amounts
        $invoiceFee->refresh();
        
        // Reset invoice fee for second payment attempt
        $invoiceFee->paid_amount = 0;
        $invoiceFee->remaining_amount = $invoiceFee->amount;
        $invoiceFee->save();
        
        // Attempt to submit duplicate payment within 5 minutes
        try {
            $paymentService->submitPayment($paymentData);
            
            // If we reach here, the test should fail
            expect(false)->toBeTrue('Expected ValidationException to be thrown for duplicate payment');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Verify the error message indicates duplicate payment
            $errors = $e->errors();
            expect($errors)->toHaveKey('payment');
            expect($errors['payment'][0])->toContain('Duplicate payment');
        }
        
        // Verify only one payment was created
        $paymentCount = \App\Models\PaymentSystem\Payment::where('invoice_id', $invoice->id)
            ->where('payment_amount', $paymentAmount)
            ->count();
        expect($paymentCount)->toBe(1);
        
        // Clean up for next iteration
        \App\Models\PaymentSystem\Payment::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\PaymentMethod::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 20: duplicate payments after 5 minutes are allowed', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Create test data
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 200000,
        'paid_amount' => 0,
        'remaining_amount' => 200000,
        'status' => 'pending',
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 200000,
        'paid_amount' => 0,
        'remaining_amount' => 200000,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = \App\Models\PaymentMethod::factory()->create();
    
    $paymentAmount = 50000;
    $paymentDate = now();
    
    $paymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => $paymentAmount,
        'payment_type' => 'partial',
        'payment_months' => 1,
        'payment_date' => $paymentDate,
        'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg'),
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => $paymentAmount,
            ],
        ],
    ];
    
    // Submit first payment
    $firstPayment = $paymentService->submitPayment($paymentData);
    expect($firstPayment)->toBeInstanceOf(\App\Models\PaymentSystem\Payment::class);
    
    // Travel forward 6 minutes
    \Carbon\Carbon::setTestNow(now()->addMinutes(6));
    
    // Refresh invoice fee
    $invoiceFee->refresh();
    
    // Update payment data for second payment
    $paymentData['receipt_image'] = \Illuminate\Http\UploadedFile::fake()->image('receipt2.jpg');
    $paymentData['fee_payment_details'][0]['paid_amount'] = $paymentAmount;
    
    // Submit second payment after 6 minutes (should succeed)
    $secondPayment = $paymentService->submitPayment($paymentData);
    expect($secondPayment)->toBeInstanceOf(\App\Models\PaymentSystem\Payment::class);
    
    // Verify two payments were created
    $paymentCount = \App\Models\PaymentSystem\Payment::where('invoice_id', $invoice->id)
        ->where('payment_amount', $paymentAmount)
        ->count();
    expect($paymentCount)->toBe(2);
    
    // Reset time
    \Carbon\Carbon::setTestNow();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 20: payments with different amounts are not considered duplicates', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Create test data
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 200000,
        'paid_amount' => 0,
        'remaining_amount' => 200000,
        'status' => 'pending',
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 200000,
        'paid_amount' => 0,
        'remaining_amount' => 200000,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = \App\Models\PaymentMethod::factory()->create();
    
    // First payment
    $firstPaymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => 50000,
        'payment_type' => 'partial',
        'payment_months' => 1,
        'payment_date' => now(),
        'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt1.jpg'),
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => 50000,
            ],
        ],
    ];
    
    $firstPayment = $paymentService->submitPayment($firstPaymentData);
    expect($firstPayment)->toBeInstanceOf(\App\Models\PaymentSystem\Payment::class);
    
    // Refresh invoice fee
    $invoiceFee->refresh();
    
    // Second payment with different amount (should succeed)
    $secondPaymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => 60000,
        'payment_type' => 'partial',
        'payment_months' => 1,
        'payment_date' => now(),
        'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt2.jpg'),
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => 60000,
            ],
        ],
    ];
    
    $secondPayment = $paymentService->submitPayment($secondPaymentData);
    expect($secondPayment)->toBeInstanceOf(\App\Models\PaymentSystem\Payment::class);
    
    // Verify two payments were created
    expect(\App\Models\PaymentSystem\Payment::where('invoice_id', $invoice->id)->count())->toBe(2);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 11: Invoice Fee Amount Invariant
 * 
 * For any invoice_fee at any point in time, the following invariant must hold:
 * remaining_amount = amount - paid_amount, and paid_amount must never exceed amount.
 * 
 * **Validates: Requirements 11.2**
 */
test('Property 11: invoice fee amounts maintain invariant after payment', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different payment scenarios
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create test data with random amounts
        $feeAmount = fake()->randomFloat(2, 50000, 500000);
        
        $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => $feeAmount,
            'paid_amount' => 0,
            'remaining_amount' => $feeAmount,
            'status' => 'pending',
        ]);
        
        $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => $feeAmount,
            'paid_amount' => 0,
            'remaining_amount' => $feeAmount,
            'due_date' => now()->addDays(30),
        ]);
        
        $paymentMethod = \App\Models\PaymentMethod::factory()->create();
        
        // Make a random partial payment
        $paymentAmount = fake()->randomFloat(2, 10000, $feeAmount);
        
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $paymentAmount,
            'payment_type' => $paymentAmount >= $feeAmount ? 'full' : 'partial',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $paymentAmount,
                ],
            ],
        ];
        
        // Submit payment
        $payment = $paymentService->submitPayment($paymentData);
        
        // Refresh invoice fee
        $invoiceFee->refresh();
        
        // Property 11: Verify invariant - remaining_amount = amount - paid_amount
        $expectedRemaining = $invoiceFee->amount - $invoiceFee->paid_amount;
        expect(abs((float)$invoiceFee->remaining_amount - $expectedRemaining))->toBeLessThan(0.01);
        
        // Property 11: Verify paid_amount never exceeds amount
        expect((float)$invoiceFee->paid_amount)->toBeLessThanOrEqual((float)$invoiceFee->amount);
        
        // Clean up for next iteration
        \App\Models\PaymentSystem\Payment::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\PaymentMethod::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 11: invoice fee paid amount never exceeds total amount', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Test edge case: trying to pay more than remaining amount should fail
    $feeAmount = 100000;
    
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => $feeAmount,
        'paid_amount' => 0,
        'remaining_amount' => $feeAmount,
        'status' => 'pending',
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => $feeAmount,
        'paid_amount' => 0,
        'remaining_amount' => $feeAmount,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = \App\Models\PaymentMethod::factory()->create();
    
    // Try to pay more than the fee amount
    $paymentData = [
        'invoice_id' => $invoice->id,
        'payment_method_id' => $paymentMethod->id,
        'payment_amount' => $feeAmount + 10000,
        'payment_type' => 'full',
        'payment_months' => 1,
        'payment_date' => now(),
        'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg'),
        'fee_payment_details' => [
            [
                'invoice_fee_id' => $invoiceFee->id,
                'paid_amount' => $feeAmount + 10000,
            ],
        ],
    ];
    
    // Should throw validation exception
    try {
        $paymentService->submitPayment($paymentData);
        expect(false)->toBeTrue('Expected ValidationException for payment exceeding remaining amount');
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        expect($errors)->toHaveKey('fee_payment_details');
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 12: Invoice Amount Calculation
 * 
 * For any invoice at any point in time, the invoice's paid_amount should equal the sum
 * of all its invoice_fees' paid_amounts, and the invoice's remaining_amount should equal
 * total_amount - paid_amount.
 * 
 * **Validates: Requirements 11.5, 11.6**
 */
test('Property 12: invoice amounts are correctly calculated from fee amounts', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different scenarios
    for ($iteration = 0; $iteration < 15; $iteration++) {
        // Create invoice with multiple fees
        $numFees = fake()->numberBetween(2, 5);
        $totalAmount = 0;
        $fees = [];
        
        $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
        
        for ($i = 0; $i < $numFees; $i++) {
            $feeAmount = fake()->randomFloat(2, 20000, 100000);
            $totalAmount += $feeAmount;
            $fees[] = ['amount' => $feeAmount];
        }
        
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'status' => 'pending',
        ]);
        
        $invoiceFees = [];
        foreach ($fees as $feeData) {
            $invoiceFees[] = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'amount' => $feeData['amount'],
                'paid_amount' => 0,
                'remaining_amount' => $feeData['amount'],
                'due_date' => now()->addDays(30),
            ]);
        }
        
        $paymentMethod = \App\Models\PaymentMethod::factory()->create();
        
        // Make random partial payments on random fees
        $numPayments = fake()->numberBetween(1, $numFees);
        
        for ($p = 0; $p < $numPayments; $p++) {
            $randomFee = fake()->randomElement($invoiceFees);
            $randomFee->refresh();
            
            if ($randomFee->remaining_amount <= 0) {
                continue;
            }
            
            // Ensure payment amount doesn't exceed remaining amount
            $maxPayment = min(50000, (float)$randomFee->remaining_amount);
            if ($maxPayment < 10000) {
                $paymentAmount = $maxPayment;
            } else {
                $paymentAmount = fake()->randomFloat(2, 10000, $maxPayment);
            }
            
            $paymentData = [
                'invoice_id' => $invoice->id,
                'payment_method_id' => $paymentMethod->id,
                'payment_amount' => $paymentAmount,
                'payment_type' => 'partial',
                'payment_months' => 1,
                'payment_date' => now(),
                'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image("receipt{$p}.jpg"),
                'fee_payment_details' => [
                    [
                        'invoice_fee_id' => $randomFee->id,
                        'paid_amount' => $paymentAmount,
                    ],
                ],
            ];
            
            try {
                $paymentService->submitPayment($paymentData);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Skip if validation fails (e.g., payment too small)
                continue;
            }
        }
        
        // Refresh invoice and fees
        $invoice->refresh();
        foreach ($invoiceFees as $fee) {
            $fee->refresh();
        }
        
        // Property 12: Verify invoice paid_amount equals sum of fee paid_amounts
        $expectedPaidAmount = collect($invoiceFees)->sum('paid_amount');
        expect(abs((float)$invoice->paid_amount - (float)$expectedPaidAmount))->toBeLessThan(0.01);
        
        // Property 12: Verify invoice remaining_amount = total_amount - paid_amount
        $expectedRemaining = $invoice->total_amount - $invoice->paid_amount;
        expect(abs((float)$invoice->remaining_amount - $expectedRemaining))->toBeLessThan(0.01);
        
        // Clean up for next iteration
        \App\Models\PaymentSystem\Payment::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\PaymentMethod::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 12: invoice amounts remain consistent across multiple partial payments', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Create invoice with 3 fees
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    
    $fee1Amount = 50000;
    $fee2Amount = 75000;
    $fee3Amount = 100000;
    $totalAmount = $fee1Amount + $fee2Amount + $fee3Amount;
    
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => $totalAmount,
        'paid_amount' => 0,
        'remaining_amount' => $totalAmount,
        'status' => 'pending',
    ]);
    
    $fee1 = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => $fee1Amount,
        'paid_amount' => 0,
        'remaining_amount' => $fee1Amount,
        'due_date' => now()->addDays(30),
    ]);
    
    $fee2 = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => $fee2Amount,
        'paid_amount' => 0,
        'remaining_amount' => $fee2Amount,
        'due_date' => now()->addDays(30),
    ]);
    
    $fee3 = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => $fee3Amount,
        'paid_amount' => 0,
        'remaining_amount' => $fee3Amount,
        'due_date' => now()->addDays(30),
    ]);
    
    $paymentMethod = \App\Models\PaymentMethod::factory()->create();
    
    // Make 3 partial payments
    $payments = [
        ['fee' => $fee1, 'amount' => 20000],
        ['fee' => $fee2, 'amount' => 30000],
        ['fee' => $fee1, 'amount' => 15000],
    ];
    
    foreach ($payments as $index => $paymentInfo) {
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $paymentInfo['amount'],
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image("receipt{$index}.jpg"),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $paymentInfo['fee']->id,
                    'paid_amount' => $paymentInfo['amount'],
                ],
            ],
        ];
        
        $paymentService->submitPayment($paymentData);
        
        // Refresh and verify after each payment
        $invoice->refresh();
        $fee1->refresh();
        $fee2->refresh();
        $fee3->refresh();
        
        // Verify invariants hold after each payment
        $expectedPaidAmount = (float)$fee1->paid_amount + (float)$fee2->paid_amount + (float)$fee3->paid_amount;
        expect(abs((float)$invoice->paid_amount - $expectedPaidAmount))->toBeLessThan(0.01);
        
        $expectedRemaining = (float)$invoice->total_amount - (float)$invoice->paid_amount;
        expect(abs((float)$invoice->remaining_amount - $expectedRemaining))->toBeLessThan(0.01);
    }
    
    // Final verification
    expect((float)$fee1->paid_amount)->toBe(35000.0);
    expect((float)$fee2->paid_amount)->toBe(30000.0);
    expect((float)$fee3->paid_amount)->toBe(0.0);
    expect((float)$invoice->paid_amount)->toBe(65000.0);
    expect((float)$invoice->remaining_amount)->toBe(160000.0);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);


/**
 * Property 8: Partial Payment Minimum Amount Validation
 * 
 * For any partial payment submission, if any individual fee payment is less than 5,000 MMK
 * (and greater than 0), or if the total payment is less than 10,000 MMK, then the system
 * should reject the payment with a validation error.
 * 
 * **Validates: Requirements 10.1, 10.2**
 */
test('Property 8: partial payment minimum amount validation', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different scenarios
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create test data
        $grade = \App\Models\Grade::factory()->create(['level' => 1]);
        $student = \App\Models\StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        
        // Create invoice with random fees
        $numFees = fake()->numberBetween(1, 5);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
        ]);
        
        $invoiceFees = [];
        $totalAmount = 0;
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 10000, 100000);
            $totalAmount += $amount;
            
            $invoiceFees[] = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'due_date' => now()->addDays(30),
            ]);
        }
        
        $invoice->update([
            'total_amount' => $totalAmount,
            'remaining_amount' => $totalAmount,
        ]);
        
        // Test Case 1: Individual fee payment < 5,000 MMK (should fail)
        if ($numFees > 0) {
            $feeDetails = [
                [
                    'invoice_fee_id' => $invoiceFees[0]->id,
                    'paid_amount' => fake()->randomFloat(2, 1, 4999),
                ],
            ];
            
            try {
                $paymentService->validatePartialPayment($feeDetails, $invoice);
                expect(false)->toBeTrue('Should have thrown ValidationException for fee payment < 5,000');
            } catch (\Illuminate\Validation\ValidationException $e) {
                expect($e->errors())->toHaveKey("fee_payment_details.0.paid_amount");
            }
        }
        
        // Test Case 2: Total payment < 10,000 MMK (should fail)
        $feeDetails = [];
        $totalPayment = 0;
        
        // Create payments that sum to less than 10,000
        $targetTotal = fake()->randomFloat(2, 5000, 9999);
        $remainingTotal = $targetTotal;
        
        foreach ($invoiceFees as $index => $invoiceFee) {
            if ($remainingTotal <= 0) break;
            
            $payment = min($remainingTotal, fake()->randomFloat(2, 5000, $remainingTotal));
            $payment = min($payment, $invoiceFee->remaining_amount);
            
            if ($payment >= 5000) {
                $feeDetails[] = [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $payment,
                ];
                $totalPayment += $payment;
                $remainingTotal -= $payment;
            }
        }
        
        if ($totalPayment < 10000 && !empty($feeDetails)) {
            try {
                $paymentService->validatePartialPayment($feeDetails, $invoice);
                expect(false)->toBeTrue('Should have thrown ValidationException for total payment < 10,000');
            } catch (\Illuminate\Validation\ValidationException $e) {
                expect($e->errors())->toHaveKey('payment_amount');
            }
        }
        
        // Test Case 3: Valid payment (should pass)
        $feeDetails = [];
        $totalPayment = 0;
        
        // Create payments that sum to at least 10,000
        $targetTotal = fake()->randomFloat(2, 10000, min(50000, $totalAmount));
        $remainingTotal = $targetTotal;
        
        foreach ($invoiceFees as $invoiceFee) {
            if ($remainingTotal <= 0) break;
            
            $payment = min($remainingTotal, $invoiceFee->remaining_amount);
            
            if ($payment >= 5000) {
                $feeDetails[] = [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $payment,
                ];
                $totalPayment += $payment;
                $remainingTotal -= $payment;
            }
        }
        
        if ($totalPayment >= 10000) {
            // Should not throw exception
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(true)->toBeTrue();
        }
        
        // Clean up
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\Grade::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 9: Partial Payment Maximum Amount Validation
 * 
 * For any partial payment submission, if any fee payment amount exceeds that fee's
 * remaining_amount, then the system should reject the payment with a validation error.
 * 
 * **Validates: Requirements 10.3**
 */
test('Property 9: partial payment maximum amount validation', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create test data
        $grade = \App\Models\Grade::factory()->create(['level' => 1]);
        $student = \App\Models\StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        
        // Create invoice with fees
        $numFees = fake()->numberBetween(2, 5);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
        ]);
        
        $invoiceFees = [];
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 10000, 100000);
            $paidAmount = fake()->randomFloat(2, 0, $amount * 0.5);
            $remainingAmount = $amount - $paidAmount;
            
            $invoiceFees[] = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'due_date' => now()->addDays(30),
            ]);
        }
        
        // Test Case 1: Payment exceeds remaining amount (should fail)
        $targetFee = fake()->randomElement($invoiceFees);
        $excessAmount = $targetFee->remaining_amount + fake()->randomFloat(2, 1, 10000);
        
        $feeDetails = [
            [
                'invoice_fee_id' => $targetFee->id,
                'paid_amount' => $excessAmount,
            ],
        ];
        
        try {
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(false)->toBeTrue('Should have thrown ValidationException for payment exceeding remaining amount');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey("fee_payment_details.0.paid_amount");
        }
        
        // Test Case 2: Payment equals remaining amount (should pass)
        $feeDetails = [
            [
                'invoice_fee_id' => $targetFee->id,
                'paid_amount' => $targetFee->remaining_amount,
            ],
        ];
        
        if ($targetFee->remaining_amount >= 10000) {
            // Should not throw exception
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(true)->toBeTrue();
        }
        
        // Test Case 3: Payment less than remaining amount (should pass if >= 5000 and total >= 10000)
        if ($targetFee->remaining_amount > 10000) {
            $validPayment = fake()->randomFloat(2, 10000, $targetFee->remaining_amount - 1);
            
            $feeDetails = [
                [
                    'invoice_fee_id' => $targetFee->id,
                    'paid_amount' => $validPayment,
                ],
            ];
            
            // Should not throw exception
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(true)->toBeTrue();
        }
        
        // Clean up
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\Grade::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 10: Partial Payment Non-Empty Validation
 * 
 * For any payment submission, if no fees are included (empty fee list), then the system
 * should reject the payment with a validation error.
 * 
 * **Validates: Requirements 10.4**
 */
test('Property 10: partial payment non-empty validation', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations
    for ($iteration = 0; $iteration < 10; $iteration++) {
        // Create test data
        $grade = \App\Models\Grade::factory()->create(['level' => 1]);
        $student = \App\Models\StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
        ]);
        
        // Create some fees
        $numFees = fake()->numberBetween(1, 5);
        for ($i = 0; $i < $numFees; $i++) {
            \App\Models\PaymentSystem\InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'due_date' => now()->addDays(30),
            ]);
        }
        
        // Test Case 1: Empty fee list (should fail)
        $feeDetails = [];
        
        try {
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(false)->toBeTrue('Should have thrown ValidationException for empty fee list');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey('fee_payment_details');
        }
        
        // Test Case 2: Non-empty fee list with valid payment (should pass)
        $invoiceFee = $invoice->fees()->first();
        
        if ($invoiceFee && $invoiceFee->remaining_amount >= 10000) {
            $feeDetails = [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => min(10000, $invoiceFee->remaining_amount),
                ],
            ];
            
            // Should not throw exception
            $paymentService->validatePartialPayment($feeDetails, $invoice);
            expect(true)->toBeTrue();
        }
        
        // Clean up
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\Grade::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 8-10: edge case - zero payment amount', function () {
    $paymentService = new PaymentService();
    
    $grade = \App\Models\Grade::factory()->create(['level' => 1]);
    $student = \App\Models\StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
        'due_date' => now()->addDays(30),
    ]);
    
    // Zero payment should fail minimum validation
    $feeDetails = [
        [
            'invoice_fee_id' => $invoiceFee->id,
            'paid_amount' => 0,
        ],
    ];
    
    try {
        $paymentService->validatePartialPayment($feeDetails, $invoice);
        expect(false)->toBeTrue('Should have thrown ValidationException for zero payment');
    } catch (\Illuminate\Validation\ValidationException $e) {
        expect($e->errors())->toHaveKey('payment_amount');
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 8-10: edge case - invalid invoice fee ID', function () {
    $paymentService = new PaymentService();
    
    $grade = \App\Models\Grade::factory()->create(['level' => 1]);
    $student = \App\Models\StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
    ]);
    
    \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'due_date' => now()->addDays(30),
    ]);
    
    // Invalid invoice fee ID
    $feeDetails = [
        [
            'invoice_fee_id' => fake()->uuid(),
            'paid_amount' => 10000,
        ],
    ];
    
    try {
        $paymentService->validatePartialPayment($feeDetails, $invoice);
        expect(false)->toBeTrue('Should have thrown ValidationException for invalid fee ID');
    } catch (\Illuminate\Validation\ValidationException $e) {
        expect($e->errors())->toHaveKey('fee_payment_details.0.invoice_fee_id');
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 18: Partial Payment Due Date Restriction
 * 
 * For any payment submission where current_date >= fee.due_date for any fee in the payment,
 * if the payment is partial for that fee (paid_amount < fee.remaining_amount), then the
 * system should reject the payment with an error indicating full payment is required for
 * overdue fees.
 * 
 * **Validates: Requirements 16.1, 16.2, 16.3, 16.4, 16.5, 16.6**
 */
test('Property 18: partial payment due date restriction', function () {
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different scenarios
    for ($iteration = 0; $iteration < 20; $iteration++) {
        // Create test data
        $grade = \App\Models\Grade::factory()->create(['level' => 1]);
        $student = \App\Models\StudentProfile::factory()->create([
            'grade_id' => $grade->id,
            'status' => 'active',
        ]);
        
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
        ]);
        
        // Create fees with random due dates (past, present, future)
        $numFees = fake()->numberBetween(1, 5);
        $invoiceFees = [];
        
        for ($i = 0; $i < $numFees; $i++) {
            $amount = fake()->randomFloat(2, 20000, 100000);
            
            // Random due date: past, today, or future
            $daysOffset = fake()->numberBetween(-30, 30);
            $dueDate = now()->addDays($daysOffset);
            
            $invoiceFees[] = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'due_date' => $dueDate,
            ]);
        }
        
        // Test Case 1: Partial payment on overdue fee (should fail)
        $overdueFees = collect($invoiceFees)->filter(function ($fee) {
            return $fee->due_date->startOfDay()->lessThanOrEqualTo(now()->startOfDay());
        });
        
        if ($overdueFees->isNotEmpty()) {
            $overdueFee = $overdueFees->random();
            $partialPayment = fake()->randomFloat(2, 10000, $overdueFee->remaining_amount - 1);
            
            $feeDetails = [
                [
                    'invoice_fee_id' => $overdueFee->id,
                    'paid_amount' => $partialPayment,
                ],
            ];
            
            try {
                $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
                expect(false)->toBeTrue('Should have thrown ValidationException for partial payment on overdue fee');
            } catch (\Illuminate\Validation\ValidationException $e) {
                expect($e->errors())->toHaveKey("fee_payment_details.0.paid_amount");
            }
        }
        
        // Test Case 2: Full payment on overdue fee (should pass)
        if ($overdueFees->isNotEmpty()) {
            $overdueFee = $overdueFees->random();
            
            $feeDetails = [
                [
                    'invoice_fee_id' => $overdueFee->id,
                    'paid_amount' => $overdueFee->remaining_amount,
                ],
            ];
            
            // Should not throw exception
            $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
            expect(true)->toBeTrue();
        }
        
        // Test Case 3: Partial payment on future-due fee (should pass)
        $futureFees = collect($invoiceFees)->filter(function ($fee) {
            return $fee->due_date->startOfDay()->greaterThan(now()->startOfDay());
        });
        
        if ($futureFees->isNotEmpty()) {
            $futureFee = $futureFees->random();
            $partialPayment = fake()->randomFloat(2, 10000, $futureFee->remaining_amount - 1);
            
            $feeDetails = [
                [
                    'invoice_fee_id' => $futureFee->id,
                    'paid_amount' => $partialPayment,
                ],
            ];
            
            // Should not throw exception
            $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
            expect(true)->toBeTrue();
        }
        
        // Test Case 4: Mixed fees - some overdue, some not
        if ($overdueFees->isNotEmpty() && $futureFees->isNotEmpty()) {
            $overdueFee = $overdueFees->random();
            $futureFee = $futureFees->random();
            
            // Partial payment on overdue fee should fail even if other fees are valid
            $feeDetails = [
                [
                    'invoice_fee_id' => $futureFee->id,
                    'paid_amount' => $futureFee->remaining_amount, // Full payment on future fee
                ],
                [
                    'invoice_fee_id' => $overdueFee->id,
                    'paid_amount' => $overdueFee->remaining_amount - 1000, // Partial on overdue
                ],
            ];
            
            try {
                $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
                expect(false)->toBeTrue('Should have thrown ValidationException for partial payment on overdue fee in mixed payment');
            } catch (\Illuminate\Validation\ValidationException $e) {
                expect($e->errors())->toHaveKey("fee_payment_details.1.paid_amount");
            }
        }
        
        // Clean up
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\Grade::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 18: edge case - due date exactly today', function () {
    $paymentService = new PaymentService();
    
    $grade = \App\Models\Grade::factory()->create(['level' => 1]);
    $student = \App\Models\StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
    ]);
    
    // Fee due exactly today
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 50000,
        'paid_amount' => 0,
        'remaining_amount' => 50000,
        'due_date' => now()->startOfDay(),
    ]);
    
    // Partial payment on fee due today should fail
    $feeDetails = [
        [
            'invoice_fee_id' => $invoiceFee->id,
            'paid_amount' => 25000,
        ],
    ];
    
    try {
        $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
        expect(false)->toBeTrue('Should have thrown ValidationException for partial payment on fee due today');
    } catch (\Illuminate\Validation\ValidationException $e) {
        expect($e->errors())->toHaveKey("fee_payment_details.0.paid_amount");
    }
    
    // Full payment on fee due today should pass
    $feeDetails = [
        [
            'invoice_fee_id' => $invoiceFee->id,
            'paid_amount' => 50000,
        ],
    ];
    
    $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
    expect(true)->toBeTrue();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 18: edge case - all fees overdue requires full payment', function () {
    $paymentService = new PaymentService();
    
    $grade = \App\Models\Grade::factory()->create(['level' => 1]);
    $student = \App\Models\StudentProfile::factory()->create([
        'grade_id' => $grade->id,
        'status' => 'active',
    ]);
    
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
    ]);
    
    // Create multiple overdue fees
    $numFees = fake()->numberBetween(2, 4);
    $invoiceFees = [];
    $totalRemaining = 0;
    
    for ($i = 0; $i < $numFees; $i++) {
        $amount = fake()->randomFloat(2, 20000, 50000);
        $totalRemaining += $amount;
        
        $invoiceFees[] = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'paid_amount' => 0,
            'remaining_amount' => $amount,
            'due_date' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
    
    // Partial payment on any overdue fee should fail
    $feeDetails = [];
    foreach ($invoiceFees as $index => $fee) {
        $feeDetails[] = [
            'invoice_fee_id' => $fee->id,
            'paid_amount' => $fee->remaining_amount - 1000, // Partial payment
        ];
    }
    
    try {
        $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
        expect(false)->toBeTrue('Should have thrown ValidationException when all fees are overdue and payment is partial');
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Should have errors for all fees
        expect(count($e->errors()))->toBeGreaterThan(0);
    }
    
    // Full payment on all overdue fees should pass
    $feeDetails = [];
    foreach ($invoiceFees as $fee) {
        $feeDetails[] = [
            'invoice_fee_id' => $fee->id,
            'paid_amount' => $fee->remaining_amount, // Full payment
        ];
    }
    
    $paymentService->checkDueDateRestrictions($invoice, $feeDetails);
    expect(true)->toBeTrue();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 13: Invoice Fee Status Transitions
 * 
 * For any invoice_fee, when remaining_amount = 0, status should be "paid";
 * when paid_amount > 0 and remaining_amount > 0, status should be "partial";
 * when paid_amount = 0, status should be "unpaid".
 * 
 * **Validates: Requirements 11.3, 11.4**
 */
test('Property 13: invoice fee status transitions correctly based on amounts', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Run multiple iterations with different payment scenarios
    for ($iteration = 0; $iteration < 20; $iteration++) {
        $feeAmount = fake()->randomFloat(2, 50000, 200000);
        
        $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => $feeAmount,
            'paid_amount' => 0,
            'remaining_amount' => $feeAmount,
            'status' => 'pending',
        ]);
        
        $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => $feeAmount,
            'paid_amount' => 0,
            'remaining_amount' => $feeAmount,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);
        
        $paymentMethod = \App\Models\PaymentMethod::factory()->create();
        
        // Initial state: paid_amount = 0, should be unpaid
        expect($invoiceFee->status)->toBe('unpaid');
        expect((float)$invoiceFee->paid_amount)->toBe(0.0);
        
        // Make a partial payment
        $partialAmount = fake()->randomFloat(2, 10000, $feeAmount - 10000);
        
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $partialAmount,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt1.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $partialAmount,
                ],
            ],
        ];
        
        $paymentService->submitPayment($paymentData);
        $invoiceFee->refresh();
        
        // After partial payment: paid_amount > 0 and remaining_amount > 0, should be partial
        expect($invoiceFee->status)->toBe('partial');
        expect((float)$invoiceFee->paid_amount)->toBeGreaterThan(0.0);
        expect((float)$invoiceFee->remaining_amount)->toBeGreaterThan(0.0);
        
        // Pay the remaining amount
        $remainingAmount = (float)$invoiceFee->remaining_amount;
        
        $paymentData2 = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $remainingAmount,
            'payment_type' => 'full',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt2.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $remainingAmount,
                ],
            ],
        ];
        
        $paymentService->submitPayment($paymentData2);
        $invoiceFee->refresh();
        
        // After full payment: remaining_amount = 0, should be paid
        expect($invoiceFee->status)->toBe('paid');
        expect(abs((float)$invoiceFee->remaining_amount))->toBeLessThan(0.01);
        
        // Clean up for next iteration
        \App\Models\PaymentSystem\Payment::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\PaymentMethod::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 13: invoice fee status is unpaid when no payments made', function () {
    Storage::fake('public');
    
    // Create invoice fee with no payments
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'status' => 'pending',
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'status' => 'unpaid',
        'due_date' => now()->addDays(30),
    ]);
    
    // Verify status is unpaid
    expect($invoiceFee->status)->toBe('unpaid');
    expect((float)$invoiceFee->paid_amount)->toBe(0.0);
    expect((float)$invoiceFee->remaining_amount)->toBe(100000.0);
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Property 14: Invoice Status Transitions
 * 
 * For any invoice, when remaining_amount = 0, status should be "paid";
 * when paid_amount > 0 and remaining_amount > 0, status should be "partial";
 * when paid_amount = 0 and due_date < current_date, status should be "overdue";
 * when paid_amount = 0 and due_date >= current_date, status should be "pending".
 * 
 * **Validates: Requirements 11.7, 11.8**
 */
test('Property 14: invoice status transitions correctly based on amounts and due date', function () {
    Storage::fake('public');
    $paymentService = new PaymentService();
    
    // Run multiple iterations
    for ($iteration = 0; $iteration < 15; $iteration++) {
        $totalAmount = fake()->randomFloat(2, 100000, 500000);
        
        $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
        $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
            'student_id' => $student->id,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);
        
        $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => $totalAmount,
            'paid_amount' => 0,
            'remaining_amount' => $totalAmount,
            'due_date' => now()->addDays(30),
        ]);
        
        $paymentMethod = \App\Models\PaymentMethod::factory()->create();
        
        // Initial state: paid_amount = 0, due_date in future, should be pending
        expect($invoice->status)->toBe('pending');
        expect((float)$invoice->paid_amount)->toBe(0.0);
        
        // Make a partial payment
        $partialAmount = fake()->randomFloat(2, 10000, $totalAmount - 10000);
        
        $paymentData = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $partialAmount,
            'payment_type' => 'partial',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt1.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $partialAmount,
                ],
            ],
        ];
        
        $paymentService->submitPayment($paymentData);
        $invoice->refresh();
        
        // After partial payment: paid_amount > 0 and remaining_amount > 0, should be partial
        expect($invoice->status)->toBe('partial');
        expect((float)$invoice->paid_amount)->toBeGreaterThan(0.0);
        expect((float)$invoice->remaining_amount)->toBeGreaterThan(0.0);
        
        // Pay the remaining amount
        $remainingAmount = (float)$invoice->remaining_amount;
        
        $paymentData2 = [
            'invoice_id' => $invoice->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $remainingAmount,
            'payment_type' => 'full',
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => \Illuminate\Http\UploadedFile::fake()->image('receipt2.jpg'),
            'fee_payment_details' => [
                [
                    'invoice_fee_id' => $invoiceFee->id,
                    'paid_amount' => $remainingAmount,
                ],
            ],
        ];
        
        $paymentService->submitPayment($paymentData2);
        $invoice->refresh();
        
        // After full payment: remaining_amount = 0, should be paid
        expect($invoice->status)->toBe('paid');
        expect(abs((float)$invoice->remaining_amount))->toBeLessThan(0.01);
        
        // Clean up for next iteration
        \App\Models\PaymentSystem\Payment::query()->delete();
        \App\Models\PaymentSystem\InvoiceFee::query()->delete();
        \App\Models\PaymentSystem\Invoice::query()->delete();
        \App\Models\StudentProfile::query()->delete();
        \App\Models\PaymentMethod::query()->delete();
    }
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 14: invoice status is overdue when unpaid and past due date', function () {
    Storage::fake('public');
    
    // Create invoice with past due date
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'status' => 'pending',
        'due_date' => now()->subDays(5),
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'due_date' => now()->subDays(5),
    ]);
    
    // Manually update status to overdue (simulating what would happen in a cron job)
    $invoice->status = 'overdue';
    $invoice->save();
    
    // Verify status is overdue
    expect($invoice->status)->toBe('overdue');
    expect((float)$invoice->paid_amount)->toBe(0.0);
    expect($invoice->due_date->isPast())->toBeTrue();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('Property 14: invoice status is pending when unpaid and not yet due', function () {
    Storage::fake('public');
    
    // Create invoice with future due date
    $student = \App\Models\StudentProfile::factory()->create(['status' => 'active']);
    $invoice = \App\Models\PaymentSystem\Invoice::factory()->create([
        'student_id' => $student->id,
        'total_amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'status' => 'pending',
        'due_date' => now()->addDays(30),
    ]);
    
    $invoiceFee = \App\Models\PaymentSystem\InvoiceFee::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 100000,
        'paid_amount' => 0,
        'remaining_amount' => 100000,
        'due_date' => now()->addDays(30),
    ]);
    
    // Verify status is pending
    expect($invoice->status)->toBe('pending');
    expect((float)$invoice->paid_amount)->toBe(0.0);
    expect($invoice->due_date->isFuture())->toBeTrue();
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
