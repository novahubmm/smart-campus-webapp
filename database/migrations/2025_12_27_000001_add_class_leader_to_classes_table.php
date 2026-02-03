<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'class_leader_id')) {
                $table->foreignUuid('class_leader_id')->nullable()->constrained('student_profiles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['class_leader_id']);
            $table->dropColumn('class_leader_id');
        });
    }
};
