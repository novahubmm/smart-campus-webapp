<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FirebaseConfigController extends Controller
{
    /**
     * Get Firebase web app configuration
     */
    public function getConfig(): JsonResponse
    {
        // Real Firebase web config for smart-campus-dafc9 project
        // These are safe to expose publicly as they're client-side config
        $config = [
            'apiKey' => 'AIzaSyAPj9lP2Ho1IIoL_zG9lxkBRMiu1Ps-pj8',
            'authDomain' => 'smart-campus-dafc9.firebaseapp.com',
            'projectId' => 'smart-campus-dafc9',
            'storageBucket' => 'smart-campus-dafc9.firebasestorage.app',
            'messagingSenderId' => '1009023794548',
            'appId' => '1:1009023794548:web:797784460f6b1be58b8021',
            'measurementId' => 'G-RK0GRZHR28'
        ];

        return response()->json($config);
    }

    /**
     * Get VAPID public key
     */
    public function getVapidKey(): JsonResponse
    {
        return response()->json([
            'key' => config('services.vapid.public_key', env('VAPID_PUBLIC_KEY'))
        ]);
    }
}