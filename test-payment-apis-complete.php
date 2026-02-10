<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\Invoice;
use App\Models\PaymentMethod;

echo "═══════════════════════════════════════════════════════════════\n";
echo "🧪 PAYMENT API TEST CASES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Find guardian
$guardian = User::where('email', 'konyeinchan@smartcampusedu.com')->first();
if (!$guardian) {
    echo "❌ Guardian not found\n";
    exit(1);
}

$guardianProfile = GuardianProfile::where('user_id', $guardian->id)->first();
$students = $guardianProfile->students()->with('user', 'grade')->get();
$student = $students->first();

// Get invoices
$invoices = Invoice::where('student_id', $student->id)
    ->where('month', '2026-02')
    ->where('status', 'unpaid')
    ->with('feeStructure.feeType')
    ->get();

// Get payment methods
$paymentMethods = PaymentMethod::where('is_active', true)->get();
$kbzBank = $paymentMethods->where('name', 'KBZ Bank')->first();

// Create token
$token = $guardian->createToken('mobile-app-test')->plainTextToken;

$baseUrl = "http://192.168.100.114:8088/api/v1";

echo "📋 Test Data:\n";
echo "  Guardian: {$guardian->name}\n";
echo "  Student: {$student->user->name} (ID: {$student->id})\n";
echo "  Unpaid Invoices: {$invoices->count()}\n";
echo "  Payment Methods: {$paymentMethods->count()}\n";
echo "  Token: {$token}\n\n";

// Display invoices
echo "💰 Available Invoices:\n";
foreach ($invoices as $invoice) {
    $feeType = $invoice->feeStructure && $invoice->feeStructure->feeType 
        ? $invoice->feeStructure->feeType->name 
        : 'Unknown';
    echo "  - {$invoice->id}: {$feeType} - " . number_format($invoice->total_amount, 0) . " MMK\n";
}
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 1: Get Fee Structure\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: GET {$baseUrl}/guardian/students/{$student->id}/fees/structure\n";
echo "Expected: 200 OK with fee structure details\n\n";

$cmd = "curl -s -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/structure' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 2: Get Payment Options\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: GET {$baseUrl}/guardian/payment-options\n";
echo "Expected: 200 OK with payment options (1, 2, 3, 6, 9, 12 months)\n\n";

$cmd = "curl -s -X GET '{$baseUrl}/guardian/payment-options' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 3: Get Payment Methods\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: GET {$baseUrl}/guardian/payment-methods\n";
echo "Expected: 200 OK with list of payment methods (banks and mobile wallets)\n\n";

$cmd = "curl -s -X GET '{$baseUrl}/guardian/payment-methods' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 4: Submit Payment - Single Invoice\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: POST {$baseUrl}/guardian/students/{$student->id}/fees/payments\n";
echo "Expected: 201 Created with payment confirmation\n\n";

$singleInvoice = $invoices->first();
$paymentData = [
    'invoice_ids' => [$singleInvoice->id],
    'payment_method_id' => $kbzBank->id,
    'payment_amount' => (float)$singleInvoice->total_amount,
    'payment_months' => 1,
    'payment_date' => date('Y-m-d'),
    'receipt_image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=',
    'notes' => 'Test payment via KBZ Bank'
];

$cmd = "curl -s -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json' \\\n";
$cmd .= "  -H 'Content-Type: application/json' \\\n";
$cmd .= "  -d '" . json_encode($paymentData, JSON_PRETTY_PRINT) . "'";
echo "Command:\n{$cmd}\n\n";

echo "Request Body:\n";
echo json_encode($paymentData, JSON_PRETTY_PRINT) . "\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 5: Submit Payment - Multiple Invoices\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: POST {$baseUrl}/guardian/students/{$student->id}/fees/payments\n";
echo "Expected: 201 Created with payment confirmation for multiple invoices\n\n";

$multipleInvoices = $invoices->take(3);
$totalAmount = $multipleInvoices->sum('total_amount');
$paymentData = [
    'invoice_ids' => $multipleInvoices->pluck('id')->toArray(),
    'payment_method_id' => $kbzBank->id,
    'payment_amount' => (float)$totalAmount,
    'payment_months' => 1,
    'payment_date' => date('Y-m-d'),
    'receipt_image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=',
    'notes' => 'Test payment for multiple fees'
];

$cmd = "curl -s -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json' \\\n";
$cmd .= "  -H 'Content-Type: application/json' \\\n";
$cmd .= "  -d '" . json_encode($paymentData, JSON_PRETTY_PRINT) . "'";
echo "Command:\n{$cmd}\n\n";

echo "Request Body:\n";
echo json_encode($paymentData, JSON_PRETTY_PRINT) . "\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 6: Submit Payment - With Discount (3 months)\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: POST {$baseUrl}/guardian/students/{$student->id}/fees/payments\n";
echo "Expected: 201 Created with 5% discount applied\n\n";

$monthlyTotal = $invoices->where('feeStructure.frequency', 'monthly')->sum('total_amount');
$discountedAmount = $monthlyTotal * 3 * 0.95; // 3 months with 5% discount

$paymentData = [
    'invoice_ids' => $invoices->where('feeStructure.frequency', 'monthly')->pluck('id')->toArray(),
    'payment_method_id' => $kbzBank->id,
    'payment_amount' => (float)$discountedAmount,
    'payment_months' => 3,
    'payment_date' => date('Y-m-d'),
    'receipt_image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=',
    'notes' => 'Test payment with 3-month discount (5%)'
];

$cmd = "curl -s -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json' \\\n";
$cmd .= "  -H 'Content-Type: application/json' \\\n";
$cmd .= "  -d '" . json_encode($paymentData, JSON_PRETTY_PRINT) . "'";
echo "Command:\n{$cmd}\n\n";

echo "Request Body:\n";
echo json_encode($paymentData, JSON_PRETTY_PRINT) . "\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 7: Get Payment History\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: GET {$baseUrl}/guardian/students/{$student->id}/fees/payment-history\n";
echo "Expected: 200 OK with list of payments\n\n";

$cmd = "curl -s -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/payment-history' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST CASE 8: Get Payment History with Pagination\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Endpoint: GET {$baseUrl}/guardian/students/{$student->id}/fees/payment-history?page=1&per_page=5\n";
echo "Expected: 200 OK with paginated results\n\n";

$cmd = "curl -s -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/payment-history?page=1&per_page=5' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

// Error test cases
echo "═══════════════════════════════════════════════════════════════\n";
echo "ERROR TEST CASES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "TEST CASE 9: Invalid Student ID\n";
echo "Expected: 404 Not Found\n\n";
$cmd = "curl -s -X GET '{$baseUrl}/guardian/students/invalid-uuid/fees/structure' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "TEST CASE 10: Invalid Invoice IDs\n";
echo "Expected: 400 Bad Request with error message\n\n";
$paymentData = [
    'invoice_ids' => ['invalid-uuid-1', 'invalid-uuid-2'],
    'payment_method_id' => $kbzBank->id,
    'payment_amount' => 50000,
    'payment_months' => 1,
    'payment_date' => date('Y-m-d'),
    'receipt_image' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=',
    'notes' => 'Test with invalid invoice IDs'
];

$cmd = "curl -s -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json' \\\n";
$cmd .= "  -H 'Content-Type: application/json' \\\n";
$cmd .= "  -d '" . json_encode($paymentData) . "'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "TEST CASE 11: Missing Required Fields\n";
echo "Expected: 422 Unprocessable Entity with validation errors\n\n";
$paymentData = [
    'invoice_ids' => [$singleInvoice->id],
    // Missing payment_method_id, payment_amount, etc.
];

$cmd = "curl -s -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd .= "  -H 'Accept: application/json' \\\n";
$cmd .= "  -H 'Content-Type: application/json' \\\n";
$cmd .= "  -d '" . json_encode($paymentData) . "'";
echo "Command:\n{$cmd}\n\n";

echo "─────────────────────────────────────────────────────────────\n\n";

echo "TEST CASE 12: Unauthorized Access (No Token)\n";
echo "Expected: 401 Unauthorized\n\n";
$cmd = "curl -s -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/structure' \\\n";
$cmd .= "  -H 'Accept: application/json'";
echo "Command:\n{$cmd}\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

// Save executable test script
$scriptContent = "#!/bin/bash\n\n";
$scriptContent .= "# Payment API Complete Test Suite\n";
$scriptContent .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";

$scriptContent .= "TOKEN=\"{$token}\"\n";
$scriptContent .= "BASE_URL=\"{$baseUrl}\"\n";
$scriptContent .= "STUDENT_ID=\"{$student->id}\"\n";
$scriptContent .= "PAYMENT_METHOD_ID=\"{$kbzBank->id}\"\n";
$scriptContent .= "INVOICE_ID=\"{$singleInvoice->id}\"\n\n";

$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$scriptContent .= "echo \"🧪 Running Payment API Test Suite\"\n";
$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"TEST 1: Get Fee Structure\"\n";
$scriptContent .= "curl -s -X GET \"\${BASE_URL}/guardian/students/\${STUDENT_ID}/fees/structure\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"TEST 2: Get Payment Options\"\n";
$scriptContent .= "curl -s -X GET \"\${BASE_URL}/guardian/payment-options\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"TEST 3: Get Payment Methods\"\n";
$scriptContent .= "curl -s -X GET \"\${BASE_URL}/guardian/payment-methods\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"TEST 4: Submit Single Payment\"\n";
$scriptContent .= "curl -s -X POST \"\${BASE_URL}/guardian/students/\${STUDENT_ID}/fees/payments\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" \\\n";
$scriptContent .= "  -H \"Content-Type: application/json\" \\\n";
$scriptContent .= "  -d '{\n";
$scriptContent .= "    \"invoice_ids\": [\"'\${INVOICE_ID}'\"],\n";
$scriptContent .= "    \"payment_method_id\": \"'\${PAYMENT_METHOD_ID}'\",\n";
$scriptContent .= "    \"payment_amount\": " . $singleInvoice->total_amount . ",\n";
$scriptContent .= "    \"payment_months\": 1,\n";
$scriptContent .= "    \"payment_date\": \"" . date('Y-m-d') . "\",\n";
$scriptContent .= "    \"receipt_image\": \"data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA8A/9k=\",\n";
$scriptContent .= "    \"notes\": \"Test payment\"\n";
$scriptContent .= "  }' | jq .\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"TEST 5: Get Payment History\"\n";
$scriptContent .= "curl -s -X GET \"\${BASE_URL}/guardian/students/\${STUDENT_ID}/fees/payment-history\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n";
$scriptContent .= "echo \"\"\n\n";

$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$scriptContent .= "echo \"✅ Test suite completed!\"\n";
$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";

file_put_contents(__DIR__ . '/run-payment-tests.sh', $scriptContent);
chmod(__DIR__ . '/run-payment-tests.sh', 0755);

echo "✅ Test suite script saved: run-payment-tests.sh\n";
echo "   Run with: ./run-payment-tests.sh\n\n";

echo "📝 Summary:\n";
echo "   - 12 test cases created\n";
echo "   - 7 success scenarios\n";
echo "   - 5 error scenarios\n";
echo "   - Token: {$token}\n";
echo "   - Student ID: {$student->id}\n";
