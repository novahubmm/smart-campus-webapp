<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('payments', 'collected_by')) {
                $table->uuid('collected_by')->nullable()->after('notes');
                $table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('payments', 'receptionist_id')) {
                $table->uuid('receptionist_id')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('payments', 'receptionist_name')) {
                $table->string('receptionist_name')->nullable()->after('receptionist_id');
            }
            
            if (!Schema::hasColumn('payments', 'recorded_by')) {
                $table->uuid('recorded_by')->nullable()->after('receptionist_name');
                $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('payments', 'payment_proof_id')) {
                $table->uuid('payment_proof_id')->nullable()->after('student_id');
            }
            
            if (!Schema::hasColumn('payments', 'payment_method_id')) {
                $table->uuid('payment_method_id')->nullable()->after('payment_proof_id');
            }
            
            if (!Schema::hasColumn('payments', 'invoice_ids')) {
                $table->json('invoice_ids')->nullable()->after('reference_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'collected_by')) {
                $table->dropForeign(['collected_by']);
                $table->dropColumn('collected_by');
            }
            
            if (Schema::hasColumn('payments', 'receptionist_id')) {
                $table->dropColumn('receptionist_id');
            }
            
            if (Schema::hasColumn('payments', 'receptionist_name')) {
                $table->dropColumn('receptionist_name');
            }
            
            if (Schema::hasColumn('payments', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }
            
            if (Schema::hasColumn('payments', 'payment_proof_id')) {
                $table->dropColumn('payment_proof_id');
            }
            
            if (Schema::hasColumn('payments', 'payment_method_id')) {
                $table->dropColumn('payment_method_id');
            }
            
            if (Schema::hasColumn('payments', 'invoice_ids')) {
                $table->dropColumn('invoice_ids');
            }
        });
    }
};
