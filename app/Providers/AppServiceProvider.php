<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            \App\Interfaces\AuthRepositoryInterface::class,
            \App\Repositories\AuthRepository::class
        );

        $this->app->bind(
            \App\Interfaces\AcademicRepositoryInterface::class,
            \App\Repositories\AcademicRepository::class
        );

        $this->app->bind(
            \App\Interfaces\DashboardRepositoryInterface::class,
            \App\Repositories\DashboardRepository::class
        );

        $this->app->bind(
            \App\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Interfaces\DepartmentRepositoryInterface::class,
            \App\Repositories\DepartmentRepository::class
        );

        $this->app->bind(
            \App\Interfaces\TeacherProfileRepositoryInterface::class,
            \App\Repositories\TeacherProfileRepository::class
        );

        $this->app->bind(
            \App\Interfaces\StudentProfileRepositoryInterface::class,
            \App\Repositories\StudentProfileRepository::class
        );

        $this->app->bind(
            \App\Interfaces\StaffProfileRepositoryInterface::class,
            \App\Repositories\StaffProfileRepository::class
        );

        $this->app->bind(
            \App\Interfaces\EventAnnouncementRepositoryInterface::class,
            \App\Repositories\EventAnnouncementRepository::class
        );

        $this->app->bind(
            \App\Interfaces\TimeTableAttendanceRepositoryInterface::class,
            \App\Repositories\TimeTableAttendanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\FinanceRepositoryInterface::class,
            \App\Repositories\FinanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\FinanceRecordRepositoryInterface::class,
            \App\Repositories\FinanceRecordRepository::class
        );

        $this->app->bind(
            \App\Interfaces\TimetableRepositoryInterface::class,
            \App\Repositories\TimetableRepository::class
        );

        $this->app->bind(
            \App\Interfaces\SalaryPayrollRepositoryInterface::class,
            \App\Repositories\SalaryPayrollRepository::class
        );

        $this->app->bind(
            \App\Interfaces\StudentAttendanceRepositoryInterface::class,
            \App\Repositories\StudentAttendanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\TeacherAttendanceRepositoryInterface::class,
            \App\Repositories\TeacherAttendanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\LeaveRequestRepositoryInterface::class,
            \App\Repositories\LeaveRequestRepository::class
        );

        $this->app->bind(
            \App\Interfaces\StaffAttendanceRepositoryInterface::class,
            \App\Repositories\StaffAttendanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\AnnouncementRepositoryInterface::class,
            \App\Repositories\AnnouncementRepository::class
        );

        $this->app->bind(
            \App\Interfaces\EventRepositoryInterface::class,
            \App\Repositories\EventRepository::class
        );

        $this->app->bind(
            \App\Interfaces\StudentFeeRepositoryInterface::class,
            \App\Repositories\StudentFeeRepository::class
        );

        $this->app->bind(
            \App\Interfaces\ExamRepositoryInterface::class,
            \App\Repositories\ExamRepository::class
        );

        $this->app->bind(
            \App\Interfaces\ActivityLogRepositoryInterface::class,
            \App\Repositories\ActivityLogRepository::class
        );

        // Teacher API Repository bindings
        $this->app->bind(
            \App\Interfaces\Teacher\TeacherAuthRepositoryInterface::class,
            \App\Repositories\Teacher\TeacherAuthRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Teacher\TeacherDashboardRepositoryInterface::class,
            \App\Repositories\Teacher\TeacherDashboardRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Teacher\TeacherClassRepositoryInterface::class,
            \App\Repositories\Teacher\TeacherClassRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Teacher\TeacherAttendanceApiRepositoryInterface::class,
            \App\Repositories\Teacher\TeacherAttendanceApiRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Teacher\TeacherHomeworkRepositoryInterface::class,
            \App\Repositories\Teacher\TeacherHomeworkRepository::class
        );

        // Guardian API Repository bindings
        $this->app->bind(
            \App\Interfaces\Guardian\GuardianAuthRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianAuthRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianDashboardRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianDashboardRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianStudentRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianStudentRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianAttendanceRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianAttendanceRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianExamRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianExamRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianHomeworkRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianHomeworkRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianTimetableRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianTimetableRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianAnnouncementRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianAnnouncementRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianFeeRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianFeeRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianLeaveRequestRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianLeaveRequestRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianNotificationRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianNotificationRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianCurriculumRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianCurriculumRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianReportCardRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianReportCardRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Guardian\GuardianSettingsRepositoryInterface::class,
            \App\Repositories\Guardian\GuardianSettingsRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);
        
        // Use custom pagination view
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.tailwind');
        
        // Register Blade directive for localized grade names
        \Illuminate\Support\Facades\Blade::directive('gradeName', function ($level) {
            return "<?php echo \App\Helpers\GradeHelper::getLocalizedName($level); ?>";
        });

        // Register Blade directive for localized class names (grade + section)
        \Illuminate\Support\Facades\Blade::directive('className', function ($expression) {
            return "<?php echo \App\Helpers\SectionHelper::formatFullClassName($expression); ?>";
        });
    }
}
