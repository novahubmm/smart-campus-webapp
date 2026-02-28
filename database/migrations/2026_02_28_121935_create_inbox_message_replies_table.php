<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbox_message_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inbox_message_id');
            $table->uuidMorphs('sender');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('inbox_message_id')->references('id')->on('inbox_messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_message_replies');
    }
};
