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
        Schema::create('fee_type_frequencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fee_type_id');
            $table->enum('frequency', ['one_time', 'monthly']);
            $table->integer('start_month'); // 1-12
            $table->integer('end_month'); // 1-12
            $table->timestamps();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_type_frequencies');
    }
};
