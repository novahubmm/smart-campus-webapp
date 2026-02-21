<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices_payment_system', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 50)->unique();
            $table->uuid('student_id');
            $table->string('academic_year', 20);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->enum('invoice_type', ['monthly', 'one_time', 'remaining_balance']);
            $table->uuid('parent_invoice_id')->nullable();
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_type');
            
            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('parent_invoice_id')->references('id')->on('invoices_payment_system')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices_payment_system');
    }
};
