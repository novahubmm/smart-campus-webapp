<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = storage_path('app/firebase-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                Log::warning('Firebase credentials file not found at: ' . $credentialsPath);
                return;
            }

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId(config('services.firebase.project_id'));

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific FCM token
     * Supports both web and mobile platforms
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], string $androidChannelId = 'smartcampus_notifications'): bool
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            // Create notification for both web and mobile
            $notification = Notification::create($title, $body);
            
            // Android config with channel_id for notification categorization
            $androidConfig = AndroidConfig::fromArray([
                'notification' => [
                    'channel_id' => $androidChannelId,
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);
            
            // Web push config (only applies to web browsers)
            $webPushConfig = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => asset('smart-campus-logo.svg'),
                    'badge' => asset('smart-campus-logo.svg'),
                    'tag' => 'staff-notification',
                    'requireInteraction' => false,
                    'silent' => false,
                    'actions' => [
                        [
                            'action' => 'view',
                            'title' => 'View',
                        ]
                    ]
                ],
                'fcm_options' => [
                    'link' => route('staff.notifications.index')
                ]
            ]);

            // Build message with notification and data
            // This creates the format: {"to": "token", "notification": {...}, "data": {...}, "android": {"notification": {"channel_id": "..."}}}
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)  // This creates the "notification" field for mobile
                ->withData($data)                  // This creates the "data" field for mobile
                ->withAndroidConfig($androidConfig) // Android-specific config with channel_id
                ->withWebPushConfig($webPushConfig); // This only applies to web

            $result = $this->messaging->send($message);
            
            // $result is a string containing the message name/id
            $messageId = is_string($result) ? $result : (is_object($result) && method_exists($result, 'name') ? $result->name() : 'unknown');
            
            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'android_channel_id' => $androidChannelId,
                'message_id' => $messageId
            ]);
            
            return true;
        } catch (MessagingException $e) {
            Log::error('FCM messaging error: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
                'error_code' => $e->getCode()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM general error: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple tokens
     */
    public function sendToMultipleTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (!$this->messaging || empty($tokens)) {
            return ['success' => 0, 'failed' => count($tokens)];
        }

        $results = ['success' => 0, 'failed' => 0];

        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [], string $androidChannelId = 'smartcampus_notifications'): bool
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            $notification = Notification::create($title, $body);
            
            // Android config with channel_id
            $androidConfig = AndroidConfig::fromArray([
                'notification' => [
                    'channel_id' => $androidChannelId,
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);
            
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig($androidConfig);

            $this->messaging->send($message);
            
            Log::info('FCM topic notification sent successfully', [
                'topic' => $topic,
                'title' => $title,
                'android_channel_id' => $androidChannelId
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('FCM topic error: ' . $e->getMessage(), [
                'topic' => $topic,
                'title' => $title
            ]);
            return false;
        }
    }

    /**
     * Test method to send mobile-specific notification
     * This creates the exact format: {"to": "device_token", "notification": {...}, "data": {...}, "android": {"notification": {"channel_id": "..."}}}
     */
    public function sendMobileTestNotification(string $token): bool
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            $notification = Notification::create('New Message', 'You have a new notification');
            
            $data = [
                'type' => 'announcement',
                'id' => '123'
            ];
            
            // Android config with channel_id
            $androidConfig = AndroidConfig::fromArray([
                'notification' => [
                    'channel_id' => 'smartcampus_notifications',
                    'title' => 'New Message',
                    'body' => 'You have a new notification',
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);

            // This creates exactly the format the mobile developer requested
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data)
                ->withAndroidConfig($androidConfig);

            $result = $this->messaging->send($message);
            
            // $result is a string containing the message name/id
            $messageId = is_string($result) ? $result : (is_object($result) && method_exists($result, 'name') ? $result->name() : 'unknown');
            
            Log::info('Mobile test notification sent', [
                'token' => substr($token, 0, 20) . '...',
                'message_id' => $messageId,
                'format' => 'Mobile FCM format with notification, data, and android_channel_id'
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Mobile test notification failed: ' . $e->getMessage(), [
                'token' => substr($token, 0, 20) . '...'
            ]);
            return false;
        }
    }
}