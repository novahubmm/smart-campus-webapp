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
        // Update expenses table
        Schema::table('expenses', function (Blueprint $table) {
            // Drop the old enum column
            $table->dropColumn('payment_method');
        });
        
        Schema::table('expenses', function (Blueprint $table) {
            // Add new UUID column for payment method
            $table->uuid('payment_method_id')->nullable()->after('expense_date');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
        });

        // Update incomes table
        Schema::table('incomes', function (Blueprint $table) {
            // Drop the old enum column
            $table->dropColumn('payment_method');
        });
        
        Schema::table('incomes', function (Blueprint $table) {
            // Add new UUID column for payment method
            $table->uuid('payment_method_id')->nullable()->after('income_date');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
        
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card'])->default('cash')->after('expense_date');
        });

        // Revert incomes table
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
        
        Schema::table('incomes', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'online', 'mobile_payment'])->default('cash')->after('income_date');
        });
    }
};
