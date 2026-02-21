<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_type_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('fee_type_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
            
            // Ensure unique combination
            $table->unique(['student_id', 'fee_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_type_assignments');
    }
};
