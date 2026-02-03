<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            // Change attachment from single string to JSON array for multiple files
            $table->json('attachments')->nullable()->after('attachment');
        });
    }

    public function down(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
