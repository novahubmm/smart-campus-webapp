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
        Schema::create('free_period_activity_items', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id', 50);
            $table->unsignedBigInteger('activity_type_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('free_period_activities')->onDelete('cascade');
            $table->foreign('activity_type_id')->references('id')->on('activity_types')->onDelete('cascade');
            $table->index('activity_id');
            $table->index('activity_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_period_activity_items');
    }
};
