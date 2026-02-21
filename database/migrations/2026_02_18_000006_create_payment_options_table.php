<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('months');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->string('label', 100);
            $table->string('label_mm', 100)->nullable();
            $table->string('badge', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_options');
    }
};
