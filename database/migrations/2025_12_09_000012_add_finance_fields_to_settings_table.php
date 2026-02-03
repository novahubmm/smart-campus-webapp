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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('payment_frequency')->default('monthly');
            $table->decimal('late_fee_percentage', 5, 2)->default(0);
            $table->integer('late_fee_grace_period')->default(0);
            $table->decimal('default_discount_percentage', 5, 2)->default(0);
            $table->json('tuition_fee_by_grade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'payment_frequency',
                'late_fee_percentage',
                'late_fee_grace_period',
                'default_discount_percentage',
                'tuition_fee_by_grade',
            ]);
        });
    }
};
