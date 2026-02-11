<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rule_categories', function (Blueprint $table) {
            $table->string('title_mm')->nullable()->after('title');
            $table->text('description_mm')->nullable()->after('description');
            $table->string('icon_background_color')->nullable()->after('icon_bg_color');
            $table->unsignedInteger('priority')->default(0)->after('icon_background_color');
            $table->boolean('is_active')->default(true)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('rule_categories', function (Blueprint $table) {
            $table->dropColumn(['title_mm', 'description_mm', 'icon_background_color', 'priority', 'is_active']);
        });
    }
};
