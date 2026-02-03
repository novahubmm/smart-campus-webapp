<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->foreignUuid('recipient_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('direction')->default('incoming')->after('recipient_user_id'); // incoming = from teacher, outgoing = to teacher
            
            $table->index(['recipient_user_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex(['recipient_user_id', 'direction']);
            $table->dropForeign(['recipient_user_id']);
            $table->dropColumn(['recipient_user_id', 'direction']);
        });
    }
};
