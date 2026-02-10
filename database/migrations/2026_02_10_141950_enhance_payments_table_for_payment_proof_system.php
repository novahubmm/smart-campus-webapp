<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add payment_proof_id to link to payment proofs
            $table->uuid('payment_proof_id')->nullable()->after('student_id');
            $table->foreign('payment_proof_id')->references('id')->on('payment_proofs')->onDelete('set null');
            
            // Add payment_method_id to link to payment methods table
            $table->uuid('payment_method_id')->nullable()->after('payment_proof_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            
            // Add invoice_ids JSON column to track which invoices were paid
            $table->json('invoice_ids')->nullable()->after('amount');
            
            // Rename collected_by to recorded_by for consistency
            $table->renameColumn('collected_by', 'recorded_by');
            
            // Add indexes
            $table->index('payment_proof_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['payment_proof_id']);
            
            // Drop foreign keys and columns
            $table->dropForeign(['payment_proof_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn(['payment_proof_id', 'payment_method_id', 'invoice_ids']);
            
            // Rename back
            $table->renameColumn('recorded_by', 'collected_by');
        });
    }
};
