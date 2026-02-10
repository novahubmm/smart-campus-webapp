<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\StudentProfile;
use App\Models\Invoice;

echo "🔍 Finding Guardian and Student...\n\n";

// Find guardian user
$guardian = User::where('email', 'konyeinchan@smartcampusedu.com')->first();
if (!$guardian) {
    echo "❌ Guardian not found\n";
    exit(1);
}

echo "✓ Guardian: {$guardian->name}\n";
echo "  Email: {$guardian->email}\n";
echo "  User ID: {$guardian->id}\n\n";

// Find guardian profile
$guardianProfile = GuardianProfile::where('user_id', $guardian->id)->first();
if (!$guardianProfile) {
    echo "❌ Guardian profile not found\n";
    exit(1);
}

echo "✓ Guardian Profile ID: {$guardianProfile->id}\n\n";

// Find students
$students = $guardianProfile->students()->with('user', 'grade')->get();
if ($students->isEmpty()) {
    echo "❌ No students found for this guardian\n";
    exit(1);
}

echo "✓ Students ({$students->count()}):\n";
foreach ($students as $student) {
    echo "  - {$student->user->name} (Grade: {$student->grade->name}, ID: {$student->student_id})\n";
}
echo "\n";

// Use first student for testing
$student = $students->first();
echo "📝 Using student for testing: {$student->user->name}\n";
echo "  - Student UUID: {$student->id}\n";
echo "  - Student ID: {$student->student_id}\n";
echo "  - Grade: {$student->grade->name}\n\n";

// Get student's invoices
$invoices = Invoice::where('student_id', $student->id)
    ->where('month', '2026-02')
    ->where('status', 'unpaid')
    ->with('feeStructure.feeType')
    ->get();

echo "💰 Unpaid Invoices for February 2026: {$invoices->count()}\n";
$totalAmount = 0;
$invoiceIds = [];
foreach ($invoices as $invoice) {
    $feeType = $invoice->feeStructure && $invoice->feeStructure->feeType 
        ? $invoice->feeStructure->feeType->name 
        : 'Unknown';
    echo "  - {$feeType}: " . number_format($invoice->total_amount, 0) . " MMK (ID: {$invoice->id})\n";
    $totalAmount += $invoice->total_amount;
    $invoiceIds[] = $invoice->id;
}
echo "  Total: " . number_format($totalAmount, 0) . " MMK\n\n";

// Create token
$token = $guardian->createToken('mobile-app')->plainTextToken;

echo "🔑 Authentication Token:\n";
echo "{$token}\n\n";

$baseUrl = "http://192.168.100.114:8088/api/v1";

echo "═══════════════════════════════════════════════════════════════\n";
echo "📋 API TEST COMMANDS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test 1: Get Fee Structure
echo "1️⃣  GET Fee Structure\n";
echo "─────────────────────────────────────────────────────────────\n";
$cmd1 = "curl -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/structure' \\\n";
$cmd1 .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd1 .= "  -H 'Accept: application/json'";
echo $cmd1 . "\n\n";

// Test 2: Get Payment Options
echo "2️⃣  GET Payment Options (with promotions)\n";
echo "─────────────────────────────────────────────────────────────\n";
$cmd2 = "curl -X GET '{$baseUrl}/guardian/payment-options' \\\n";
$cmd2 .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd2 .= "  -H 'Accept: application/json'";
echo $cmd2 . "\n\n";

// Test 3: Get Payment Methods
echo "3️⃣  GET Payment Methods\n";
echo "─────────────────────────────────────────────────────────────\n";
$cmd3 = "curl -X GET '{$baseUrl}/guardian/payment-methods' \\\n";
$cmd3 .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd3 .= "  -H 'Accept: application/json'";
echo $cmd3 . "\n\n";

// Test 4: POST Payment
echo "4️⃣  POST Payment (Submit Payment)\n";
echo "─────────────────────────────────────────────────────────────\n";
$invoiceIdsJson = json_encode(array_slice($invoiceIds, 0, 2), JSON_PRETTY_PRINT);
$cmd4 = "curl -X POST '{$baseUrl}/guardian/students/{$student->id}/fees/payments' \\\n";
$cmd4 .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd4 .= "  -H 'Accept: application/json' \\\n";
$cmd4 .= "  -H 'Content-Type: application/json' \\\n";
$cmd4 .= "  -d '{\n";
$cmd4 .= "    \"invoice_ids\": " . json_encode(array_slice($invoiceIds, 0, 2)) . ",\n";
$cmd4 .= "    \"payment_method_id\": \"<GET_FROM_PAYMENT_METHODS_API>\",\n";
$cmd4 .= "    \"payment_option\": \"monthly\",\n";
$cmd4 .= "    \"amount\": " . ($invoices->take(2)->sum('total_amount')) . ",\n";
$cmd4 .= "    \"payment_proof\": \"data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...\"\n";
$cmd4 .= "  }'";
echo $cmd4 . "\n\n";

// Test 5: Get Payment History
echo "5️⃣  GET Payment History\n";
echo "─────────────────────────────────────────────────────────────\n";
$cmd5 = "curl -X GET '{$baseUrl}/guardian/students/{$student->id}/fees/payment-history' \\\n";
$cmd5 .= "  -H 'Authorization: Bearer {$token}' \\\n";
$cmd5 .= "  -H 'Accept: application/json'";
echo $cmd5 . "\n\n";

echo "═══════════════════════════════════════════════════════════════\n\n";

// Save to shell script
$scriptContent = "#!/bin/bash\n\n";
$scriptContent .= "# Payment API Test Script\n";
$scriptContent .= "# Guardian: {$guardian->name}\n";
$scriptContent .= "# Student: {$student->user->name}\n";
$scriptContent .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";

$scriptContent .= "TOKEN=\"{$token}\"\n";
$scriptContent .= "BASE_URL=\"{$baseUrl}\"\n";
$scriptContent .= "STUDENT_ID=\"{$student->id}\"\n\n";

$scriptContent .= "echo \"Testing Payment APIs...\"\n";
$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n\n";

$scriptContent .= "echo \"1️⃣  Testing Fee Structure API...\"\n";
$scriptContent .= "curl -X GET \"\${BASE_URL}/guardian/students/\${STUDENT_ID}/fees/structure\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n\n";

$scriptContent .= "echo -e \"\\n2️⃣  Testing Payment Options API...\"\n";
$scriptContent .= "curl -X GET \"\${BASE_URL}/guardian/payment-options\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n\n";

$scriptContent .= "echo -e \"\\n3️⃣  Testing Payment Methods API...\"\n";
$scriptContent .= "curl -X GET \"\${BASE_URL}/guardian/payment-methods\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n\n";

$scriptContent .= "echo -e \"\\n5️⃣  Testing Payment History API...\"\n";
$scriptContent .= "curl -X GET \"\${BASE_URL}/guardian/students/\${STUDENT_ID}/fees/payment-history\" \\\n";
$scriptContent .= "  -H \"Authorization: Bearer \${TOKEN}\" \\\n";
$scriptContent .= "  -H \"Accept: application/json\" | jq .\n\n";

$scriptContent .= "echo \"═══════════════════════════════════════════════════════════════\"\n";
$scriptContent .= "echo \"✅ All tests completed!\"\n";

file_put_contents(__DIR__ . '/test-payment-apis.sh', $scriptContent);
chmod(__DIR__ . '/test-payment-apis.sh', 0755);

echo "✅ Shell script saved: test-payment-apis.sh\n";
echo "   Run with: ./test-payment-apis.sh\n\n";

echo "📝 Quick Reference:\n";
echo "   Student UUID: {$student->id}\n";
echo "   Token: {$token}\n";
