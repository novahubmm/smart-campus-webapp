<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ScheduledAnnouncementController extends Controller
{
    /**
     * Trigger publishing of scheduled announcements
     * This endpoint can be called by external cron services or webhooks
     */
    public function publishScheduled(Request $request): JsonResponse
    {
        try {
            // Validate the trigger token for security
            $token = $request->header('X-Trigger-Token') ?? $request->input('token');
            $expectedToken = config('app.scheduled_announcement_token', 'default-token-change-me');
            
            if ($token !== $expectedToken) {
                Log::warning('Unauthorized attempt to trigger scheduled announcements', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Run the command to publish scheduled announcements
            $exitCode = Artisan::call('announcements:publish-scheduled');
            $output = Artisan::output();

            Log::info('Scheduled announcements trigger executed', [
                'exit_code' => $exitCode,
                'output' => $output,
                'triggered_by' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheduled announcements processed',
                'exit_code' => $exitCode,
                'output' => trim($output),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process scheduled announcements', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process scheduled announcements',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status of scheduled announcements
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $token = $request->header('X-Trigger-Token') ?? $request->input('token');
            $expectedToken = config('app.scheduled_announcement_token', 'default-token-change-me');
            
            if ($token !== $expectedToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Get scheduled announcements that are due
            $scheduledAnnouncements = \App\Models\Announcement::where('is_published', false)
                ->where('status', true)
                ->whereDate('publish_date', '<=', now())
                ->whereNotNull('publish_date')
                ->get(['id', 'title', 'publish_date', 'target_roles']);

            // Get upcoming scheduled announcements
            $upcomingAnnouncements = \App\Models\Announcement::where('is_published', false)
                ->where('status', true)
                ->whereDate('publish_date', '>', now())
                ->whereNotNull('publish_date')
                ->orderBy('publish_date')
                ->limit(10)
                ->get(['id', 'title', 'publish_date', 'target_roles']);

            return response()->json([
                'success' => true,
                'data' => [
                    'due_for_publishing' => $scheduledAnnouncements->map(function ($announcement) {
                        return [
                            'id' => $announcement->id,
                            'title' => $announcement->title,
                            'publish_date' => $announcement->publish_date->format('Y-m-d H:i:s'),
                            'target_roles' => $announcement->target_roles,
                            'days_overdue' => now()->diffInDays($announcement->publish_date, false),
                        ];
                    }),
                    'upcoming' => $upcomingAnnouncements->map(function ($announcement) {
                        return [
                            'id' => $announcement->id,
                            'title' => $announcement->title,
                            'publish_date' => $announcement->publish_date->format('Y-m-d H:i:s'),
                            'target_roles' => $announcement->target_roles,
                            'days_until' => now()->diffInDays($announcement->publish_date),
                        ];
                    }),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}