<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class HealthController extends Controller
{
    /**
     * Get system health status
     */
    public function index(): JsonResponse
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'checks' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Database connection successful'
            ];
        } catch (Exception $e) {
            $health['status'] = 'error';
            $health['checks']['database'] = [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Cache check
        try {
            Cache::put('health_check', 'ok', 60);
            $cacheValue = Cache::get('health_check');
            
            if ($cacheValue === 'ok') {
                $health['checks']['cache'] = [
                    'status' => 'ok',
                    'message' => 'Cache is working'
                ];
            } else {
                $health['status'] = 'warning';
                $health['checks']['cache'] = [
                    'status' => 'warning',
                    'message' => 'Cache read/write issue'
                ];
            }
        } catch (Exception $e) {
            $health['status'] = 'warning';
            $health['checks']['cache'] = [
                'status' => 'warning',
                'message' => 'Cache check failed: ' . $e->getMessage()
            ];
        }

        // Storage check
        try {
            $testFile = storage_path('app/health_check.txt');
            file_put_contents($testFile, 'health check');
            
            if (file_exists($testFile) && file_get_contents($testFile) === 'health check') {
                unlink($testFile);
                $health['checks']['storage'] = [
                    'status' => 'ok',
                    'message' => 'Storage is writable'
                ];
            } else {
                $health['status'] = 'warning';
                $health['checks']['storage'] = [
                    'status' => 'warning',
                    'message' => 'Storage write/read issue'
                ];
            }
        } catch (Exception $e) {
            $health['status'] = 'warning';
            $health['checks']['storage'] = [
                'status' => 'warning',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }

        // Maintenance mode check
        if (app()->isDownForMaintenance()) {
            $health['status'] = 'maintenance';
            $health['checks']['maintenance'] = [
                'status' => 'maintenance',
                'message' => 'Application is in maintenance mode'
            ];
        } else {
            $health['checks']['maintenance'] = [
                'status' => 'ok',
                'message' => 'Application is operational'
            ];
        }

        // System info
        $health['system'] = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'uptime' => $this->getUptime(),
        ];

        // Set appropriate HTTP status code
        $httpStatus = match($health['status']) {
            'ok' => 200,
            'warning' => 200, // Still operational
            'maintenance' => 503,
            'error' => 503,
            default => 200
        };

        return response()->json($health, $httpStatus);
    }

    /**
     * Simple ping endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'pong',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get system uptime (approximation)
     */
    private function getUptime(): string
    {
        if (function_exists('sys_getloadavg') && is_readable('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = floatval(explode(' ', $uptime)[0]);
            
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        }
        
        return 'Unknown';
    }
}