<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_category_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['academic', 'sports', 'cultural', 'holiday', 'meeting', 'exam', 'other'])->default('other');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->uuid('organized_by')->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('status')->default(true);

            $table->foreign('organized_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('event_category_id')->references('id')->on('event_categories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
