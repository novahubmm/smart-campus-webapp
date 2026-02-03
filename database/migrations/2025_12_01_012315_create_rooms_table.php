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
        Schema::create('rooms', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Room 101" or "Classroom A"
            $table->string('building'); // e.g., "Main Building"
            $table->string('floor'); // e.g., "1st Floor"
            $table->integer('capacity')->default(40);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
