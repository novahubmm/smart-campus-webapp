<?php

/**
 * Regenerate Monthly Invoices Script
 * 
 * This script will:
 * 1. Delete all invoices for the current month (February 2026)
 * 2. Regenerate invoices for all active students with ALL their fee structures
 * 
 * Usage: php regenerate-monthly-invoices.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\FeeStructure;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

echo "🔄 Monthly Invoice Regeneration Script\n";
echo "=====================================\n\n";

// Get current month
$currentMonth = now()->format('Y-m');
$currentMonthName = now()->format('F Y');
$academicYear = now()->format('Y');

echo "📅 Target Month: {$currentMonthName} ({$currentMonth})\n";
echo "📚 Academic Year: {$academicYear}\n\n";

// Step 1: Check current invoice count
$currentInvoiceCount = Invoice::where('month', $currentMonth)->count();
echo "📊 Current invoices for this month: {$currentInvoiceCount}\n\n";

// Step 2: Get active students count
$activeStudents = StudentProfile::where('status', 'active')->count();
echo "👥 Active students: {$activeStudents}\n\n";

// Step 3: Get fee structures
$monthlyFees = FeeStructure::where('frequency', 'monthly')
    ->where('status', true)
    ->with('feeType', 'grade')
    ->get();

$oneTimeFees = FeeStructure::whereIn('frequency', ['one-time', 'one_time'])
    ->where('status', true)
    ->with('feeType', 'grade')
    ->get();

echo "💰 Fee Structures:\n";
echo "   - Monthly fees: {$monthlyFees->count()}\n";
echo "   - One-time fees: {$oneTimeFees->count()}\n";
echo "   - Total fee structures: " . ($monthlyFees->count() + $oneTimeFees->count()) . "\n\n";

// Display fee structure details
if ($monthlyFees->isNotEmpty()) {
    echo "📋 Monthly Fee Structures:\n";
    foreach ($monthlyFees as $fee) {
        $gradeName = $fee->grade ? $fee->grade->name : 'All Grades';
        echo "   - {$fee->feeType->name} ({$gradeName}): " . number_format($fee->amount, 0) . " MMK\n";
    }
    echo "\n";
}

if ($oneTimeFees->isNotEmpty()) {
    echo "📋 One-Time Fee Structures:\n";
    foreach ($oneTimeFees as $fee) {
        $gradeName = $fee->grade ? $fee->grade->name : 'All Grades';
        echo "   - {$fee->feeType->name} ({$gradeName}): " . number_format($fee->amount, 0) . " MMK\n";
    }
    echo "\n";
}

// Calculate expected invoices
$expectedInvoices = 0;
$studentsByGrade = StudentProfile::where('status', 'active')
    ->select('grade_id', DB::raw('count(*) as count'))
    ->groupBy('grade_id')
    ->get()
    ->pluck('count', 'grade_id');

foreach ($studentsByGrade as $gradeId => $studentCount) {
    $gradeMonthlyFees = $monthlyFees->where('grade_id', $gradeId)->count();
    $gradeOneTimeFees = $oneTimeFees->where('grade_id', $gradeId)->count();
    $totalFeesForGrade = $gradeMonthlyFees + $gradeOneTimeFees;
    $expectedInvoices += $studentCount * $totalFeesForGrade;
}

echo "📈 Expected invoices: {$expectedInvoices}\n";
echo "   ({$activeStudents} students × multiple fee structures per student)\n\n";

// Auto-confirm for regeneration
echo "⚠️  Proceeding to DELETE all {$currentInvoiceCount} invoices for {$currentMonthName}\n";
echo "   and regenerate {$expectedInvoices} new invoices.\n\n";
echo "🚀 Starting regeneration process...\n\n";

try {
    DB::beginTransaction();
    
    // Step 4: Delete existing invoices for this month
    echo "🗑️  Deleting existing invoices...\n";
    $deletedCount = Invoice::where('month', $currentMonth)->delete();
    echo "   ✓ Deleted {$deletedCount} invoices\n\n";
    
    // Step 5: Generate new invoices
    echo "📝 Generating new invoices...\n";
    
    $students = StudentProfile::where('status', 'active')
        ->with(['grade', 'user'])
        ->get();
    
    $stats = [
        'total_students' => $students->count(),
        'invoices_created' => 0,
        'invoices_skipped' => 0,
        'errors' => [],
    ];
    
    // Get next invoice number
    $todayPrefix = 'INV' . date('Ymd');
    $lastInvoice = Invoice::where('invoice_number', 'like', $todayPrefix . '%')
        ->orderBy('invoice_number', 'desc')
        ->first();
    
    $counter = 1;
    if ($lastInvoice && preg_match('/INV\d{8}-(\d{4})/', $lastInvoice->invoice_number, $matches)) {
        $counter = intval($matches[1]) + 1;
    }
    
    $now = now();
    $dueDate = $now->copy()->addDays(30);
    
    // Get first user ID (admin/system user)
    $userId = \App\Models\User::first()->id;
    $datePrefix = date('Ymd');
    
    foreach ($students as $student) {
        try {
            // Get all fee structures for this student's grade
            $applicableFees = FeeStructure::where('status', true)
                ->where(function($query) use ($student) {
                    $query->where('grade_id', $student->grade_id)
                          ->orWhereNull('grade_id');
                })
                ->get();
            
            if ($applicableFees->isEmpty()) {
                $stats['invoices_skipped']++;
                continue;
            }
            
            // Create one invoice per fee structure
            foreach ($applicableFees as $feeStructure) {
                $invoice = Invoice::create([
                    'invoice_number' => sprintf('INV%s-%04d', $datePrefix, $counter),
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'invoice_date' => $now,
                    'due_date' => $dueDate,
                    'month' => $currentMonth,
                    'academic_year' => $academicYear,
                    'subtotal' => $feeStructure->amount,
                    'discount' => 0,
                    'total_amount' => $feeStructure->amount,
                    'paid_amount' => 0,
                    'balance' => $feeStructure->amount,
                    'status' => 'unpaid',
                    'created_by' => $userId,
                ]);
                
                $stats['invoices_created']++;
                $counter++;
            }
            
        } catch (\Exception $e) {
            $stats['errors'][] = [
                'student_id' => $student->id,
                'student_name' => $student->user->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ];
        }
    }
    
    DB::commit();
    
    echo "\n✅ Invoice generation completed!\n\n";
    
    echo "📊 Summary:\n";
    echo "   - Total students: {$stats['total_students']}\n";
    echo "   - Invoices created: {$stats['invoices_created']}\n";
    echo "   - Students skipped: {$stats['invoices_skipped']}\n";
    echo "   - Errors: " . count($stats['errors']) . "\n\n";
    
    if (!empty($stats['errors'])) {
        echo "❌ Errors encountered:\n";
        foreach ($stats['errors'] as $error) {
            echo "   - {$error['student_name']} (ID: {$error['student_id']}): {$error['error']}\n";
        }
        echo "\n";
    }
    
    // Verify final count
    $finalCount = Invoice::where('month', $currentMonth)->count();
    echo "✓ Final invoice count for {$currentMonthName}: {$finalCount}\n";
    echo "✓ Average invoices per student: " . round($finalCount / $activeStudents, 2) . "\n\n";
    
    echo "🎉 Done! Invoices have been regenerated successfully.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "   All changes have been rolled back.\n";
    exit(1);
}
