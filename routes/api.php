<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\FeedbackApiController;
use App\Http\Controllers\Api\ControlApiController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\RulesController;
use App\Http\Controllers\Api\V1\Teacher\AuthController as TeacherAuthController;
use App\Http\Controllers\Api\V1\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\V1\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Api\V1\Teacher\SubjectController as TeacherSubjectController;
use App\Http\Controllers\Api\V1\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Api\V1\Teacher\HomeworkController as TeacherHomeworkController;
use App\Http\Controllers\Api\V1\Teacher\AnnouncementController as TeacherAnnouncementController;
use App\Http\Controllers\Api\V1\Teacher\LeaveRequestController as TeacherLeaveRequestController;
use App\Http\Controllers\Api\V1\Teacher\DailyReportController as TeacherDailyReportController;
use App\Http\Controllers\Api\V1\Teacher\PayslipController as TeacherPayslipController;
use App\Http\Controllers\Api\V1\Teacher\ClassRecordController as TeacherClassRecordController;
use App\Http\Controllers\Api\V1\Teacher\NotificationController as TeacherNotificationController;
use App\Http\Controllers\Api\V1\Teacher\ForgotPasswordController as TeacherForgotPasswordController;
use App\Http\Controllers\Api\V1\Teacher\ExamController as TeacherExamController;
use App\Http\Controllers\Api\V1\Teacher\RemarkController as TeacherRemarkController;
use App\Http\Controllers\Api\V1\Teacher\FreePeriodActivityController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\FirebaseConfigController;

// Unified API Controllers
use App\Http\Controllers\Api\V1\UnifiedAuthController;
use App\Http\Controllers\Api\V1\UnifiedDashboardController;
use App\Http\Controllers\Api\V1\UnifiedNotificationController;

// Guardian API Controllers
use App\Http\Controllers\Api\V1\Guardian\AuthController as GuardianAuthController;
use App\Http\Controllers\Api\V1\Guardian\DashboardController as GuardianDashboardController;
use App\Http\Controllers\Api\V1\Guardian\StudentController as GuardianStudentController;
use App\Http\Controllers\Api\V1\Guardian\AttendanceController as GuardianAttendanceController;
use App\Http\Controllers\Api\V1\Guardian\ExamController as GuardianExamController;
use App\Http\Controllers\Api\V1\Guardian\HomeworkController as GuardianHomeworkController;
use App\Http\Controllers\Api\V1\Guardian\TimetableController as GuardianTimetableController;
use App\Http\Controllers\Api\V1\Guardian\AnnouncementController as GuardianAnnouncementController;
use App\Http\Controllers\Api\V1\Guardian\FeeController as GuardianFeeController;
use App\Http\Controllers\Api\V1\Guardian\LeaveRequestController as GuardianLeaveRequestController;
use App\Http\Controllers\Api\V1\Guardian\NotificationController as GuardianNotificationController;
use App\Http\Controllers\Api\V1\Guardian\CurriculumController as GuardianCurriculumController;
use App\Http\Controllers\Api\V1\Guardian\ReportCardController as GuardianReportCardController;
use App\Http\Controllers\Api\V1\Guardian\SettingsController as GuardianSettingsController;

// Health endpoints - Public for monitoring
Route::get('/health', [HealthController::class, 'index']);
Route::get('/ping', [HealthController::class, 'ping']);

// Firebase configuration endpoints (public for web app)
Route::get('/firebase-config', [FirebaseConfigController::class, 'getConfig']);
Route::get('/vapid-key', [FirebaseConfigController::class, 'getVapidKey']);

// FCM test endpoint (for development/testing)
Route::post('/test-fcm-notification', function (\Illuminate\Http\Request $request) {
    $token = $request->input('token');
    
    if (!$token) {
        return response()->json(['message' => 'Token is required'], 400);
    }
    
    $firebaseService = new \App\Services\FirebaseService();
    $success = $firebaseService->sendMobileTestNotification($token);
    
    if ($success) {
        return response()->json(['message' => 'FCM test notification sent successfully']);
    } else {
        return response()->json(['message' => 'Failed to send FCM test notification'], 500);
    }
});

// General FCM token save endpoint (for testing)
Route::post('/save-fcm-token', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'token' => 'required|string'
    ]);
    
    // For testing, we'll save to the current user if authenticated, or just return success
    if (auth()->check()) {
        auth()->user()->update(['fcm_token' => $request->token]);
        return response()->json(['success' => true, 'message' => 'Token saved to user account']);
    }
    
    // For testing without authentication, just return success
    return response()->json(['success' => true, 'message' => 'Token received (test mode)']);
});

// Backend FCM test endpoint
Route::post('/backend-fcm-test', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'token' => 'required|string',
        'title' => 'required|string',
        'body' => 'required|string'
    ]);
    
    $firebaseService = new \App\Services\FirebaseService();
    $success = $firebaseService->sendToToken(
        $request->token,
        $request->title,
        $request->body,
        [
            'type' => 'announcement',
            'id' => '123'
        ]
    );
    
    if ($success) {
        return response()->json(['message' => 'FCM notification sent successfully! Check the mobile device.']);
    } else {
        return response()->json(['message' => 'Failed to send FCM notification. Check logs for details.'], 500);
    }
});

// Scheduled announcements trigger - For external cron services
Route::post('/scheduled-announcements/publish', [\App\Http\Controllers\Api\ScheduledAnnouncementController::class, 'publishScheduled']);
Route::get('/scheduled-announcements/status', [\App\Http\Controllers\Api\ScheduledAnnouncementController::class, 'status']);

// School info endpoint (public)
Route::get('/school/info', [SchoolController::class, 'info']);

// School rules endpoints (public)
Route::get('/rules', [RulesController::class, 'index']);
Route::get('/rules/{ruleCategory}', [RulesController::class, 'show']);

// Control Panel API - Protected by control_api_auth middleware
Route::prefix('control')->middleware('control_api_auth')->group(function () {
    Route::post('/ping', [ControlApiController::class, 'ping']);
    Route::post('/clear-cache', [ControlApiController::class, 'clearCache']);
    Route::post('/status', [ControlApiController::class, 'status']);
    Route::get('/maintenance-status', [ControlApiController::class, 'maintenanceStatus']);
    Route::post('/maintenance', [ControlApiController::class, 'maintenance']);
    Route::post('/update-modules', [ControlApiController::class, 'updateModules']);
});

// API Version 1
Route::prefix('v1')->group(function () {
    Route::post('/login', LoginController::class);

    // Unified API Routes (for apps that support both teacher and guardian)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [UnifiedAuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [UnifiedAuthController::class, 'logout']);
            Route::get('/profile', [UnifiedAuthController::class, 'profile']);
            Route::post('/change-password', [UnifiedAuthController::class, 'changePassword']);
        });
    });

    // Unified Dashboard Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [UnifiedDashboardController::class, 'index']);
        Route::get('/dashboard/today', [UnifiedDashboardController::class, 'today']);
        Route::get('/dashboard/stats', [UnifiedDashboardController::class, 'stats']);

        // Unified Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [UnifiedNotificationController::class, 'index']);
            Route::get('/unread-count', [UnifiedNotificationController::class, 'unreadCount']);
            Route::post('/mark-all-read', [UnifiedNotificationController::class, 'markAllAsRead']);
            Route::post('/{id}/read', [UnifiedNotificationController::class, 'markAsRead']);
            Route::get('/settings', [UnifiedNotificationController::class, 'getSettings']);
            Route::put('/settings', [UnifiedNotificationController::class, 'updateSettings']);
        });

        // Device Tokens (unified for both roles)
        Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class);
        Route::get('/profile', ProfileController::class);

        // Feedback API for mobile apps
        Route::prefix('feedback')->group(function () {
            Route::post('/', [FeedbackApiController::class, 'store']);
            Route::get('/categories', [FeedbackApiController::class, 'categories']);
            Route::get('/priorities', [FeedbackApiController::class, 'priorities']);
        });

        // Curriculum API for teacher mobile app
        Route::prefix('curriculum')->group(function () {
            Route::get('/subjects/{subjectId}', [\App\Http\Controllers\Api\V1\CurriculumApiController::class, 'getSubjectCurriculum']);
            Route::get('/teacher/subjects', [\App\Http\Controllers\Api\V1\CurriculumApiController::class, 'getTeacherSubjects']);
            Route::get('/teacher/classes', [\App\Http\Controllers\Api\V1\CurriculumApiController::class, 'getTeacherClassesProgress']);
            Route::get('/classes/{classId}/progress', [\App\Http\Controllers\Api\V1\CurriculumApiController::class, 'getClassProgress']);
            Route::post('/topics/{topicId}/progress', [\App\Http\Controllers\Api\V1\CurriculumApiController::class, 'updateProgress']);
        });

        Route::middleware('permission:students.view')->get('/students', function () {
            return ApiResponse::success();
        });
    });

    // Teacher App API Routes
    Route::prefix('teacher')->group(function () {
        Route::get('/school/info', [SchoolController::class, 'info']);
        Route::get('/rules', [RulesController::class, 'index']);
        Route::get('/rules/{ruleCategory}', [RulesController::class, 'show']);

        // Auth routes (public)
        Route::prefix('auth')->group(function () {
            Route::post('/login', [TeacherAuthController::class, 'login']);
        });

        // Forgot Password routes (public)
        Route::prefix('forgot-password')->group(function () {
            Route::post('/verify-identifier', [TeacherForgotPasswordController::class, 'verifyIdentifier']);
            Route::post('/verify-nrc', [TeacherForgotPasswordController::class, 'verifyNrc']);
            Route::post('/verify-otp', [TeacherForgotPasswordController::class, 'verifyOtp']);
            Route::post('/resend-otp', [TeacherForgotPasswordController::class, 'resendOtp']);
            Route::post('/reset', [TeacherForgotPasswordController::class, 'resetPassword']);
        });

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            // Auth
            Route::post('/auth/logout', [TeacherAuthController::class, 'logout']);
            Route::get('/auth/profile', [TeacherAuthController::class, 'profile']);
            Route::post('/auth/change-password', [TeacherAuthController::class, 'changePassword']);

            // Dashboard
            Route::get('/dashboard/stats', [TeacherDashboardController::class, 'stats']);
            Route::get('/today-classes', [TeacherDashboardController::class, 'todayClasses']);
            Route::get('/today-classes/{id}', [TeacherDashboardController::class, 'todayClassDetail']);
            Route::get('/today-classes/{id}/summary', [TeacherDashboardController::class, 'getClassSummary']);
            Route::post('/today-classes/{id}/attendance', [TeacherDashboardController::class, 'takeAttendance']);
            Route::put('/today-classes/{id}/curriculum', [TeacherDashboardController::class, 'updateCurriculum']);
            Route::post('/today-classes/{id}/class-remarks', [TeacherDashboardController::class, 'addClassRemark']);
            Route::post('/today-classes/{id}/student-remarks', [TeacherDashboardController::class, 'addStudentRemark']);
            Route::post('/today-classes/{id}/homework', [TeacherDashboardController::class, 'assignHomework']);
            Route::put('/today-classes/{id}/homework/{homeworkId}/collect', [TeacherDashboardController::class, 'collectHomework']);
            Route::put('/today-classes/{id}/end', [TeacherDashboardController::class, 'endClass']);
            Route::get('/schedule/weekly', [TeacherDashboardController::class, 'weeklySchedule']);
            Route::get('/schedule/full', [TeacherDashboardController::class, 'fullSchedule']);

            // Free Period Activities (KPI)
            Route::get('/free-period/activity-types', [FreePeriodActivityController::class, 'activityTypes']);
            Route::get('/free-period/activities', [FreePeriodActivityController::class, 'index']);
            Route::post('/free-period/activities', [FreePeriodActivityController::class, 'store']);

            // Classes
            Route::get('/classes/dropdown', [TeacherClassController::class, 'dropdown']);
            Route::get('/classes/attendance-dropdown', [TeacherClassController::class, 'attendanceDropdown']);
            Route::get('/classes', [TeacherClassController::class, 'index']);
            Route::get('/classes/{id}', [TeacherClassController::class, 'show']);
            Route::get('/classes/{id}/students', [TeacherClassController::class, 'students']);
            Route::get('/classes/{classId}/students/{studentId}', [TeacherClassController::class, 'studentDetail']);
            Route::get('/classes/{id}/teachers', [TeacherClassController::class, 'teachers']);
            Route::get('/classes/{id}/timetable', [TeacherClassController::class, 'timetable']);
            Route::get('/classes/{id}/rankings', [TeacherClassController::class, 'rankings']);
            Route::get('/classes/{classId}/rankings/{examId}/{studentId}', [TeacherClassController::class, 'classStudentRankingDetails']);
            Route::get('/classes/{id}/exams', [TeacherClassController::class, 'exams']);
            Route::put('/classes/{id}/class-leader', [TeacherClassController::class, 'assignClassLeader']);
            Route::get('/classes/{id}/switch-requests', [TeacherClassController::class, 'switchRequests']);
            Route::post('/classes/{id}/switch-requests', [TeacherClassController::class, 'createSwitchRequest']);
            Route::put('/classes/{id}/switch-requests/{requestId}', [TeacherClassController::class, 'respondToSwitchRequest']);
            Route::get('/classes/{id}/available-teachers', [TeacherClassController::class, 'availableTeachers']);
            Route::get('/classes/{id}/statistics', [TeacherClassController::class, 'statistics']);

            // Student Profile APIs
            Route::get('/students/{id}/profile', [TeacherClassController::class, 'studentProfile']);
            Route::get('/students/{id}/academic', [TeacherClassController::class, 'studentAcademic']);
            Route::get('/students/{id}/attendance', [TeacherClassController::class, 'studentAttendance']);
            Route::get('/students/{id}/remarks', [TeacherClassController::class, 'studentRemarks']);
            Route::get('/students/{id}/rankings', [TeacherClassController::class, 'studentRankings']);
            Route::get('/students/{id}/rankings/{examId}', [TeacherClassController::class, 'studentRankingDetail']);

            // Subjects
            Route::get('/subjects', [TeacherSubjectController::class, 'index']);
            Route::get('/subjects/{id}', [TeacherSubjectController::class, 'show']);
            Route::get('/subjects/{id}/curriculum', [TeacherSubjectController::class, 'curriculum']);
            Route::get('/subjects/{id}/teaching-periods', [TeacherSubjectController::class, 'teachingPeriods']);

            // Attendance
            Route::get('/attendance/students', [TeacherAttendanceController::class, 'students']);
            Route::post('/attendance', [TeacherAttendanceController::class, 'store']);
            Route::post('/attendance/bulk', [TeacherAttendanceController::class, 'bulk']);
            Route::get('/attendance/history', [TeacherAttendanceController::class, 'history']);
            Route::get('/attendance/history/{id}', [TeacherAttendanceController::class, 'historyDetail']);

            // Homework
            Route::get('/homework', [TeacherHomeworkController::class, 'index']);
            Route::post('/homework', [TeacherHomeworkController::class, 'store']);
            Route::get('/homework/{id}', [TeacherHomeworkController::class, 'show']);
            Route::post('/homework/{id}/collect', [TeacherHomeworkController::class, 'collect']);

            // Announcements
            Route::get('/announcements', [TeacherAnnouncementController::class, 'index']);
            Route::get('/announcements/{id}', [TeacherAnnouncementController::class, 'show']);

            // Calendar Events
            Route::get('/calendar/events', [TeacherAnnouncementController::class, 'calendarEvents']);
            Route::get('/calendar/events/{id}', [TeacherAnnouncementController::class, 'eventDetail']);

            // Teacher's Leave Requests
            Route::get('/my-leave-requests', [TeacherLeaveRequestController::class, 'myRequests']);
            Route::post('/my-leave-requests', [TeacherLeaveRequestController::class, 'applyLeave']);
            Route::get('/my-leave-requests/{id}', [TeacherLeaveRequestController::class, 'myRequestDetail']);
            Route::get('/leave-balance', [TeacherLeaveRequestController::class, 'leaveBalance']);

            // Student Leave Requests (for class teacher)
            Route::get('/leave-requests/pending', [TeacherLeaveRequestController::class, 'pendingRequests']);
            Route::get('/leave-requests', [TeacherLeaveRequestController::class, 'studentRequests']);
            Route::post('/leave-requests/{id}/approve', [TeacherLeaveRequestController::class, 'approve']);
            Route::post('/leave-requests/{id}/reject', [TeacherLeaveRequestController::class, 'reject']);

            // Daily Reports
            Route::get('/daily-reports/my-reports', [TeacherDailyReportController::class, 'myReports']);
            Route::get('/daily-reports/received', [TeacherDailyReportController::class, 'receivedReports']);
            Route::get('/daily-reports/recipients', [TeacherDailyReportController::class, 'recipients']);
            Route::post('/daily-reports', [TeacherDailyReportController::class, 'store']);
            Route::get('/daily-reports/{id}', [TeacherDailyReportController::class, 'show']);
            Route::put('/daily-reports/{id}/status', [TeacherDailyReportController::class, 'updateStatus']);

            // Payslips
            Route::get('/payslips', [TeacherPayslipController::class, 'index']);
            Route::get('/payslips/{id}', [TeacherPayslipController::class, 'show']);

            // Class Records
            Route::get('/class-records', [TeacherClassRecordController::class, 'index']);
            Route::get('/class-records/{id}', [TeacherClassRecordController::class, 'show']);

            // Notifications
            Route::post('/notifications', [TeacherNotificationController::class, 'store']);
            Route::get('/notifications', [TeacherNotificationController::class, 'index']);
            Route::get('/notifications/unread-count', [TeacherNotificationController::class, 'unreadCount']);
            Route::get('/notifications/settings', [TeacherNotificationController::class, 'getSettings']);
            Route::put('/notifications/settings', [TeacherNotificationController::class, 'updateSettings']);
            Route::post('/notifications/mark-all-read', [TeacherNotificationController::class, 'markAllAsRead']);
            Route::delete('/notifications/clear-all', [TeacherNotificationController::class, 'clearAll']);
            Route::post('/notifications/{id}/read', [TeacherNotificationController::class, 'markAsRead']);
            Route::delete('/notifications/{id}', [TeacherNotificationController::class, 'destroy']);

            // Exams
            Route::get('/exams', [TeacherExamController::class, 'index']);
            Route::get('/exams/{id}', [TeacherExamController::class, 'show']);
            Route::get('/exams/{id}/results', [TeacherExamController::class, 'results']);
            Route::get('/exams/{id}/results/detailed', [TeacherExamController::class, 'detailedResults']);
            Route::get('/exams/{id}/students', [TeacherExamController::class, 'students']);
            Route::post('/exams/{id}/grades', [TeacherExamController::class, 'submitGrades']);
            Route::put('/exams/{examId}/grades/{studentId}', [TeacherExamController::class, 'updateGrade']);
            Route::post('/exams/{id}/complete', [TeacherExamController::class, 'markCompleted']);

            // Class Remarks
            Route::get('/classes/{classId}/remarks', [TeacherRemarkController::class, 'classRemarks']);
            Route::post('/class-remarks', [TeacherRemarkController::class, 'storeClassRemark']);
            Route::put('/class-remarks/{id}', [TeacherRemarkController::class, 'updateClassRemark']);
            Route::delete('/class-remarks/{id}', [TeacherRemarkController::class, 'deleteClassRemark']);

            // Student Remarks
            Route::get('/classes/{classId}/student-remarks', [TeacherRemarkController::class, 'studentRemarks']);
            Route::post('/student-remarks', [TeacherRemarkController::class, 'storeStudentRemark']);
            Route::put('/student-remarks/{id}', [TeacherRemarkController::class, 'updateStudentRemark']);
            Route::delete('/student-remarks/{id}', [TeacherRemarkController::class, 'deleteStudentRemark']);

            // Activity Summary (for ongoing class)
            Route::get('/classes/{classId}/activity-summary', [TeacherRemarkController::class, 'activitySummary']);

            // Device Tokens (for push notifications)
            Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
            Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        });
    });

    // Guardian App API Routes
    Route::prefix('guardian')->group(function () {
        Route::get('/school/info', [SchoolController::class, 'info']);
        Route::get('/rules', [RulesController::class, 'index']);
        Route::get('/rules/{ruleCategory}', [RulesController::class, 'show']);

        // Auth routes (public)
        Route::prefix('auth')->group(function () {
            Route::post('/login', [GuardianAuthController::class, 'login']);
            Route::post('/forgot-password', [GuardianAuthController::class, 'forgotPassword']);
            Route::post('/resend-otp', [GuardianAuthController::class, 'resendOtp']);
            Route::post('/verify-otp', [GuardianAuthController::class, 'verifyOtp']);
            Route::post('/reset-password', [GuardianAuthController::class, 'resetPassword']);
        });

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            // Auth
            Route::post('/auth/logout', [GuardianAuthController::class, 'logout']);
            Route::get('/auth/profile', [GuardianAuthController::class, 'profile']);
            Route::put('/auth/profile', [GuardianAuthController::class, 'updateProfile']);
            Route::post('/auth/change-password', [GuardianAuthController::class, 'changePassword']);

            // Students (Guardian's children/wards)
            Route::get('/students', [GuardianAuthController::class, 'students']);

            // Dashboard
            Route::get('/home/dashboard', [GuardianDashboardController::class, 'dashboard']);
            Route::get('/dashboard/current-class', [GuardianDashboardController::class, 'currentClass']);
            Route::get('/today-schedule', [GuardianDashboardController::class, 'todaySchedule']);
            Route::get('/upcoming-homework', [GuardianDashboardController::class, 'upcomingHomework']);
            Route::get('/announcements/recent', [GuardianDashboardController::class, 'recentAnnouncements']);
            Route::get('/fee-reminder', [GuardianDashboardController::class, 'feeReminder']);

            // Student Profile & Academic
            Route::get('/students/{id}/profile', [GuardianStudentController::class, 'profile']);
            Route::get('/students/{id}/academic-summary', [GuardianStudentController::class, 'academicSummary']);
            Route::get('/students/{id}/rankings', [GuardianStudentController::class, 'rankings']);
            Route::get('/students/{id}/achievements', [GuardianStudentController::class, 'achievements']);

            // Student Goals
            Route::get('/students/{id}/goals', [GuardianStudentController::class, 'goals']);
            Route::post('/students/{id}/goals', [GuardianStudentController::class, 'createGoal']);
            Route::put('/students/{id}/goals/{goalId}', [GuardianStudentController::class, 'updateGoal']);
            Route::delete('/students/{id}/goals/{goalId}', [GuardianStudentController::class, 'deleteGoal']);

            // Guardian Notes
            Route::get('/students/{id}/notes', [GuardianStudentController::class, 'notes']);
            Route::post('/students/{id}/notes', [GuardianStudentController::class, 'createNote']);
            Route::put('/students/{id}/notes/{noteId}', [GuardianStudentController::class, 'updateNote']);
            Route::delete('/students/{id}/notes/{noteId}', [GuardianStudentController::class, 'deleteNote']);

            // Attendance
            Route::get('/attendance', [GuardianAttendanceController::class, 'index']);
            Route::get('/attendance/summary', [GuardianAttendanceController::class, 'summary']);
            Route::get('/attendance/calendar', [GuardianAttendanceController::class, 'calendar']);
            Route::get('/attendance/stats', [GuardianAttendanceController::class, 'stats']);

            // Exams
            Route::get('/exams', [GuardianExamController::class, 'index']);
            Route::get('/exams/{id}', [GuardianExamController::class, 'show']);
            Route::get('/exams/{id}/results', [GuardianExamController::class, 'results']);

            // Subjects
            Route::get('/subjects', [GuardianExamController::class, 'subjects']);
            Route::get('/subjects/{id}', [GuardianExamController::class, 'subjectDetail']);
            Route::get('/subjects/{id}/performance', [GuardianExamController::class, 'subjectPerformance']);
            Route::get('/subjects/{id}/schedule', [GuardianExamController::class, 'subjectSchedule']);

            // Homework
            Route::get('/homework', [GuardianHomeworkController::class, 'index']);
            Route::get('/homework/stats', [GuardianHomeworkController::class, 'stats']);
            Route::get('/homework/{id}', [GuardianHomeworkController::class, 'show']);
            Route::post('/homework/{id}/submit', [GuardianHomeworkController::class, 'submit']);
            Route::put('/homework/{id}/status', [GuardianHomeworkController::class, 'updateStatus']);

            // Timetable
            Route::get('/timetable', [GuardianTimetableController::class, 'index']);
            Route::get('/timetable/day', [GuardianTimetableController::class, 'day']);
            Route::get('/class-info', [GuardianTimetableController::class, 'classInfo']);
            Route::get('/classes/{id}', [GuardianTimetableController::class, 'classInfo']);

            // Announcements
            Route::get('/announcements', [GuardianAnnouncementController::class, 'index']);
            Route::get('/announcements/{id}', [GuardianAnnouncementController::class, 'show']);
            Route::post('/announcements/{id}/read', [GuardianAnnouncementController::class, 'markAsRead']);
            Route::post('/announcements/mark-all-read', [GuardianAnnouncementController::class, 'markAllAsRead']);

            // Fees & Payments
            Route::prefix('fees')->group(function () {
                Route::get('/', [GuardianFeeController::class, 'index']);
                Route::get('/pending', [GuardianFeeController::class, 'pending']);
                Route::get('/payment-history', [GuardianFeeController::class, 'paymentHistory']);
                Route::get('/{fee_id}', [GuardianFeeController::class, 'show']);
                Route::post('/{fee_id}/payment', [GuardianFeeController::class, 'initiatePayment']);
            });

            // Leave Requests
            Route::get('/leave-requests', [GuardianLeaveRequestController::class, 'index']);
            Route::get('/leave-requests/stats', [GuardianLeaveRequestController::class, 'stats']);
            Route::get('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'show']);
            Route::post('/leave-requests', [GuardianLeaveRequestController::class, 'store']);
            Route::put('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'update']);
            Route::delete('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'destroy']);
            Route::get('/leave-types', [GuardianLeaveRequestController::class, 'leaveTypes']);

            // Notifications
            Route::get('/notifications', [GuardianNotificationController::class, 'index']);
            Route::get('/notifications/unread-count', [GuardianNotificationController::class, 'unreadCount']);
            Route::get('/notifications/settings', [GuardianNotificationController::class, 'getSettings']);
            Route::put('/notifications/settings', [GuardianNotificationController::class, 'updateSettings']);
            Route::post('/notifications/mark-all-read', [GuardianNotificationController::class, 'markAllAsRead']);
            Route::post('/notifications/{id}/read', [GuardianNotificationController::class, 'markAsRead']);

            // Curriculum
            Route::get('/curriculum', [GuardianCurriculumController::class, 'index']);
            Route::get('/curriculum/subjects/{id}', [GuardianCurriculumController::class, 'subjectCurriculum']);
            Route::get('/curriculum/chapters', [GuardianCurriculumController::class, 'chapters']);
            Route::get('/curriculum/chapters/{id}', [GuardianCurriculumController::class, 'chapterDetail']);

            // Report Cards
            Route::get('/report-cards', [GuardianReportCardController::class, 'index']);
            Route::get('/report-cards/{id}', [GuardianReportCardController::class, 'show']);

            // Settings
            Route::get('/settings', [GuardianSettingsController::class, 'index']);
            Route::put('/settings', [GuardianSettingsController::class, 'update']);

            // School Info (authenticated)
            Route::get('/school/info', [GuardianSettingsController::class, 'schoolInfo']);
            Route::get('/school/rules', [GuardianSettingsController::class, 'schoolRules']);
            Route::get('/school/contact', [GuardianSettingsController::class, 'schoolContact']);
            Route::get('/school/facilities', [GuardianSettingsController::class, 'schoolFacilities']);

            // Device Tokens (for push notifications)
            Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
            Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);
        });
    });
});
