<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_fee_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payment_id');
            $table->uuid('invoice_fee_id');
            $table->string('fee_name', 100);
            $table->string('fee_name_mm', 100)->nullable();
            $table->decimal('full_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->boolean('is_partial')->default(false);
            $table->timestamps();
            
            $table->index('payment_id');
            $table->index('invoice_fee_id');
            
            $table->foreign('payment_id')->references('id')->on('payments_payment_system')->onDelete('cascade');
            $table->foreign('invoice_fee_id')->references('id')->on('invoice_fees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_fee_details');
    }
};
