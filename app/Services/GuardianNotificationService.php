<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\GuardianProfile;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class GuardianNotificationService
{
    protected $fcmService;
    
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    
    /**
     * Send notification when announcement is created
     */
    public function sendAnnouncementNotification(string $announcementId, string $title, string $content, array $targetGrades = ['all']): void
    {
        Log::info('Sending announcement notification to guardians', [
            'announcement_id' => $announcementId,
            'target_grades' => $targetGrades,
        ]);
        
        // Get all guardians or filter by grades
        $guardians = $this->getGuardiansByGrades($targetGrades);
        
        if ($guardians->isEmpty()) {
            Log::info('No guardians found for announcement notification');
            return;
        }
        
        $notificationTitle = '📢 ' . $title;
        $notificationMessage = $content;
        
        $data = [
            'type' => 'announcement',
            'announcement_id' => $announcementId,
            'title' => $title,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->sendToGuardians($guardians, $notificationTitle, $notificationMessage, $data, 'App\\Notifications\\AnnouncementCreated');
    }
    
    /**
     * Send notification when event is created
     */
    public function sendEventNotification(string $eventId, string $title, string $description, string $startDate, string $endDate): void
    {
        Log::info('Sending event notification to guardians', [
            'event_id' => $eventId,
        ]);
        
        // Get all guardians
        $guardians = $this->getAllGuardians();
        
        if ($guardians->isEmpty()) {
            Log::info('No guardians found for event notification');
            return;
        }
        
        $notificationTitle = '📅 New Event: ' . $title;
        $notificationMessage = $description . ' (' . $startDate . ' - ' . $endDate . ')';
        
        $data = [
            'type' => 'event',
            'event_id' => $eventId,
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->sendToGuardians($guardians, $notificationTitle, $notificationMessage, $data, 'App\\Notifications\\EventCreated');
    }
    
    /**
     * Send notification when exam is created
     */
    public function sendExamNotification(string $examId, string $examName, ?string $gradeId = null, ?string $classId = null, ?string $startDate = null): void
    {
        Log::info('Sending exam notification to guardians', [
            'exam_id' => $examId,
            'grade_id' => $gradeId,
            'class_id' => $classId,
        ]);
        
        // Get guardians based on grade/class
        $guardians = $this->getGuardiansByExam($gradeId, $classId);
        
        if ($guardians->isEmpty()) {
            Log::info('No guardians found for exam notification');
            return;
        }
        
        $notificationTitle = '📝 New Exam: ' . $examName;
        $notificationMessage = 'An exam has been scheduled' . ($startDate ? ' on ' . $startDate : '');
        
        $data = [
            'type' => 'exam',
            'exam_id' => $examId,
            'exam_name' => $examName,
            'start_date' => $startDate,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->sendToGuardians($guardians, $notificationTitle, $notificationMessage, $data, 'App\\Notifications\\ExamScheduled');
    }
    
    /**
     * Send payment reminder notification
     */
    public function sendPaymentReminder(string $studentId, string $studentName, float $amount, string $dueDate): void
    {
        Log::info('Sending payment reminder notification', [
            'student_id' => $studentId,
        ]);
        
        $student = StudentProfile::with(['guardians.user'])->find($studentId);
        
        if (!$student || $student->guardians->isEmpty()) {
            Log::info('No guardians found for payment reminder', ['student_id' => $studentId]);
            return;
        }
        
        $notificationTitle = '💰 Payment Reminder';
        $notificationMessage = "Payment reminder for {$studentName}: MMK " . number_format($amount, 2) . " due by {$dueDate}";
        
        $data = [
            'type' => 'payment_reminder',
            'student_id' => $studentId,
            'student_name' => $studentName,
            'amount' => $amount,
            'due_date' => $dueDate,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $guardians = $student->guardians->map(fn($g) => $g->user)->filter();
        
        $this->sendToGuardians($guardians, $notificationTitle, $notificationMessage, $data, 'App\\Notifications\\FeeReminder');
    }
    
    /**
     * Send first attendance notification (student arrived at school)
     */
    public function sendFirstAttendanceNotification(string $studentId, string $studentName, string $date): void
    {
        Log::info('Sending first attendance notification', [
            'student_id' => $studentId,
            'date' => $date,
        ]);
        
        $student = StudentProfile::with(['guardians.user'])->find($studentId);
        
        if (!$student || $student->guardians->isEmpty()) {
            Log::info('No guardians found for first attendance', ['student_id' => $studentId]);
            return;
        }
        
        $notificationTitle = '✅ Student Arrived at School';
        $notificationMessage = "Your child {$studentName} has arrived at school";
        
        $data = [
            'type' => 'first_attendance',
            'student_id' => $studentId,
            'student_name' => $studentName,
            'date' => $date,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $guardians = $student->guardians->map(fn($g) => $g->user)->filter();
        
        $this->sendToGuardians($guardians, $notificationTitle, $notificationMessage, $data, 'App\\Notifications\\StudentAttendanceAlert');
    }
    
    /**
     * Get all guardians
     */
    private function getAllGuardians(): Collection
    {
        return User::whereHas('guardianProfile')
            ->with('guardianProfile')
            ->get();
    }
    
    /**
     * Get guardians by grades
     */
    private function getGuardiansByGrades(array $targetGrades): Collection
    {
        if (in_array('all', $targetGrades)) {
            return $this->getAllGuardians();
        }
        
        return User::whereHas('guardianProfile.students', function ($query) use ($targetGrades) {
            $query->whereIn('grade_id', $targetGrades);
        })->with('guardianProfile')->get();
    }
    
    /**
     * Get guardians by exam (grade/class)
     */
    private function getGuardiansByExam(?string $gradeId, ?string $classId): Collection
    {
        $query = User::whereHas('guardianProfile.students');
        
        if ($classId) {
            $query->whereHas('guardianProfile.students', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        } elseif ($gradeId) {
            $query->whereHas('guardianProfile.students', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }
        
        return $query->with('guardianProfile')->get();
    }
    
    /**
     * Send notifications to guardians
     */
    private function sendToGuardians(Collection $guardians, string $title, string $message, array $data, string $notificationType): void
    {
        foreach ($guardians as $guardian) {
            try {
                // Get FCM tokens
                $tokens = DeviceToken::where('user_id', $guardian->id)
                    ->pluck('token')
                    ->toArray();
                
                if (empty($tokens)) {
                    Log::info('No FCM tokens found for guardian', ['user_id' => $guardian->id]);
                    continue;
                }
                
                // Send FCM notification
                $result = $this->fcmService->sendToTokens($tokens, $title, $message, $data);
                
                // Save notification to database
                $this->saveNotification($guardian->id, $title, $message, $data, $notificationType);
                
                Log::info('Notification sent to guardian', [
                    'user_id' => $guardian->id,
                    'tokens_count' => count($tokens),
                    'success_count' => $result['success'] ?? 0,
                    'failed_count' => $result['failed'] ?? 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification to guardian', [
                    'error' => $e->getMessage(),
                    'user_id' => $guardian->id,
                ]);
            }
        }
    }
    
    /**
     * Save notification to database
     */
    private function saveNotification(string $userId, string $title, string $message, array $data, string $notificationType): void
    {
        try {
            Notification::create([
                'id' => (string) Str::uuid(),
                'type' => $notificationType,
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $userId,
                'data' => array_merge([
                    'title' => $title,
                    'message' => $message,
                ], $data),
                'read_at' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save notification to database', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }
}
