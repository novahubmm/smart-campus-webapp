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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('recipient_id'); // Guardian user ID
            $table->string('notification_type'); // 'rejection', 'reinform', etc.
            $table->json('data')->nullable(); // Notification content/metadata
            $table->uuid('triggered_by')->nullable(); // Admin who triggered the notification
            $table->timestamp('sent_at');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('triggered_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for querying
            $table->index('recipient_id');
            $table->index('notification_type');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
