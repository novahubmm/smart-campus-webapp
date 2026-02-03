<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rule_category_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('text');
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->text('consequence')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rule_category_id')
                ->references('id')
                ->on('rule_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_rules');
    }
};
