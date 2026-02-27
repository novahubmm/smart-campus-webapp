<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\AcademicSetupController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicManagementController;
use App\Http\Controllers\SalaryPayrollController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\RuleCategoryController;
use App\Http\Controllers\SchoolRuleController;
use App\Http\Controllers\PWA\TeacherPWAController;
use App\Http\Controllers\PWA\GuardianPWAController;

Route::view('/', 'welcome')->name('welcome');
Route::view('/deactivated', 'auth.deactivated')->name('deactivated');

// Language switcher
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Guest password recovery (two-step: identifier -> NRC -> OTP send)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-account', [PasswordRecoveryController::class, 'createIdentifier'])
        ->name('password.recovery.identifier');
    Route::post('/forgot-account', [PasswordRecoveryController::class, 'storeIdentifier'])
        ->name('password.recovery.identifier.store');

    Route::get('/forgot-account/verify', [PasswordRecoveryController::class, 'createNrc'])
        ->name('password.recovery.nrc');
    Route::post('/forgot-account/verify', [PasswordRecoveryController::class, 'storeNrc'])
        ->name('password.recovery.nrc.store');

    Route::get('/forgot-account/otp', [PasswordRecoveryController::class, 'createOtp'])
        ->name('password.recovery.otp');
    Route::post('/forgot-account/otp', [PasswordRecoveryController::class, 'storeOtp'])
        ->name('password.recovery.otp.store');

    Route::get('/forgot-account/reset', [PasswordRecoveryController::class, 'createReset'])
        ->name('password.recovery.reset');
    Route::post('/forgot-account/reset', [PasswordRecoveryController::class, 'storeReset'])
        ->name('password.recovery.reset.store');
});

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'ensure.active', 'verified', 'ensure.setup:school'])
    ->name('dashboard');

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/setup', \App\Http\Controllers\SetupController::class)->name('setup.overview');
});

Route::middleware(['auth', 'ensure.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications (AJAX endpoints for web)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });

    Route::view('/maintenance', 'maintenance')->name('maintenance');
    Route::view('/user-manual', 'user-manual')->name('manual');

    // User & access management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Academic setup
    Route::get('/academic-setup', [AcademicSetupController::class, 'index'])->name('academic-setup.index');
    Route::post('/academic-setup/complete', [AcademicSetupController::class, 'completeSetup'])->name('academic-setup.complete');

    // Academic Management
    Route::prefix('academic-management')->middleware(['auth', 'ensure.setup:academic'])->group(function () {
        Route::get('/', [AcademicManagementController::class, 'index'])->name('academic-management.index');

        Route::post('/batches', [AcademicManagementController::class, 'storeBatch'])->name('academic-management.batches.store');
        Route::get('/batches/{id}', [AcademicManagementController::class, 'showBatch'])->name('academic-management.batches.show');
        Route::get('/batches/{id}/edit', [AcademicManagementController::class, 'editBatch'])->name('academic-management.batches.edit');
        Route::put('/batches/{id}', [AcademicManagementController::class, 'updateBatch'])->name('academic-management.batches.update');
        Route::delete('/batches/{id}', [AcademicManagementController::class, 'destroyBatch'])->name('academic-management.batches.destroy');

        Route::post('/grades', [AcademicManagementController::class, 'storeGrade'])->name('academic-management.grades.store');
        Route::get('/grades/{id}', [AcademicManagementController::class, 'showGrade'])->name('academic-management.grades.show');
        Route::get('/grades/{id}/edit', [AcademicManagementController::class, 'editGrade'])->name('academic-management.grades.edit');
        Route::put('/grades/{id}', [AcademicManagementController::class, 'updateGrade'])->name('academic-management.grades.update');
        Route::delete('/grades/{id}', [AcademicManagementController::class, 'deleteGrade'])->name('academic-management.grades.destroy');

        Route::post('/classes', [AcademicManagementController::class, 'storeClass'])->name('academic-management.classes.store');
        Route::get('/classes/search-students', [AcademicManagementController::class, 'searchStudents'])
            ->name('academic-management.classes.search-students');
        Route::get('/classes/{id}', [AcademicManagementController::class, 'showClass'])->name('academic-management.classes.show');
        Route::get('/classes/{id}/edit', [AcademicManagementController::class, 'editClass'])->name('academic-management.classes.edit');
        Route::put('/classes/{id}', [AcademicManagementController::class, 'updateClass'])->name('academic-management.classes.update');
        Route::delete('/classes/{id}', [AcademicManagementController::class, 'destroyClass'])->name('academic-management.classes.destroy');
        Route::post('/classes/{class}/add-student', [AcademicManagementController::class, 'addStudentToClass'])
            ->name('academic-management.classes.add-student');

        Route::post('/rooms', [AcademicManagementController::class, 'storeRoom'])->name('academic-management.rooms.store');
        Route::get('/rooms/{id}', [AcademicManagementController::class, 'showRoom'])->name('academic-management.rooms.show');
        Route::get('/rooms/{id}/edit', [AcademicManagementController::class, 'editRoom'])->name('academic-management.rooms.edit');
        Route::put('/rooms/{id}', [AcademicManagementController::class, 'updateRoom'])->name('academic-management.rooms.update');
        Route::delete('/rooms/{id}', [AcademicManagementController::class, 'deleteRoom'])->name('academic-management.rooms.destroy');

        Route::post('/subjects', [AcademicManagementController::class, 'storeSubject'])->name('academic-management.subjects.store');
        Route::get('/subjects/{id}', [AcademicManagementController::class, 'showSubject'])->name('academic-management.subjects.show');
        Route::get('/subjects/{id}/edit', [AcademicManagementController::class, 'editSubject'])->name('academic-management.subjects.edit');
        Route::put('/subjects/{id}', [AcademicManagementController::class, 'updateSubject'])->name('academic-management.subjects.update');
        Route::delete('/subjects/{id}', [AcademicManagementController::class, 'deleteSubject'])->name('academic-management.subjects.destroy');
        Route::post('/subjects/{id}/teachers/attach', [AcademicManagementController::class, 'attachTeacher'])->name('academic-management.subjects.teachers.attach');
        Route::delete('/subjects/{subjectId}/teachers/{teacherId}', [AcademicManagementController::class, 'detachTeacher'])->name('academic-management.subjects.teachers.detach');

        // Curriculum Management Routes (single bulk save)
        Route::post('/subjects/{subjectId}/curriculum', [\App\Http\Controllers\CurriculumController::class, 'saveCurriculum'])->name('curriculum.save');
        Route::delete('/curriculum/chapters/{chapterId}', [\App\Http\Controllers\CurriculumController::class, 'destroyChapter'])->name('curriculum.chapters.destroy');
        Route::delete('/curriculum/topics/{topicId}', [\App\Http\Controllers\CurriculumController::class, 'destroyTopic'])->name('curriculum.topics.destroy');
    });

    Route::prefix('exams')
        ->middleware(['ensure.setup:academic', 'can:manage exam database'])
        ->name('exams.')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\ExamController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\ExamController::class, 'store'])->name('store');
            Route::get('/{exam}', [\App\Http\Controllers\ExamController::class, 'show'])->name('show');
            Route::put('/{exam}', [\App\Http\Controllers\ExamController::class, 'update'])->name('update');
            Route::delete('/{exam}', [\App\Http\Controllers\ExamController::class, 'destroy'])->name('destroy');
            Route::patch('/{exam}/publish-results', [\App\Http\Controllers\ExamController::class, 'publishResults'])->name('publish-results');

            Route::post('/marks', [\App\Http\Controllers\ExamController::class, 'storeMark'])->name('marks.store');
            Route::put('/marks/{examMark}', [\App\Http\Controllers\ExamController::class, 'updateMark'])->name('marks.update');
            Route::delete('/marks/{examMark}', [\App\Http\Controllers\ExamController::class, 'destroyMark'])->name('marks.destroy');
        });

    // Ongoing Class / Virtual Campus
    Route::prefix('ongoing-class')->middleware(['ensure.setup:academic'])->name('ongoing-class.')->group(function () {
        Route::get('/', [\App\Http\Controllers\OngoingClassController::class, 'index'])->name('index');
        Route::get('/class/{class}', [\App\Http\Controllers\OngoingClassController::class, 'classDetail'])->name('class-detail');
        Route::get('/{class}/quick-view', [\App\Http\Controllers\OngoingClassController::class, 'quickView'])->name('quick-view');
        Route::get('/period/{period}', [\App\Http\Controllers\OngoingClassController::class, 'periodDetail'])->name('period-detail');
        Route::get('/api/data', [\App\Http\Controllers\OngoingClassController::class, 'getOngoingData'])->name('api.data');
    });

    // Class Remarks (Web)
    Route::post('/class-remarks', [\App\Http\Controllers\ClassRemarkController::class, 'store'])->name('class-remarks.store');

    // Student Remarks (Web)
    Route::post('/student-remarks', [\App\Http\Controllers\StudentRemarkController::class, 'store'])->name('student-remarks.store');

    // Homework Management
    Route::prefix('homework')->middleware(['ensure.setup:academic', 'feature:homework'])->name('homework.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HomeworkController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HomeworkController::class, 'index'])->name('create');
        Route::post('/', [\App\Http\Controllers\HomeworkController::class, 'store'])->name('store');
        Route::get('/{homework}', [\App\Http\Controllers\HomeworkController::class, 'show'])->name('show');
        Route::put('/{homework}', [\App\Http\Controllers\HomeworkController::class, 'update'])->name('update');
        Route::delete('/{homework}', [\App\Http\Controllers\HomeworkController::class, 'destroy'])->name('destroy');
        Route::get('/api/classes/{gradeId}', [\App\Http\Controllers\HomeworkController::class, 'getClassesByGrade'])->name('api.classes');
        Route::get('/api/subjects/{gradeId}', [\App\Http\Controllers\HomeworkController::class, 'getSubjectsByGrade'])->name('api.subjects');
    });

    // Curriculum Management
    Route::get('/curriculum', [\App\Http\Controllers\CurriculumController::class, 'index'])
        ->middleware(['ensure.setup:academic'])
        ->name('curriculum.index');

    Route::get('/event-announcement-setup', [\App\Http\Controllers\EventAnnouncementSetupController::class, 'index'])
        ->middleware('setup.locked')
        ->name('event-announcement-setup.index');
    Route::post('/event-announcement-setup', [\App\Http\Controllers\EventAnnouncementSetupController::class, 'store'])
        ->middleware('setup.locked')
        ->name('event-announcement-setup.store');

    Route::resource('events', \App\Http\Controllers\EventController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('ensure.setup:events');

    Route::prefix('event-categories')->middleware('ensure.setup:events')->name('event-categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EventCategoryController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\EventCategoryController::class, 'store'])->name('store');
        Route::put('/{event_category}', [\App\Http\Controllers\EventCategoryController::class, 'update'])->name('update');
        Route::delete('/{event_category}', [\App\Http\Controllers\EventCategoryController::class, 'destroy'])->name('destroy');
    });

    // Announcement management routes (admin only)
    Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware(['ensure.setup:events', 'can:manage announcements', 'feature:announcements']);
    
    // Announcement view route (accessible to staff for viewing from notifications)
    Route::get('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show'])
        ->name('announcements.show')
        ->middleware(['ensure.setup:events', 'feature:announcements']);

    Route::get('/time-table-attendance-setup', [\App\Http\Controllers\TimeTableAttendanceSetupController::class, 'index'])
        ->middleware('setup.locked')
        ->name('time-table-attendance-setup.index');
    Route::post('/time-table-attendance-setup', [\App\Http\Controllers\TimeTableAttendanceSetupController::class, 'store'])
        ->middleware('setup.locked')
        ->name('time-table-attendance-setup.store');

    Route::prefix('time-table')->group(function () {
        Route::get('/', [TimetableController::class, 'index'])->name('time-table.index');
        Route::get('/create', [TimetableController::class, 'create'])->name('time-table.create');
        Route::get('/class/{class}/versions', [TimetableController::class, 'classVersions'])->name('time-table.class-versions');
        Route::get('/{timetable}/edit', [TimetableController::class, 'edit'])->name('time-table.edit');
        Route::post('/', [TimetableController::class, 'store'])->name('time-table.store');
        Route::put('/{timetable}', [TimetableController::class, 'update'])->name('time-table.update');
        Route::post('/{timetable}/publish', [TimetableController::class, 'publish'])->name('time-table.publish');
        Route::post('/{timetable}/set-active', [TimetableController::class, 'setActive'])->name('time-table.set-active');
        Route::post('/{timetable}/duplicate', [TimetableController::class, 'duplicate'])->name('time-table.duplicate');
        Route::put('/{timetable}/version-name', [TimetableController::class, 'updateVersionName'])->name('time-table.update-version-name');
        Route::delete('/{timetable}', [TimetableController::class, 'destroy'])->name('time-table.destroy');
        Route::put('/period/{period}', [TimetableController::class, 'updatePeriod'])->name('time-table.update-period');
        Route::post('/switch-request/{switchRequest}/approve', [TimetableController::class, 'approveSwitchRequest'])->name('time-table.switch-request.approve');
        Route::post('/switch-request/{switchRequest}/reject', [TimetableController::class, 'rejectSwitchRequest'])->name('time-table.switch-request.reject');
        Route::post('/class/{class}/switch-request', [TimetableController::class, 'storeSwitchRequest'])->name('time-table.switch-request.store');
        Route::post('/global-settings', [TimetableController::class, 'updateGlobalSettings'])->name('time-table.global-settings');
    });

    Route::prefix('attendance')->middleware('ensure.setup:attendance')->group(function () {
        Route::get('/students', [\App\Http\Controllers\StudentAttendanceController::class, 'index'])
            ->name('student-attendance.index');
        Route::get('/collect-attendance', [\App\Http\Controllers\StudentAttendanceController::class, 'create'])
            ->name('student-attendance.create');
        Route::get('/collect-attendance/{class}', [\App\Http\Controllers\StudentAttendanceController::class, 'collectClass'])
            ->name('student-attendance.collect-class');
        Route::get('/collect-attendance/{class}/students', [\App\Http\Controllers\StudentAttendanceController::class, 'collectClassStudents'])
            ->name('student-attendance.collect-class-students');
        Route::get('/collect-attendance/{class}/period-status', [\App\Http\Controllers\StudentAttendanceController::class, 'collectClassPeriodStatus'])
            ->name('student-attendance.collect-class-period-status');
        Route::post('/collect-attendance/{class}/store', [\App\Http\Controllers\StudentAttendanceController::class, 'storeClassAttendance'])
            ->name('student-attendance.collect-class-store');
        Route::get('/students/class/{class}', [\App\Http\Controllers\StudentAttendanceController::class, 'classDetail'])
            ->name('student-attendance.class-detail');
        Route::get('/students/detail/{student}', [\App\Http\Controllers\StudentAttendanceController::class, 'studentDetail'])
            ->name('student-attendance.student-detail');
        Route::get('/students/class-summary', [\App\Http\Controllers\StudentAttendanceController::class, 'classSummary'])
            ->name('student-attendance.summary');
        Route::get('/students/list', [\App\Http\Controllers\StudentAttendanceController::class, 'students'])
            ->name('student-attendance.students');
        Route::get('/students/register', [\App\Http\Controllers\StudentAttendanceController::class, 'register'])
            ->name('student-attendance.register');
        Route::post('/students/register', [\App\Http\Controllers\StudentAttendanceController::class, 'storeRegister'])
            ->name('student-attendance.register.store');
        Route::get('/students/schedule', [\App\Http\Controllers\StudentAttendanceController::class, 'schedule'])
            ->name('student-attendance.schedule');
        Route::get('/students/collect/{period}', [\App\Http\Controllers\StudentAttendanceController::class, 'collect'])
            ->name('student-attendance.collect');
        Route::post('/students/collect/{period}', [\App\Http\Controllers\StudentAttendanceController::class, 'storeCollect'])
            ->name('student-attendance.collect.store');

        Route::get('/teachers', [\App\Http\Controllers\TeacherAttendanceController::class, 'index'])
            ->name('teacher-attendance.index');
        Route::get('/teachers/daily', [\App\Http\Controllers\TeacherAttendanceController::class, 'daily'])
            ->name('teacher-attendance.daily');
        Route::get('/teachers/monthly', [\App\Http\Controllers\TeacherAttendanceController::class, 'monthly'])
            ->name('teacher-attendance.monthly');
        Route::get('/teachers/summer', [\App\Http\Controllers\TeacherAttendanceController::class, 'summer'])
            ->name('teacher-attendance.summer');
        Route::get('/teachers/annual', [\App\Http\Controllers\TeacherAttendanceController::class, 'annual'])
            ->name('teacher-attendance.annual');
        Route::get('/teachers/detail/{teacher}', [\App\Http\Controllers\TeacherAttendanceController::class, 'detail'])
            ->name('teacher-attendance.detail');
        Route::post('/teachers/store', [\App\Http\Controllers\TeacherAttendanceController::class, 'store'])
            ->name('teacher-attendance.store');

        Route::get('/staff', [\App\Http\Controllers\StaffAttendanceController::class, 'index'])
            ->name('staff-attendance.index');
        Route::get('/staff/daily', [\App\Http\Controllers\StaffAttendanceController::class, 'daily'])
            ->name('staff-attendance.daily');
        Route::get('/staff/monthly', [\App\Http\Controllers\StaffAttendanceController::class, 'monthly'])
            ->name('staff-attendance.monthly');
        Route::get('/staff/summer', [\App\Http\Controllers\StaffAttendanceController::class, 'summer'])
            ->name('staff-attendance.summer');
        Route::get('/staff/annual', [\App\Http\Controllers\StaffAttendanceController::class, 'annual'])
            ->name('staff-attendance.annual');
        Route::get('/staff/detail/{staff}', [\App\Http\Controllers\StaffAttendanceController::class, 'detail'])
            ->name('staff-attendance.detail');
        Route::post('/staff/store', [\App\Http\Controllers\StaffAttendanceController::class, 'store'])
            ->name('staff-attendance.store');
    });

    Route::prefix('leave-requests')->middleware('ensure.setup:attendance')->group(function () {
        Route::get('/', [\App\Http\Controllers\LeaveRequestController::class, 'index'])
            ->name('leave-requests.index');
        Route::get('/staff/pending', [\App\Http\Controllers\LeaveRequestController::class, 'staffPending'])
            ->name('leave-requests.staff.pending');
        Route::get('/staff/history', [\App\Http\Controllers\LeaveRequestController::class, 'staffHistory'])
            ->name('leave-requests.staff.history');
        Route::get('/students/pending', [\App\Http\Controllers\LeaveRequestController::class, 'studentPending'])
            ->name('leave-requests.students.pending');
        Route::get('/students/history', [\App\Http\Controllers\LeaveRequestController::class, 'studentHistory'])
            ->name('leave-requests.students.history');
        Route::get('/apply', [\App\Http\Controllers\LeaveRequestController::class, 'apply'])
            ->name('leave-requests.apply');
        Route::get('/apply-for-other', [\App\Http\Controllers\LeaveRequestController::class, 'applyForOther'])
            ->name('leave-requests.apply-for-other');
        Route::get('/apply-for-other/search-users', [\App\Http\Controllers\LeaveRequestController::class, 'searchUsers'])
            ->name('leave-requests.search-users');
        Route::get('/apply-for-other/history', [\App\Http\Controllers\LeaveRequestController::class, 'userHistory'])
            ->name('leave-requests.user-history');
        Route::post('/apply', [\App\Http\Controllers\LeaveRequestController::class, 'store'])
            ->name('leave-requests.store');
        Route::post('/apply-for-other', [\App\Http\Controllers\LeaveRequestController::class, 'storeForOther'])
            ->name('leave-requests.store-for-other');
        Route::get('/my', [\App\Http\Controllers\LeaveRequestController::class, 'myHistory'])
            ->name('leave-requests.my');
        Route::post('/{leaveRequest}/approve', [\App\Http\Controllers\LeaveRequestController::class, 'approve'])
            ->name('leave-requests.approve');
        Route::post('/{leaveRequest}/reject', [\App\Http\Controllers\LeaveRequestController::class, 'reject'])
            ->name('leave-requests.reject');
    });

    // Department member management routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/search-members', [\App\Http\Controllers\DepartmentController::class, 'searchMembers'])
            ->name('search-members');
        Route::post('/{department}/add-member', [\App\Http\Controllers\DepartmentController::class, 'addMember'])
            ->name('add-member');
        Route::delete('/{department}/remove-member', [\App\Http\Controllers\DepartmentController::class, 'removeMember'])
            ->name('remove-member');
    });

    Route::resource('departments', \App\Http\Controllers\DepartmentController::class)
        ->whereUuid('department');

    Route::resource('teacher-profiles', \App\Http\Controllers\TeacherProfileController::class)->except(['destroy']);
    Route::get('teacher-profiles/{teacher_profile}/activities', [\App\Http\Controllers\TeacherProfileController::class, 'activities'])
        ->name('teacher-profiles.activities');

    Route::resource('student-profiles', \App\Http\Controllers\StudentProfileController::class)->except(['destroy']);

    Route::resource('staff-profiles', \App\Http\Controllers\StaffProfileController::class)->except(['destroy']);

    Route::resource('daily-report-recipients', \App\Http\Controllers\DailyReportRecipientController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/finance-setup', [\App\Http\Controllers\FinanceSetupController::class, 'index'])
        ->middleware('setup.locked')
        ->name('finance-setup.index');
    Route::post('/finance-setup', [\App\Http\Controllers\FinanceSetupController::class, 'store'])
        ->middleware('setup.locked')
        ->name('finance-setup.store');

    Route::get('/student-fees', [\App\Http\Controllers\StudentFeeController::class, 'index'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.index');
    Route::post('/student-fees/generate-invoices', [\App\Http\Controllers\StudentFeeController::class, 'generateInvoices'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.generate-invoices');
    Route::get('/student-fees/categories/{feeType}', [\App\Http\Controllers\StudentFeeController::class, 'showCategory'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.show');
    Route::post('/student-fees/categories', [\App\Http\Controllers\StudentFeeController::class, 'storeCategory'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.store');
    Route::put('/student-fees/categories/{feeType}', [\App\Http\Controllers\StudentFeeController::class, 'updateCategory'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.update');
    Route::delete('/student-fees/categories/{feeType}', [\App\Http\Controllers\StudentFeeController::class, 'destroyCategory'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.destroy');
    Route::post('/student-fees/categories/{feeType}/students/{student}/toggle', [\App\Http\Controllers\StudentFeeController::class, 'toggleStudentFeeType'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.toggle-student');
    Route::post('/student-fees/categories/{feeType}/activate-all', [\App\Http\Controllers\StudentFeeController::class, 'activateAllStudents'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.activate-all');
    Route::post('/student-fees/categories/{feeType}/students/{student}/send-invoice', [\App\Http\Controllers\StudentFeeController::class, 'sendInvoiceToStudent'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.send-invoice');
    Route::post('/student-fees/categories/{feeType}/bulk-send-invoices', [\App\Http\Controllers\StudentFeeController::class, 'bulkSendInvoices'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.categories.bulk-send-invoices');
    Route::post('/student-fees/structures', [\App\Http\Controllers\StudentFeeController::class, 'storeStructure'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.structures.store');
    Route::put('/student-fees/structures/{structure}', [\App\Http\Controllers\StudentFeeController::class, 'updateStructure'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.structures.update');
    Route::delete('/student-fees/structures/{structure}', [\App\Http\Controllers\StudentFeeController::class, 'destroyStructure'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.structures.destroy');
    Route::post('/student-fees/invoices', [\App\Http\Controllers\StudentFeeController::class, 'storeInvoice'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.invoices.store');
    Route::put('/student-fees/invoices/{invoice}', [\App\Http\Controllers\StudentFeeController::class, 'updateInvoice'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.invoices.update');
    Route::delete('/student-fees/invoices/{invoice}', [\App\Http\Controllers\StudentFeeController::class, 'destroyInvoice'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.invoices.destroy');
    // Legacy storePayment / confirmPayment / rejectPayment routes removed
    // All payment operations now use PaymentSystem routes below

    Route::post('/student-fees/payment-system/invoices/{invoice}/process', [\App\Http\Controllers\StudentFeeController::class, 'processPaymentSystem'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.process');
    
    Route::get('/student-fees/payment-receipt/{payment}', [\App\Http\Controllers\StudentFeeController::class, 'showPaymentReceipt'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-receipt');
    
    // PaymentSystem Payment Proof Routes (Mobile API)
    Route::post('/student-fees/payment-system/payments/{payment}/approve', [\App\Http\Controllers\StudentFeeController::class, 'approvePaymentSystemPayment'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.payments.approve');
    Route::post('/student-fees/payment-system/payments/{payment}/reject', [\App\Http\Controllers\StudentFeeController::class, 'rejectPaymentSystemPayment'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.payments.reject');
    Route::get('/student-fees/payment-system/payments/{payment}/details', [\App\Http\Controllers\StudentFeeController::class, 'getPaymentSystemPaymentDetails'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.payments.details');
    
    // Old PaymentProof Routes (Legacy)
    Route::post('/student-fees/payment-proofs/{paymentProof}/approve', [\App\Http\Controllers\StudentFeeController::class, 'approvePaymentProof'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-proofs.approve');
    Route::post('/student-fees/payment-proofs/{paymentProof}/reject', [\App\Http\Controllers\StudentFeeController::class, 'rejectPaymentProof'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-proofs.reject');
    Route::get('/student-fees/payment-proofs/{paymentProof}/details', [\App\Http\Controllers\StudentFeeController::class, 'getPaymentProofDetails'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-proofs.details');
    
    // PaymentSystem routes (new payment system from mobile)
    Route::get('/student-fees/payment-system/{payment}/details', [\App\Http\Controllers\StudentFeeController::class, 'getPaymentSystemDetails'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.details');
    Route::post('/student-fees/payment-system/{payment}/approve', [\App\Http\Controllers\StudentFeeController::class, 'approvePaymentSystemPayment'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.approve');
    Route::post('/student-fees/payment-system/{payment}/reject', [\App\Http\Controllers\StudentFeeController::class, 'rejectPaymentSystemPayment'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.payment-system.reject');
    
    Route::get('/student-fees/invoices/{invoice}/history', [\App\Http\Controllers\StudentFeeController::class, 'getInvoiceHistory'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.invoices.history');
    Route::post('/student-fees/students/{student}/reinform', [\App\Http\Controllers\StudentFeeController::class, 'reinform'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.students.reinform');
    Route::post('/student-fees/remind-all', [\App\Http\Controllers\StudentFeeController::class, 'remindAll'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.remind-all');
    Route::put('/student-fees/grades/{grade}', [\App\Http\Controllers\StudentFeeController::class, 'updateGradeFee'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.grades.update');
    
    // Payment Promotions
    Route::put('/student-fees/promotions/{promotion}', [\App\Http\Controllers\StudentFeeController::class, 'updatePromotion'])
        ->middleware('ensure.setup:finance')
        ->name('student-fees.promotions.update');
    
    // Payment Methods CRUD
    Route::post('/payment-methods', [\App\Http\Controllers\StudentFeeController::class, 'storePaymentMethod'])
        ->middleware('ensure.setup:finance')
        ->name('payment-methods.store');
    Route::put('/payment-methods/{paymentMethod}', [\App\Http\Controllers\StudentFeeController::class, 'updatePaymentMethod'])
        ->middleware('ensure.setup:finance')
        ->name('payment-methods.update');
    Route::delete('/payment-methods/{paymentMethod}', [\App\Http\Controllers\StudentFeeController::class, 'destroyPaymentMethod'])
        ->middleware('ensure.setup:finance')
        ->name('payment-methods.destroy');

    Route::get('/salary-payroll', [SalaryPayrollController::class, 'index'])
        ->middleware('ensure.setup:finance')
        ->name('salary-payroll.index');

    Route::post('/salary-payroll/pay', [SalaryPayrollController::class, 'pay'])
        ->middleware('ensure.setup:finance')
        ->name('salary-payroll.pay');

    // Temporary route to clear payroll data - remove after testing
    Route::get('/salary-payroll/clear-data', function () {
        try {
            $count = \DB::table('payrolls')->count();
            \DB::table('payrolls')->delete();
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} payroll records. Table structure is intact.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    })->middleware('auth')->name('salary-payroll.clear');

    Route::prefix('finance')->middleware('ensure.setup:finance')->group(function () {
        Route::get('/', [\App\Http\Controllers\FinanceController::class, 'index'])->name('finance.index');
        Route::post('/income', [\App\Http\Controllers\FinanceController::class, 'storeIncome'])->name('finance.income.store');
        Route::put('/income/{income}', [\App\Http\Controllers\FinanceController::class, 'updateIncome'])->name('finance.income.update');
        Route::delete('/income/{income}', [\App\Http\Controllers\FinanceController::class, 'destroyIncome'])->name('finance.income.destroy');

        Route::post('/expense', [\App\Http\Controllers\FinanceController::class, 'storeExpense'])->name('finance.expense.store');
        Route::put('/expense/{expense}', [\App\Http\Controllers\FinanceController::class, 'updateExpense'])->name('finance.expense.update');
        Route::delete('/expense/{expense}', [\App\Http\Controllers\FinanceController::class, 'destroyExpense'])->name('finance.expense.destroy');
    });

    Route::get('/settings/school-info', \App\Http\Controllers\SchoolInfoController::class)->name('settings.school-info');
    Route::post('/settings/school-info', [\App\Http\Controllers\SchoolInfoController::class, 'update'])->name('settings.school-info.update');
    Route::post('/settings/working-hours', [\App\Http\Controllers\SchoolInfoController::class, 'updateWorkingHours'])->name('settings.working-hours.update');
    Route::post('/settings/key-contacts', [\App\Http\Controllers\SchoolInfoController::class, 'storeContact'])->name('settings.key-contacts.store');
    Route::put('/settings/key-contacts/{contact}', [\App\Http\Controllers\SchoolInfoController::class, 'updateContact'])->name('settings.key-contacts.update');
    Route::delete('/settings/key-contacts/{contact}', [\App\Http\Controllers\SchoolInfoController::class, 'destroyContact'])->name('settings.key-contacts.destroy');

    Route::prefix('rules')->name('rules.')->group(function () {
        Route::get('/', [RuleCategoryController::class, 'index'])->name('index');
        Route::post('/', [RuleCategoryController::class, 'store'])->name('store');
        Route::get('/{ruleCategory}', [RuleCategoryController::class, 'show'])->name('show');
        Route::put('/{ruleCategory}', [RuleCategoryController::class, 'update'])->name('update');
        Route::delete('/{ruleCategory}', [RuleCategoryController::class, 'destroy'])->name('destroy');

        Route::post('/{ruleCategory}/items', [SchoolRuleController::class, 'store'])->name('items.store');
        Route::put('/{ruleCategory}/items/{schoolRule}', [SchoolRuleController::class, 'update'])->name('items.update');
        Route::delete('/{ruleCategory}/items/{schoolRule}', [SchoolRuleController::class, 'destroy'])->name('items.destroy');
    });

    Route::view('/settings/academic-year-terms', 'placeholders.page', [
        'title' => __('Academic Year & Terms'),
        'description' => __('Manage academic years and terms.'),
    ])->name('settings.academic-year-terms');

    Route::get('/user-activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
        ->name('user-activity-logs.index');

    // Report Centre
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\ReportController::class, 'store'])->name('store');
        Route::delete('/{report}', [\App\Http\Controllers\ReportController::class, 'destroy'])->name('destroy');
        
        // Incoming Reports (Daily reports from teachers)
        Route::get('/incoming', [\App\Http\Controllers\ReportController::class, 'incomingReports'])->name('incoming');
        Route::get('/incoming/{report}', [\App\Http\Controllers\ReportController::class, 'showIncomingReport'])->name('incoming.show');
        Route::post('/incoming/{report}/review', [\App\Http\Controllers\ReportController::class, 'reviewReport'])->name('incoming.review');
        Route::post('/incoming/{report}/acknowledge', [\App\Http\Controllers\ReportController::class, 'acknowledgeReport'])->name('incoming.acknowledge');
        
        // Student Reports
        Route::get('/students', [\App\Http\Controllers\ReportController::class, 'studentReports'])->name('students');
        Route::post('/students/generate', [\App\Http\Controllers\ReportController::class, 'generateStudentReport'])->name('students.generate');
        
        // Teacher Reports
        Route::get('/teachers', [\App\Http\Controllers\ReportController::class, 'teacherReports'])->name('teachers');
        Route::post('/teachers/generate', [\App\Http\Controllers\ReportController::class, 'generateTeacherReport'])->name('teachers.generate');
        
        // Staff Reports
        Route::get('/staff', [\App\Http\Controllers\ReportController::class, 'staffReports'])->name('staff');
        Route::post('/staff/generate', [\App\Http\Controllers\ReportController::class, 'generateStaffReport'])->name('staff.generate');
        
        // Attendance Reports
        Route::get('/attendance', [\App\Http\Controllers\ReportController::class, 'attendanceReports'])->name('attendance');
        Route::post('/attendance/generate', [\App\Http\Controllers\ReportController::class, 'generateAttendanceReport'])->name('attendance.generate');
        
        // API endpoints for dynamic selects
        Route::get('/api/classes/{gradeId}', [\App\Http\Controllers\ReportController::class, 'getClassesByGrade']);
        Route::get('/api/students/{classId}', [\App\Http\Controllers\ReportController::class, 'getStudentsByClass']);
    });

    Route::view('/contacts', 'contacts.index')->name('contacts.index');

    // Feedback system - sends directly to Control Panel
    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Staff Notification routes (for staff role users)
    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Staff\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Staff\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/list', [\App\Http\Controllers\Staff\NotificationController::class, 'list'])->name('notifications.list');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/save-fcm-token', [\App\Http\Controllers\Staff\NotificationController::class, 'saveFcmToken'])->name('notifications.save-fcm-token');
        Route::get('/notifications/{id}', [\App\Http\Controllers\Staff\NotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Staff\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Staff\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // FCM Test page (for development/testing)
    Route::get('/test-fcm', function () {
        return view('test-fcm');
    })->name('test-fcm');

    // Simple FCM Test page
    Route::get('/simple-fcm-test', function () {
        return view('simple-fcm-test');
    })->name('simple-fcm-test');

    // Backend FCM Test page
    Route::get('/backend-fcm-test', function () {
        return view('backend-fcm-test');
    })->name('backend-fcm-test');

    // PWA Routes - Protected by auth middleware
    // Teacher PWA Routes
    Route::prefix('teacher-pwa')->name('teacher-pwa.')->group(function () {
        Route::get('/dashboard', [TeacherPWAController::class, 'dashboard'])->name('dashboard');
        Route::get('/classes', [TeacherPWAController::class, 'classes'])->name('classes');
        Route::get('/attendance', [TeacherPWAController::class, 'attendance'])->name('attendance');
        Route::get('/homework', [TeacherPWAController::class, 'homework'])->name('homework');
        Route::get('/students', [TeacherPWAController::class, 'students'])->name('students');
        Route::get('/announcements', [TeacherPWAController::class, 'announcements'])->name('announcements');
        Route::get('/utilities', [TeacherPWAController::class, 'utilities'])->name('utilities');
        Route::get('/profile', [TeacherPWAController::class, 'profile'])->name('profile');
        Route::get('/timetable', [TeacherPWAController::class, 'timetable'])->name('timetable');
    });
    
    // Guardian PWA Routes
    Route::prefix('guardian-pwa')->name('guardian-pwa.')->group(function () {
        Route::get('/home', [GuardianPWAController::class, 'home'])->name('home');
        Route::get('/attendance', [GuardianPWAController::class, 'attendance'])->name('attendance');
        Route::get('/homework', [GuardianPWAController::class, 'homework'])->name('homework');
        Route::get('/timetable', [GuardianPWAController::class, 'timetable'])->name('timetable');
        Route::get('/fees', [GuardianPWAController::class, 'fees'])->name('fees');
        Route::get('/announcements', [GuardianPWAController::class, 'announcements'])->name('announcements');
        Route::get('/utilities', [GuardianPWAController::class, 'utilities'])->name('utilities');
        Route::get('/profile', [GuardianPWAController::class, 'profile'])->name('profile');
        Route::get('/student/{id}', [GuardianPWAController::class, 'studentDetail'])->name('student-detail');
        Route::get('/announcement/{id}', [GuardianPWAController::class, 'announcementDetail'])->name('announcement-detail');
    });
    
    // Shared PWA Routes
    Route::get('/pwa/notifications', function() {
        return view('pwa.notifications', [
            'headerTitle' => 'Notifications',
            'showBack' => true,
            'hideBottomNav' => true
        ]);
    })->name('pwa.notifications');
});

// System Admin Routes
Route::middleware(['auth', 'ensure.active', 'role:system_admin'])->prefix('system-admin')->name('system-admin.')->group(function () {
    // Feature Flag Management
    Route::get('/features', [\App\Http\Controllers\FeatureFlagController::class, 'index'])->name('features.index');
    Route::post('/features', [\App\Http\Controllers\FeatureFlagController::class, 'update'])->name('features.update');
    
    // Feedback Management
    Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'show'])->name('feedback.show');
    Route::put('/feedback/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'update'])->name('feedback.update');
});

// Public Feedback Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/feedback', [\App\Http\Controllers\FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
});

require __DIR__ . '/auth.php';
