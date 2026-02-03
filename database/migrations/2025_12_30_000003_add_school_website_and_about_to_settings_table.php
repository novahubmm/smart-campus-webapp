<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'school_website')) {
                $table->string('school_website')->nullable()->after('school_address');
            }
            if (!Schema::hasColumn('settings', 'school_about_us')) {
                $table->text('school_about_us')->nullable()->after('school_website');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'school_about_us')) {
                $table->dropColumn('school_about_us');
            }
            if (Schema::hasColumn('settings', 'school_website')) {
                $table->dropColumn('school_website');
            }
        });
    }
};
