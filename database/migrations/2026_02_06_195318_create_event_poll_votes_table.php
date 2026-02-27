<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_poll_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('poll_option_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('poll_option_id')->references('id')->on('event_poll_options')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_poll_votes');
    }
};
