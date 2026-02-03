<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('icon', 10)->nullable()->after('name');
            $table->string('icon_color', 20)->nullable()->after('icon');
            $table->string('progress_color', 20)->nullable()->after('icon_color');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['icon', 'icon_color', 'progress_color']);
        });
    }
};
