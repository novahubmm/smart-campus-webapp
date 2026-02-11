<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_rules', function (Blueprint $table) {
            $table->string('title')->nullable()->after('rule_category_id');
            $table->string('title_mm')->nullable()->after('title');
            $table->text('description')->nullable()->after('title_mm');
            $table->text('description_mm')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('school_rules', function (Blueprint $table) {
            $table->dropColumn(['title', 'title_mm', 'description', 'description_mm']);
        });
    }
};
