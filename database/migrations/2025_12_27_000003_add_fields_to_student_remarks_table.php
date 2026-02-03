<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            if (!Schema::hasColumn('student_remarks', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('student_remarks', 'title')) {
                $table->string('title')->nullable()->after('category');
            }
            if (!Schema::hasColumn('student_remarks', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            $table->dropColumn(['category', 'title', 'description']);
        });
    }
};
