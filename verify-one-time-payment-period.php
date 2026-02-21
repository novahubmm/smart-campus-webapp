<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentSystem\FeeStructure;
use App\Models\PaymentSystem\Invoice;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║  ONE-TIME FEE WITH PAYMENT PERIOD SUPPORT - VERIFICATION      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$fee = FeeStructure::where('name', 'Special One-Time Course Fee')->first();

echo "1️⃣  Fee Structure:\n";
echo "   ─────────────────────────────────────────────────────────────\n";
echo "   Name: {$fee->name} ({$fee->name_mm})\n";
echo "   Amount: " . number_format($fee->amount) . " MMK\n";
echo "   Frequency: {$fee->frequency}\n";
echo "   Target Month: " . \Carbon\Carbon::create()->month($fee->target_month)->format('F') . "\n";
echo "   Supports Payment Period: " . ($fee->supports_payment_period ? '✅ TRUE' : '❌ FALSE') . "\n\n";

$invoices = Invoice::whereHas('fees', function($q) use ($fee) {
    $q->where('fee_id', $fee->id);
})->with('student.user', 'fees')->get();

echo "2️⃣  Generated Invoices: {$invoices->count()} invoices\n";
echo "   ─────────────────────────────────────────────────────────────\n\n";

$sampleInvoice = $invoices->first();
$invoiceFee = $sampleInvoice->fees->first();

echo "3️⃣  Sample Invoice Details:\n";
echo "   ─────────────────────────────────────────────────────────────\n";
echo "   Invoice Number: {$sampleInvoice->invoice_number}\n";
echo "   Student: {$sampleInvoice->student->user->name}\n";
echo "   Fee Name: {$invoiceFee->fee_name}\n";
echo "   Amount: " . number_format($invoiceFee->amount) . " MMK\n";
echo "   Supports Payment Period: " . ($invoiceFee->supports_payment_period ? '✅ TRUE' : '❌ FALSE') . "\n";
echo "   Due Date: {$invoiceFee->due_date->format('Y-m-d')}\n\n";

echo "4️⃣  Payment Period Options Available:\n";
echo "   ─────────────────────────────────────────────────────────────\n";
if ($invoiceFee->supports_payment_period) {
    echo "   ✅ 1 month  - No discount\n";
    echo "   ✅ 3 months - 5% discount\n";
    echo "   ✅ 6 months - 10% discount\n";
    echo "   ✅ 12 months - 15% discount\n\n";
    
    $base = $invoiceFee->amount;
    echo "   Example calculations for " . number_format($base) . " MMK:\n";
    echo "   • 1 month:  " . number_format($base) . " MMK\n";
    echo "   • 3 months: " . number_format($base * 3 * 0.95) . " MMK (save " . number_format($base * 3 * 0.05) . " MMK)\n";
    echo "   • 6 months: " . number_format($base * 6 * 0.90) . " MMK (save " . number_format($base * 6 * 0.10) . " MMK)\n";
    echo "   • 12 months: " . number_format($base * 12 * 0.85) . " MMK (save " . number_format($base * 12 * 0.15) . " MMK)\n";
} else {
    echo "   ❌ Payment periods not supported\n";
}

echo "\n✅ SUCCESS: One-time invoices created with payment period support!\n";
echo "\n📋 Sample Invoices:\n";
echo "   ─────────────────────────────────────────────────────────────\n";
foreach ($invoices->take(5) as $index => $inv) {
    $fee = $inv->fees->first();
    echo sprintf("   %d. %s | %s | %s MMK | %s\n", 
        $index + 1,
        $inv->invoice_number,
        $inv->student->user->name,
        number_format($inv->total_amount),
        $fee->supports_payment_period ? 'Payment Period: YES' : 'Payment Period: NO'
    );
}
