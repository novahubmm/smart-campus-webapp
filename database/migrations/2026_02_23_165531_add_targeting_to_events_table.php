<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->json('target_roles')->nullable()->after('status');
            $table->json('target_grades')->nullable()->after('target_roles');
            $table->json('target_teacher_grades')->nullable()->after('target_grades');
            $table->json('target_guardian_grades')->nullable()->after('target_teacher_grades');
            $table->json('target_departments')->nullable()->after('target_guardian_grades');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'target_roles',
                'target_grades',
                'target_teacher_grades',
                'target_guardian_grades',
                'target_departments'
            ]);
        });
    }
};
