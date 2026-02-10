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
        Schema::table('invoices', function (Blueprint $table) {
            // Add fee_structure_id foreign key
            $table->uuid('fee_structure_id')->nullable()->after('student_id');
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->onDelete('set null');
            
            // Add month and academic_year columns
            $table->string('month', 7)->nullable()->after('invoice_date'); // Format: YYYY-MM
            $table->string('academic_year', 9)->nullable()->after('month'); // Format: YYYY-YYYY
            
            // Update status enum to include unpaid and pending_verification
            // Note: We'll keep existing statuses for backward compatibility
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled', 'unpaid', 'pending_verification'])->default('draft')->change();
            
            // Add payment_date for tracking when payment was made
            $table->date('payment_date')->nullable()->after('due_date');
            
            // Add indexes for performance
            $table->index(['student_id', 'status'], 'idx_student_status');
            $table->index(['month', 'academic_year'], 'idx_month_year');
            $table->index('fee_structure_id', 'idx_fee_structure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_student_status');
            $table->dropIndex('idx_month_year');
            $table->dropIndex('idx_fee_structure');
            
            // Drop foreign key and column
            $table->dropForeign(['fee_structure_id']);
            $table->dropColumn(['fee_structure_id', 'month', 'academic_year', 'payment_date']);
            
            // Revert status enum to original values
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft')->change();
        });
    }
};
