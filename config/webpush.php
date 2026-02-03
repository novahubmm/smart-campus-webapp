<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Configuration
    |--------------------------------------------------------------------------
    |
    | VAPID (Voluntary Application Server Identification) keys are used
    | to identify your application when sending push notifications.
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', env('APP_URL')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default TTL
    |--------------------------------------------------------------------------
    |
    | Time to live (TTL) for push notifications in seconds.
    | Default: 24 hours
    |
    */

    'ttl' => env('WEBPUSH_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Urgency
    |--------------------------------------------------------------------------
    |
    | Urgency hint for push notifications.
    | Options: very-low, low, normal, high
    |
    */

    'urgency' => env('WEBPUSH_URGENCY', 'normal'),
];
