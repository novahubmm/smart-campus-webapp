<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    /**
     * Register device token
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'required|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);
        
        try {
            $user = auth()->user();
            
            // Check if token already exists
            $deviceToken = DeviceToken::where('token', $validated['token'])->first();
            
            if ($deviceToken) {
                // Update existing token
                $deviceToken->update([
                    'user_id' => $user->id,
                    'platform' => $validated['platform'],
                    'device_name' => $validated['device_name'] ?? null,
                    'last_used_at' => now(),
                ]);
                
                Log::info('Device token updated', [
                    'user_id' => $user->id,
                    'token' => substr($validated['token'], 0, 20) . '...',
                ]);
            } else {
                // Create new token
                $deviceToken = DeviceToken::create([
                    'user_id' => $user->id,
                    'token' => $validated['token'],
                    'platform' => $validated['platform'],
                    'device_name' => $validated['device_name'] ?? null,
                    'last_used_at' => now(),
                ]);
                
                Log::info('Device token registered', [
                    'user_id' => $user->id,
                    'token' => substr($validated['token'], 0, 20) . '...',
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Device token registered successfully',
                'data' => [
                    'id' => $deviceToken->id,
                    'platform' => $deviceToken->platform,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Device token registration failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to register device token',
            ], 500);
        }
    }
    
    /**
     * Delete device token
     */
    public function delete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);
        
        try {
            $user = auth()->user();
            
            $deleted = DeviceToken::where('user_id', $user->id)
                ->where('token', $validated['token'])
                ->delete();
            
            if ($deleted) {
                Log::info('Device token deleted', [
                    'user_id' => $user->id,
                    'token' => substr($validated['token'], 0, 20) . '...',
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Device token deleted successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found',
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Device token deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device token',
            ], 500);
        }
    }
}
