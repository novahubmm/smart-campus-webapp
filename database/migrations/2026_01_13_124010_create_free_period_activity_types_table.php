<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_period_activity_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // lesson_prep, grading, etc.
            $table->string('label'); // Display name
            $table->string('color', 20)->default('#6B7280'); // Hex color
            $table->text('icon_svg')->nullable(); // SVG icon
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_period_activity_types');
    }
};
