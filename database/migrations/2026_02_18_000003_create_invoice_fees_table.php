<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->uuid('fee_id');
            $table->string('fee_name', 100);
            $table->string('fee_name_mm', 100)->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->boolean('supports_payment_period')->default(false);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();
            
            $table->index('invoice_id');
            $table->index('fee_id');
            $table->index('status');
            
            $table->foreign('invoice_id')->references('id')->on('invoices_payment_system')->onDelete('cascade');
            $table->foreign('fee_id')->references('id')->on('fee_structures_payment_system')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_fees');
    }
};
