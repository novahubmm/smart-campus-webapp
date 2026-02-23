<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Add fields for partial payment tracking
            $table->decimal('total_amount', 12, 2)->default(0)->after('amount')->comment('Full monthly salary');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount')->comment('Amount paid in this transaction or cumulative');
            $table->integer('payment_count')->default(0)->after('paid_amount')->comment('Number of payments made (0 for pending records)');
            
            // Add index for status and period queries
            $table->index(['status', 'year', 'month'], 'payroll_status_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex('payroll_status_period_idx');
            $table->dropColumn(['total_amount', 'paid_amount', 'payment_count']);
        });
    }
};
