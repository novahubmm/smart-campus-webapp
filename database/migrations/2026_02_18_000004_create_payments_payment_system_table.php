<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments_payment_system', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_number', 50)->unique();
            $table->uuid('student_id');
            $table->uuid('invoice_id');
            $table->uuid('payment_method_id');
            $table->decimal('payment_amount', 10, 2);
            $table->enum('payment_type', ['full', 'partial']);
            $table->integer('payment_months')->default(1);
            $table->date('payment_date');
            $table->text('receipt_image_url')->nullable();
            $table->enum('status', ['pending_verification', 'verified', 'rejected'])->default('pending_verification');
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('invoice_id');
            $table->index('status');
            $table->index('payment_date');
            
            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices_payment_system')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments_payment_system');
    }
};
