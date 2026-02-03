<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\GuardianProfile;
use App\Models\StaffProfile;
use App\Services\FirebaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAnnouncementNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public array $targetRoles,
        public array $targetGrades = ['all'],
        public array $targetDepartments = ['all']
    ) {}

    public function handle(): void
    {
        Log::info('Processing announcement notifications', [
            'announcement_id' => $this->announcement->id,
            'target_roles' => $this->targetRoles,
            'target_grades' => $this->targetGrades,
            'target_departments' => $this->targetDepartments,
        ]);

        if (in_array('teacher', $this->targetRoles)) {
            $this->sendToTeachers();
        }

        if (in_array('staff', $this->targetRoles)) {
            $this->sendToStaff();
        }

        if (in_array('guardian', $this->targetRoles)) {
            $this->sendToGuardians();
        }
    }

    /**
     * Send notifications to teachers filtered by grades
     */
    private function sendToTeachers(): void
    {
        try {
            $query = User::role('teacher')->where('is_active', true);
            
            // Filter by grades if not "all"
            if (!in_array('all', $this->targetGrades) && !empty($this->targetGrades)) {
                $gradeIds = $this->targetGrades;
                $query->whereHas('teacherProfile', function ($q) use ($gradeIds) {
                    // current_grades is a JSON array of grade IDs
                    $q->where(function ($subQ) use ($gradeIds) {
                        foreach ($gradeIds as $gradeId) {
                            $subQ->orWhereJsonContains('current_grades', $gradeId);
                        }
                    });
                });
            }

            $users = $query->get();
            $this->sendNotificationsToUsers($users, 'teacher', ['ios', 'android']);

        } catch (\Exception $e) {
            Log::error('Failed to send teacher notifications', [
                'announcement_id' => $this->announcement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notifications to staff filtered by departments
     */
    private function sendToStaff(): void
    {
        try {
            $query = User::role('staff')->where('is_active', true);
            
            // Filter by departments if not "all"
            if (!in_array('all', $this->targetDepartments) && !empty($this->targetDepartments)) {
                $departmentIds = $this->targetDepartments;
                $query->whereHas('staffProfile', function ($q) use ($departmentIds) {
                    $q->whereIn('department_id', $departmentIds);
                });
            }

            $users = $query->get();
            $this->sendNotificationsToUsers($users, 'staff', ['web']);

        } catch (\Exception $e) {
            Log::error('Failed to send staff notifications', [
                'announcement_id' => $this->announcement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notifications to guardians filtered by grades (via their students)
     */
    private function sendToGuardians(): void
    {
        try {
            $query = User::role('guardian')->where('is_active', true);
            
            // Filter by grades if not "all" (via students)
            if (!in_array('all', $this->targetGrades) && !empty($this->targetGrades)) {
                $gradeIds = $this->targetGrades;
                $query->whereHas('guardianProfile.students', function ($q) use ($gradeIds) {
                    $q->whereIn('grade_id', $gradeIds);
                });
            }

            $users = $query->get();
            $this->sendNotificationsToUsers($users, 'guardian', ['ios', 'android']);

        } catch (\Exception $e) {
            Log::error('Failed to send guardian notifications', [
                'announcement_id' => $this->announcement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notifications to a collection of users
     */
    private function sendNotificationsToUsers($users, string $role, array $platforms): void
    {
        $fcmTokens = [];
        $notificationData = [
            'title' => $this->announcement->title,
            'message' => Str::limit(strip_tags($this->announcement->content), 200),
            'announcement_id' => $this->announcement->id,
            'priority' => $this->announcement->priority ?? 'medium',
            'type' => 'announcement',
        ];

        // Create database notifications and collect FCM tokens
        foreach ($users as $user) {
            // Create database notification for each user
            Notification::create([
                'id' => Str::uuid(),
                'type' => 'App\\Notifications\\AnnouncementNotification',
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'data' => $notificationData,
                'read_at' => null,
            ]);

            // Collect FCM tokens for this user
            $userTokens = DeviceToken::where('user_id', $user->id)
                ->whereIn('platform', $platforms)
                ->pluck('token')
                ->toArray();
            
            $fcmTokens = array_merge($fcmTokens, $userTokens);
        }

        // Remove duplicates and empty values
        $fcmTokens = array_filter(array_unique($fcmTokens));

        Log::info("Created notifications for {$role}", [
            'announcement_id' => $this->announcement->id,
            'user_count' => $users->count(),
            'token_count' => count($fcmTokens)
        ]);

        // Send FCM push notifications
        if (!empty($fcmTokens)) {
            $firebaseService = new FirebaseService();
            $title = $this->announcement->title;
            $body = Str::limit(strip_tags($this->announcement->content), 100);
            
            $results = $firebaseService->sendToMultipleTokens(
                $fcmTokens, 
                $title, 
                $body,
                [
                    'announcement_id' => (string) $this->announcement->id,
                    'type' => 'announcement',
                    'priority' => $this->announcement->priority ?? 'medium',
                ]
            );

            Log::info("Sent FCM notifications to {$role}", [
                'announcement_id' => $this->announcement->id,
                'total_tokens' => count($fcmTokens),
                'success' => $results['success'],
                'failed' => $results['failed']
            ]);
        }
    }
}
