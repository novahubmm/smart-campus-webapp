<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FcmService
{
    private ?string $accessToken = null;

    /**
     * Send push notification to a user's devices
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
        
        if (empty($tokens)) {
            return ['success' => false, 'message' => 'No device tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to multiple tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($tokens as $token) {
            $result = $this->sendToToken($token, $title, $body, $data);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $result['error'] ?? 'Unknown error';
                
                // Remove invalid tokens
                if (isset($result['invalid_token']) && $result['invalid_token']) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }

        return $results;
    }

    /**
     * Send push notification to a single token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return ['success' => false, 'error' => 'Failed to get access token'];
            }

            $projectId = config('firebase.project_id');
            if (!$projectId) {
                return ['success' => false, 'error' => 'Firebase project ID not configured'];
            }

            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map('strval', $data),
                    'android' => [
                        'notification' => [
                            'icon' => config('firebase.notification.icon'),
                            'color' => config('firebase.notification.color'),
                            'sound' => config('firebase.notification.sound'),
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => config('firebase.notification.sound'),
                            ],
                        ],
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon' => config('firebase.notification.icon'),
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post(config('firebase.fcm_url'), $message);

            if ($response->successful()) {
                return ['success' => true];
            }

            $error = $response->json();
            $errorCode = $error['error']['details'][0]['errorCode'] ?? null;
            
            // Check for invalid token errors
            $invalidTokenErrors = ['UNREGISTERED', 'INVALID_ARGUMENT'];
            $isInvalidToken = in_array($errorCode, $invalidTokenErrors);

            Log::warning('FCM send failed', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Unknown error',
                'invalid_token' => $isInvalidToken,
            ];
        } catch (\Exception $e) {
            Log::error('FCM exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get OAuth2 access token for FCM v1 API
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $credentialsPath = config('firebase.credentials.file');
            
            if (!file_exists($credentialsPath)) {
                Log::error('Firebase credentials file not found', ['path' => $credentialsPath]);
                return null;
            }

            $credentials = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                json_decode(file_get_contents($credentialsPath), true)
            );

            $token = $credentials->fetchAuthToken();
            $this->accessToken = $token['access_token'] ?? null;

            return $this->accessToken;
        } catch (\Exception $e) {
            Log::error('Failed to get FCM access token', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
