<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('fa-bullhorn');
            $table->string('color')->default('#f59e0b'); // amber
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add announcement_type_id to announcements table
        Schema::table('announcements', function (Blueprint $table) {
            $table->uuid('announcement_type_id')->nullable()->after('content');
            $table->foreign('announcement_type_id')->references('id')->on('announcement_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['announcement_type_id']);
            $table->dropColumn('announcement_type_id');
        });

        Schema::dropIfExists('announcement_types');
    }
};
