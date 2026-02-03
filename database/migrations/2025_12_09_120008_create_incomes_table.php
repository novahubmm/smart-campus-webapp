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
        Schema::create('incomes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('income_number')->unique();
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'online', 'mobile_payment'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->uuid('grade_id')->nullable();
            $table->uuid('class_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
