<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('in_school_relative_name')->nullable()->after('partner_phone');
            $table->string('in_school_relative_relationship')->nullable()->after('in_school_relative_name');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn(['in_school_relative_name', 'in_school_relative_relationship']);
        });
    }
};
