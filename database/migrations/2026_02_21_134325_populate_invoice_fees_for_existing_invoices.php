<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all invoices that don't have invoice_fees
        $invoices = DB::table('invoices_payment_system')
            ->leftJoin('invoice_fees', 'invoices_payment_system.id', '=', 'invoice_fees.invoice_id')
            ->whereNull('invoice_fees.id')
            ->select('invoices_payment_system.*')
            ->get();

        foreach ($invoices as $invoice) {
            // Try to find the student
            $student = DB::table('student_profiles')->where('id', $invoice->student_id)->first();
            
            if (!$student) {
                continue;
            }

            // Get the grade level
            $grade = DB::table('grades')->where('id', $student->grade_id)->first();
            if (!$grade) {
                continue;
            }

            // Get the batch name
            $batch = DB::table('batches')->where('id', $invoice->batch_id)->first();
            $batchName = $batch ? $batch->name : null;

            // Get fee structures for this student's grade (matching by grade level as string)
            $feeStructures = DB::table('fee_structures_payment_system')
                ->where('grade', (string)$grade->level)
                ->get();

            if ($feeStructures->isEmpty()) {
                // Try to find ANY fee structure
                $feeStructures = DB::table('fee_structures_payment_system')
                    ->limit(1)
                    ->get();
                
                if ($feeStructures->isEmpty()) {
                    // Skip this invoice if we can't find any fee structure
                    continue;
                }
            }

            // Create invoice fees for each fee structure (or just the first one)
            $feeStructure = $feeStructures->first();
            
            // Get the fee type
            $feeType = DB::table('fee_types')->where('code', $feeStructure->fee_type)->first();
            
            DB::table('invoice_fees')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'invoice_id' => $invoice->id,
                'fee_id' => $feeStructure->id,
                'fee_type_id' => $feeType ? $feeType->id : null,
                'fee_name' => $feeStructure->name,
                'fee_name_mm' => $feeStructure->name_mm,
                'amount' => $invoice->total_amount, // Use invoice total amount
                'paid_amount' => $invoice->paid_amount,
                'remaining_amount' => $invoice->remaining_amount,
                'supports_payment_period' => $feeStructure->supports_payment_period ?? false,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
                'created_at' => $invoice->created_at ?? now(),
                'updated_at' => $invoice->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to delete the invoice_fees as they might be legitimate
        // This migration is a one-time data fix
    }
};
