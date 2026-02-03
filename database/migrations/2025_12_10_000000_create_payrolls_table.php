<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_type');
            $table->uuid('employee_id');
            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->uuid('processed_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_type', 'employee_id', 'year', 'month'], 'payroll_employee_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
