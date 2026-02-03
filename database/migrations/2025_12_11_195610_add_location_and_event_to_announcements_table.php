<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('location')->nullable()->after('priority');
            $table->uuid('event_id')->nullable()->after('location');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['location', 'event_id']);
        });
    }
};
