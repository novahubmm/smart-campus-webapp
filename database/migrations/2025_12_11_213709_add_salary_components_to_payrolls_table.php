<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Attendance fields
            $table->integer('working_days')->default(21)->after('month');
            $table->integer('days_present')->default(0)->after('working_days');
            $table->integer('leave_days')->default(0)->after('days_present');
            $table->integer('annual_leave')->default(0)->after('leave_days');
            $table->integer('days_absent')->default(0)->after('annual_leave');
            
            // Salary components
            $table->decimal('basic_salary', 12, 0)->default(0)->after('days_absent');
            $table->decimal('attendance_allowance', 12, 0)->default(0)->after('basic_salary');
            $table->decimal('loyalty_bonus', 12, 0)->default(0)->after('attendance_allowance');
            $table->decimal('other_bonus', 12, 0)->default(0)->after('loyalty_bonus');
            
            // Receptionist info
            $table->string('receptionist_id')->nullable()->after('reference');
            $table->string('receptionist_name')->nullable()->after('receptionist_id');
            $table->string('remark')->nullable()->after('receptionist_name');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'working_days',
                'days_present',
                'leave_days',
                'annual_leave',
                'days_absent',
                'basic_salary',
                'attendance_allowance',
                'loyalty_bonus',
                'other_bonus',
                'receptionist_id',
                'receptionist_name',
                'remark',
            ]);
        });
    }
};
