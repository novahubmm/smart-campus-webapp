<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ControlPanelService
{
    private string $baseUrl;
    private string $schoolToken;

    public function __construct()
    {
        $this->baseUrl = config('app.control_panel_base_url', env('CONTROL_PANEL_BASE_URL'));
        $this->schoolToken = config('app.control_panel_school_token', env('CONTROL_PANEL_SCHOOL_TOKEN'));
    }

    /**
     * Send feedback to Control Panel
     */
    public function sendFeedback(array $feedbackData): bool
    {
        try {
            if (!$this->baseUrl || !$this->schoolToken) {
                Log::warning('Control Panel integration not configured');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->schoolToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/feedback-from-school', [
                'school_identifier' => config('app.name', 'Smart Campus'),
                'school_url' => config('app.url'),
                'source' => $feedbackData['source'] ?? 'web',
                'user_name' => $feedbackData['user_name'] ?? null,
                'user_email' => $feedbackData['user_email'] ?? null,
                'user_role' => $feedbackData['user_role'] ?? 'unknown',
                'title' => $feedbackData['title'],
                'message' => $feedbackData['message'],
                'priority' => $feedbackData['priority'] ?? 'normal',
                'category' => $feedbackData['category'] ?? 'general',
                'submitted_at' => now()->toISOString(),
            ]);

            if ($response->successful()) {
                Log::info('Feedback sent to Control Panel successfully');
                return true;
            } else {
                Log::error('Failed to send feedback to Control Panel', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (Exception $e) {
            Log::error('Error sending feedback to Control Panel: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Report system status to Control Panel
     */
    public function reportStatus(array $statusData): bool
    {
        try {
            if (!$this->baseUrl || !$this->schoolToken) {
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->schoolToken,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/api/school-status', [
                'school_identifier' => config('app.name', 'Smart Campus'),
                'school_url' => config('app.url'),
                'status' => $statusData['status'] ?? 'active',
                'last_activity' => now()->toISOString(),
                'system_info' => $statusData,
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Error reporting status to Control Panel: ' . $e->getMessage());
            return false;
        }
    }
}