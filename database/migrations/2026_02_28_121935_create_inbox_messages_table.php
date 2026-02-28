<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guardian_profile_id');
            $table->uuid('student_profile_id')->nullable();

            $table->string('subject');
            $table->enum('category', ['general', 'academic', 'behavior', 'health', 'complaint'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['unread', 'read', 'assigned', 'resolved', 'closed'])->default('unread');

            $table->nullableUuidMorphs('assigned_to');

            $table->timestamps();

            $table->foreign('guardian_profile_id')->references('id')->on('guardian_profiles')->onDelete('cascade');
            $table->foreign('student_profile_id')->references('id')->on('student_profiles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbox_messages');
    }
};
