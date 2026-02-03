<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Student Attendance - heavily queried table
        Schema::table('student_attendance', function (Blueprint $table) {
            if (!$this->hasIndex('student_attendance', 'student_attendance_date_idx')) {
                $table->index('date', 'student_attendance_date_idx');
            }
            if (!$this->hasIndex('student_attendance', 'student_attendance_status_idx')) {
                $table->index('status', 'student_attendance_status_idx');
            }
            if (!$this->hasIndex('student_attendance', 'student_attendance_date_student_idx')) {
                $table->index(['date', 'student_id'], 'student_attendance_date_student_idx');
            }
            if (!$this->hasIndex('student_attendance', 'student_attendance_student_date_status_idx')) {
                $table->index(['student_id', 'date', 'status'], 'student_attendance_student_date_status_idx');
            }
        });

        // Periods - queried by teacher and day
        Schema::table('periods', function (Blueprint $table) {
            if (!$this->hasIndex('periods', 'periods_teacher_idx')) {
                $table->index('teacher_profile_id', 'periods_teacher_idx');
            }
            if (!$this->hasIndex('periods', 'periods_day_idx')) {
                $table->index('day_of_week', 'periods_day_idx');
            }
            if (!$this->hasIndex('periods', 'periods_teacher_day_idx')) {
                $table->index(['teacher_profile_id', 'day_of_week'], 'periods_teacher_day_idx');
            }
            if (!$this->hasIndex('periods', 'periods_is_break_idx')) {
                $table->index('is_break', 'periods_is_break_idx');
            }
        });

        // Student Profiles - queried by class and grade
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!$this->hasIndex('student_profiles', 'student_profiles_class_idx')) {
                $table->index('class_id', 'student_profiles_class_idx');
            }
            if (!$this->hasIndex('student_profiles', 'student_profiles_grade_idx')) {
                $table->index('grade_id', 'student_profiles_grade_idx');
            }
            if (!$this->hasIndex('student_profiles', 'student_profiles_class_grade_idx')) {
                $table->index(['class_id', 'grade_id'], 'student_profiles_class_grade_idx');
            }
            if (!$this->hasIndex('student_profiles', 'student_profiles_status_idx')) {
                $table->index('status', 'student_profiles_status_idx');
            }
        });

        // Teacher Profiles - queried frequently
        if (Schema::hasTable('teacher_profiles')) {
            Schema::table('teacher_profiles', function (Blueprint $table) {
                if (!$this->hasIndex('teacher_profiles', 'teacher_profiles_user_id_idx')) {
                    $table->index('user_id', 'teacher_profiles_user_id_idx');
                }
            });
        }

        // Timetables - queried by class and status
        Schema::table('timetables', function (Blueprint $table) {
            if (!$this->hasIndex('timetables', 'timetables_class_active_status_idx')) {
                $table->index(['class_id', 'is_active', 'status'], 'timetables_class_active_status_idx');
            }
        });

        // Staff Attendance
        if (Schema::hasTable('staff_attendance')) {
            Schema::table('staff_attendance', function (Blueprint $table) {
                if (!$this->hasIndex('staff_attendance', 'staff_attendance_date_idx')) {
                    $table->index('date', 'staff_attendance_date_idx');
                }
                if (!$this->hasIndex('staff_attendance', 'staff_attendance_status_idx')) {
                    $table->index('status', 'staff_attendance_status_idx');
                }
            });
        }

        // Teacher Attendance
        if (Schema::hasTable('teacher_attendance')) {
            Schema::table('teacher_attendance', function (Blueprint $table) {
                if (!$this->hasIndex('teacher_attendance', 'teacher_attendance_date_idx')) {
                    $table->index('date', 'teacher_attendance_date_idx');
                }
                if (!$this->hasIndex('teacher_attendance', 'teacher_attendance_status_idx')) {
                    $table->index('status', 'teacher_attendance_status_idx');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropIndex('student_attendance_date_idx');
            $table->dropIndex('student_attendance_status_idx');
            $table->dropIndex('student_attendance_date_student_idx');
            $table->dropIndex('student_attendance_student_date_status_idx');
        });

        Schema::table('periods', function (Blueprint $table) {
            $table->dropIndex('periods_teacher_idx');
            $table->dropIndex('periods_day_idx');
            $table->dropIndex('periods_teacher_day_idx');
            $table->dropIndex('periods_is_break_idx');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropIndex('student_profiles_class_idx');
            $table->dropIndex('student_profiles_grade_idx');
            $table->dropIndex('student_profiles_class_grade_idx');
            $table->dropIndex('student_profiles_status_idx');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
