<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures_payment_system', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('name_mm', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('description_mm')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['one_time', 'monthly']);
            $table->string('fee_type', 50); // Changed from enum to string to allow any fee type
            $table->string('grade', 20);
            $table->string('batch', 20);
            $table->integer('target_month')->nullable()->comment('1-12 for one-time fees');
            $table->date('due_date');
            $table->boolean('supports_payment_period')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['grade', 'batch']);
            $table->index('frequency');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures_payment_system');
    }
};
