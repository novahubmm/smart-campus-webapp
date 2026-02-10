#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\GuardianProfile;
use App\Models\Invoice;
use App\Models\User;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           Guardian Fees Debug Script                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Find guardian by phone
$phone = $argv[1] ?? 'konyeinchan';

$user = User::where('phone', $phone)
    ->orWhere('name', 'like', "%{$phone}%")
    ->first();

if (!$user) {
    echo "❌ User not found with phone/name: {$phone}\n";
    exit(1);
}

echo "👤 User Found:\n";
echo "   Name: {$user->name}\n";
echo "   Phone: {$user->phone}\n";
echo "   ID: {$user->id}\n";
echo "\n";

$guardian = $user->guardianProfile;

if (!$guardian) {
    echo "❌ No guardian profile found for this user\n";
    exit(1);
}

echo "👨‍👩‍👧‍👦 Guardian Profile:\n";
echo "   ID: {$guardian->id}\n";
echo "\n";

// Get all students
$students = $guardian->students()->with(['user', 'grade', 'classModel'])->get();

echo "📚 Students ({$students->count()}):\n";
echo "─────────────────────────────────────────────────────────────\n";

foreach ($students as $index => $student) {
    echo "\n" . ($index + 1) . ". {$student->user->name}\n";
    echo "   Student ID: {$student->id}\n";
    echo "   Grade: {$student->grade?->name}\n";
    echo "   Grade ID: {$student->grade_id}\n";
    echo "   Class: {$student->classModel?->name}\n";
    echo "   Status: {$student->status}\n";
    
    // Check invoices for this student
    $invoices = Invoice::where('student_id', $student->id)
        ->whereYear('invoice_date', 2026)
        ->whereMonth('invoice_date', 2)
        ->with('items.feeType')
        ->get();
    
    echo "   Invoices: {$invoices->count()}\n";
    
    if ($invoices->isEmpty()) {
        echo "   ⚠️  NO INVOICES FOUND!\n";
        
        // Check why no invoice was created
        echo "   \n   Checking fee structures for this student...\n";
        
        $feeStructures = \App\Models\FeeStructure::where('frequency', 'monthly')
            ->where('status', true)
            ->where('grade_id', $student->grade_id)
            ->with('feeType')
            ->get();
        
        echo "   Fee Structures Found: {$feeStructures->count()}\n";
        
        if ($feeStructures->isEmpty()) {
            echo "   ❌ No fee structures found for grade: {$student->grade?->name} (ID: {$student->grade_id})\n";
        } else {
            foreach ($feeStructures as $fs) {
                echo "   - {$fs->feeType->name}: {$fs->amount} MMK\n";
            }
        }
    } else {
        foreach ($invoices as $invoice) {
            echo "   \n   Invoice: {$invoice->invoice_number}\n";
            echo "   Amount: {$invoice->total_amount} MMK\n";
            echo "   Status: {$invoice->status}\n";
            echo "   Items:\n";
            foreach ($invoice->items as $item) {
                echo "     - {$item->feeType->name}: {$item->amount} MMK\n";
            }
        }
    }
    
    echo "\n";
}

echo "─────────────────────────────────────────────────────────────\n";
echo "\n";

// Summary
$totalInvoices = Invoice::whereIn('student_id', $students->pluck('id'))
    ->whereYear('invoice_date', 2026)
    ->whereMonth('invoice_date', 2)
    ->count();

echo "📊 Summary:\n";
echo "   Total Students: {$students->count()}\n";
echo "   Total Invoices: {$totalInvoices}\n";
echo "   Missing Invoices: " . ($students->count() - $totalInvoices) . "\n";
echo "\n";

if ($totalInvoices < $students->count()) {
    echo "💡 To generate missing fees, run:\n";
    echo "   php artisan fees:generate-monthly --force\n";
    echo "\n";
}
