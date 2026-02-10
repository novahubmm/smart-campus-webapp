<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentPromotion;
use App\Repositories\Guardian\GuardianPaymentRepository;

echo "=== Testing Promotion Update ===\n\n";

// Update promotions with sample discounts
$updates = [
    3 => 2.0,   // 3 months: 2% discount
    6 => 5.0,   // 6 months: 5% discount
    12 => 10.0, // 12 months: 10% discount
];

foreach ($updates as $months => $discount) {
    $promotion = PaymentPromotion::where('months', $months)->first();
    if ($promotion) {
        $promotion->update(['discount_percent' => $discount]);
        echo "✓ Updated {$months} month(s) promotion to {$discount}% discount\n";
    }
}

echo "\n=== Testing API Response ===\n\n";

// Test payment options endpoint
$repository = new GuardianPaymentRepository();
$paymentOptions = $repository->getPaymentOptions();

echo "Payment Options:\n";
foreach ($paymentOptions['options'] as $option) {
    $badge = $option['badge'] ?? 'No discount';
    $default = $option['is_default'] ? ' (default)' : '';
    echo "  - {$option['label']}: {$option['discount_percent']}% discount - Badge: {$badge}{$default}\n";
}

echo "\n=== Test Complete ===\n";
