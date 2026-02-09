<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('payment_method_id');
            $table->decimal('payment_amount', 10, 2);
            $table->integer('payment_months')->default(1);
            $table->date('payment_date');
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending_verification', 'verified', 'rejected'])->default('pending_verification');
            $table->uuid('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('fee_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('student_id');
            $table->index('payment_method_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
