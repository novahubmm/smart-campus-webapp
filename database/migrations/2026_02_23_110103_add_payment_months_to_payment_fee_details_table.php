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
        Schema::table('payment_fee_details', function (Blueprint $table) {
            $table->integer('payment_months')->default(1)->after('is_partial')
                ->comment('Number of months paid for this specific fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_fee_details', function (Blueprint $table) {
            $table->dropColumn('payment_months');
        });
    }
};
