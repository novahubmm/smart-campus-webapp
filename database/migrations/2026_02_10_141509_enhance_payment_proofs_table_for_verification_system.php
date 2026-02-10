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
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Add composite index for student_id + status for efficient filtering
            $table->index(['student_id', 'status'], 'idx_payment_proof_student_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Drop composite index
            $table->dropIndex('idx_payment_proof_student_status');
        });
    }
};
