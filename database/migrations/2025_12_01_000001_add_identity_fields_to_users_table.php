<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('nrc')->nullable()->unique()->after('phone');
            $table->string('password_otp_code', 6)->nullable()->after('remember_token');
            $table->timestamp('password_otp_expires_at')->nullable()->after('password_otp_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'nrc', 'password_otp_code', 'password_otp_expires_at']);
        });
    }
};
