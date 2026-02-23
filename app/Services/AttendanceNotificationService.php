<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttendanceNotificationService
{
    protected $fcmService;
    
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    
    /**
     * Send attendance notification to guardian
     * 
     * @param string $studentId
     * @param string $status (present, absent, late, leave)
     * @param string|null $date
     * @return void
     */
    public function sendAttendanceNotification(string $studentId, string $status, ?string $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        Log::info('Sending attendance notification', [
            'student_id' => $studentId,
            'status' => $status,
            'date' => $date,
        ]);
        
        // Get student with guardians
        $student = StudentProfile::with(['user', 'guardians.user'])->find($studentId);
        
        if (!$student) {
            Log::warning('Student not found', ['student_id' => $studentId]);
            return;
        }
        
        $guardians = $student->guardians;
        
        if ($guardians->isEmpty()) {
            Log::info('No guardians found for student', [
                'student_id' => $studentId,
                'student_name' => $student->user?->name,
            ]);
            return;
        }
        
        foreach ($guardians as $guardian) {
            $guardianUser = $guardian->user;
            
            if (!$guardianUser) {
                Log::warning('Guardian user not found', ['guardian_id' => $guardian->id]);
                continue;
            }
            
            // Get guardian's FCM tokens
            $tokens = DeviceToken::where('user_id', $guardianUser->id)
                ->pluck('token')
                ->toArray();
            
            if (empty($tokens)) {
                Log::info('No FCM tokens found for guardian', [
                    'guardian_id' => $guardian->id,
                    'user_id' => $guardianUser->id,
                ]);
                continue;
            }
            
            // Prepare notification content
            $title = $this->getAttendanceTitle($status);
            $message = $this->getAttendanceMessage($student->user?->name ?? 'Student', $status);
            
            $data = [
                'type' => 'attendance',
                'student_id' => $student->id,
                'student_name' => $student->user?->name ?? 'Student',
                'status' => $status,
                'date' => $date,
                'timestamp' => now()->toIso8601String(),
            ];
            
            // Send FCM notification
            $result = $this->fcmService->sendToTokens($tokens, $title, $message, $data);
            
            // Save notification to database
            $this->saveNotification($guardianUser->id, $title, $message, $data);
            
            Log::info('Attendance notification sent to guardian', [
                'guardian_id' => $guardian->id,
                'user_id' => $guardianUser->id,
                'tokens_count' => count($tokens),
                'success_count' => $result['success'] ?? 0,
                'failed_count' => $result['failed'] ?? 0,
            ]);
        }
    }
    
    /**
     * Save notification to database
     */
    private function saveNotification(string $userId, string $title, string $message, array $data): void
    {
        try {
            Notification::create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\StudentAttendanceAlert',
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
    
    /**
     * Get attendance notification title based on status
     */
    private function getAttendanceTitle(string $status): string
    {
        return match($status) {
            'present' => '✅ Student Arrived at School',
            'absent' => '⚠️ Student Absent',
            'late' => '⏰ Student Arrived Late',
            'leave' => 'ℹ️ Student on Leave',
            default => '📢 Attendance Update',
        };
    }
    
    /**
     * Get attendance notification message based on status
     */
    private function getAttendanceMessage(string $studentName, string $status): string
    {
        return match($status) {
            'present' => "Your child {$studentName} has arrived at school",
            'absent' => "Your child {$studentName} is marked absent today",
            'late' => "Your child {$studentName} arrived late to school",
            'leave' => "Your child {$studentName} is on leave today",
            default => "Attendance update for {$studentName}: {$status}",
        };
    }
}
