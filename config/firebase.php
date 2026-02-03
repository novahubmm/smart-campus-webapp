<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Firebase project credentials here.
    | You can get these from Firebase Console > Project Settings > Cloud Messaging
    |
    */

    'credentials' => [
        // Path to Firebase service account JSON file (recommended)
        'file' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    ],

    // Firebase project ID
    'project_id' => env('FIREBASE_PROJECT_ID'),

    // FCM API endpoint (v1 API)
    'fcm_url' => 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send',

    // Default notification settings
    'notification' => [
        'icon' => env('FCM_NOTIFICATION_ICON', '/icons/icon-192x192.png'),
        'color' => env('FCM_NOTIFICATION_COLOR', '#4F46E5'),
        'sound' => env('FCM_NOTIFICATION_SOUND', 'default'),
    ],
];
