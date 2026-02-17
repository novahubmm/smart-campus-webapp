<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_announcement_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guardian_id');
            $table->uuid('announcement_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();
            $table->timestamps();

            $table->foreign('guardian_id')->references('id')->on('guardian_profiles')->onDelete('cascade');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            
            // Unique constraint to ensure one record per guardian-announcement pair
            $table->unique(['guardian_id', 'announcement_id'], 'guardian_announcement_unique');
            
            // Indexes for performance
            $table->index(['guardian_id', 'is_read']);
            $table->index(['guardian_id', 'is_pinned']);
            $table->index(['announcement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_announcement_interactions');
    }
};
