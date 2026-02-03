<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'setup_completed_school_info')) {
                $table->boolean('setup_completed_school_info')->default(false)->after('school_logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'setup_completed_school_info')) {
                $table->dropColumn('setup_completed_school_info');
            }
        });
    }
};
