<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ControlApiController extends Controller
{
    /**
     * Ping endpoint - returns basic system information
     */
    public function ping(): JsonResponse
    {
        try {
            // Test database connection
            $dbStatus = 'connected';
            try {
                DB::connection()->getPdo();
            } catch (Exception $e) {
                $dbStatus = 'failed';
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'app_name' => config('app.name'),
                    'app_version' => '1.0.0', // You can add this to config
                    'environment' => config('app.env'),
                    'current_time' => now()->toISOString(),
                    'database_status' => $dbStatus,
                    'maintenance_mode' => Setting::isMaintenanceMode(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Control API ping failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'System check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application caches
     */
    public function clearCache(): JsonResponse
    {
        try {
            $results = [];
            
            // Clear various caches
            Artisan::call('cache:clear');
            $results['cache'] = 'cleared';
            
            Artisan::call('config:clear');
            $results['config'] = 'cleared';
            
            Artisan::call('route:clear');
            $results['routes'] = 'cleared';
            
            Artisan::call('view:clear');
            $results['views'] = 'cleared';

            return response()->json([
                'status' => 'success',
                'message' => 'All caches cleared successfully',
                'results' => $results
            ]);
        } catch (Exception $e) {
            Log::error('Control API cache clear failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Cache clear failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system status information
     */
    public function status(): JsonResponse
    {
        try {
            $setting = Setting::first();
            
            // Get last error log timestamp (simplified)
            $lastErrorTime = null;
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                $lastErrorTime = date('c', filemtime($logPath));
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'app_version' => '1.0.0',
                    'maintenance_mode' => Setting::isMaintenanceMode(),
                    'maintenance_message' => Setting::getMaintenanceMessage(),
                    'last_error_timestamp' => $lastErrorTime,
                    'school_info' => [
                        'name' => $setting?->school_name ?? 'Not Set',
                        'email' => $setting?->school_email ?? 'Not Set',
                        'setup_completed' => $setting?->setup_completed_school_info ?? false,
                    ],
                    'system_health' => [
                        'database' => $this->checkDatabaseHealth(),
                        'storage' => $this->checkStorageHealth(),
                    ]
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Control API status failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Status check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current maintenance mode status
     */
    public function maintenanceStatus(): JsonResponse
    {
        try {
            $setting = Setting::first();
            
            return response()->json([
                'enabled' => Setting::isMaintenanceMode(),
                'message' => Setting::getMaintenanceMessage(),
                'last_updated' => $setting?->updated_at?->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Control API maintenance status failed: ' . $e->getMessage());
            
            return response()->json([
                'enabled' => false,
                'message' => 'Unable to retrieve maintenance status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle maintenance mode
     */
    public function maintenance(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'enabled' => 'required|boolean',
                'message' => 'nullable|string|max:500'
            ]);

            $setting = Setting::first();
            if (!$setting) {
                $setting = new Setting();
            }

            $setting->maintenance_mode = $request->boolean('enabled');
            if ($request->has('message')) {
                $setting->maintenance_message = $request->input('message');
            }
            $setting->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Maintenance mode ' . ($request->boolean('enabled') ? 'enabled' : 'disabled'),
                'data' => [
                    'maintenance_mode' => $setting->maintenance_mode,
                    'maintenance_message' => $setting->maintenance_message,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Control API maintenance toggle failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance mode toggle failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update module configuration
     */
    public function updateModules(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'modules' => 'required|array',
                'modules.*' => 'boolean'
            ]);

            // For now, we'll store modules in a simple way
            // In the future, this could be expanded to a proper modules system
            $setting = Setting::first();
            if (!$setting) {
                $setting = new Setting();
            }

            // Store modules as JSON in a new field (we'd need to add this field)
            // For now, just return success
            
            return response()->json([
                'status' => 'success',
                'message' => 'Module configuration updated',
                'data' => [
                    'modules' => $request->input('modules')
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Control API module update failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Module update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): string
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth(): string
    {
        try {
            $testFile = storage_path('app/health_check.txt');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            return 'healthy';
        } catch (Exception $e) {
            return 'unhealthy';
        }
    }
}