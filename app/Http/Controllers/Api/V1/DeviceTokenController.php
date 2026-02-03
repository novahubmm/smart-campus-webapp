<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    /**
     * Register or update device token for push notifications
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Device token registration request received', [
            'user_id' => $request->user()?->id,
            'platform' => $request->platform,
            'token_prefix' => substr($request->token ?? '', 0, 30),
        ]);

        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Update or create device token
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
                'device_name' => $request->device_name,
                'last_used_at' => now(),
            ]
        );

        Log::info('Device token registered successfully', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'device_token_id' => $deviceToken->id,
            'platform' => $deviceToken->platform,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token registered successfully',
            'data' => [
                'id' => $deviceToken->id,
                'platform' => $deviceToken->platform,
            ],
        ]);
    }

    /**
     * Remove device token (on logout)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $deleted = DeviceToken::where('token', $request->token)
            ->where('user_id', $request->user()->id)
            ->delete();

        Log::info('Device token deletion', [
            'user_id' => $request->user()->id,
            'deleted' => $deleted > 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }
}
