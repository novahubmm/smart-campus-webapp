<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'announcement_notify_email')) {
                $table->boolean('announcement_notify_email')->default(false);
            }
            if (!Schema::hasColumn('settings', 'announcement_notify_push')) {
                $table->boolean('announcement_notify_push')->default(false);
            }
            if (!Schema::hasColumn('settings', 'announcement_notify_in_app')) {
                $table->boolean('announcement_notify_in_app')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'announcement_notify_email')) {
                $table->dropColumn('announcement_notify_email');
            }
            if (Schema::hasColumn('settings', 'announcement_notify_push')) {
                $table->dropColumn('announcement_notify_push');
            }
            if (Schema::hasColumn('settings', 'announcement_notify_in_app')) {
                $table->dropColumn('announcement_notify_in_app');
            }
        });
    }
};
