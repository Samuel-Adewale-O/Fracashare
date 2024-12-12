<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

class SystemMonitoringService
{
    private const CACHE_KEY_SYSTEM_STATUS = 'system_status';
    private const CACHE_TTL = 300; // 5 minutes

    public function checkSystemHealth(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY_SYSTEM_STATUS, self::CACHE_TTL, function () {
                return [
                    'status' => 'operational',
                    'database' => $this->checkDatabaseConnection(),
                    'cache' => $this->checkCacheConnection(),
                    'queue' => $this->checkQueueConnection(),
                    'storage' => $this->checkStorageHealth(),
                    'memory_usage' => $this->getMemoryUsage(),
                    'last_checked' => now()->toIso8601String()
                ];
            });
        } catch (Exception $e) {
            Log::error('System health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => 'System health check failed',
                'last_checked' => now()->toIso8601String()
            ];
        }
    }

    private function checkDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            $dbStats = [
                'status' => 'operational',
                'connection' => true,
                'max_connections' => DB::select('SHOW VARIABLES LIKE "max_connections"')[0]->Value,
                'active_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value
            ];
        } catch (Exception $e) {
            Log::error('Database connection check failed', ['error' => $e->getMessage()]);
            $dbStats = [
                'status' => 'error',
                'connection' => false,
                'error' => $e->getMessage()
            ];
        }

        return $dbStats;
    }

    private function checkCacheConnection(): array
    {
        try {
            Cache::put('health_check', true, 10);
            $cacheStatus = Cache::get('health_check') === true;
            Cache::forget('health_check');

            return [
                'status' => $cacheStatus ? 'operational' : 'error',
                'connection' => $cacheStatus
            ];
        } catch (Exception $e) {
            Log::error('Cache connection check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'connection' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkQueueConnection(): array
    {
        try {
            $queueStats = [
                'status' => 'operational',
                'connection' => true,
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'pending_jobs' => DB::table('jobs')->count()
            ];
        } catch (Exception $e) {
            Log::error('Queue connection check failed', ['error' => $e->getMessage()]);
            $queueStats = [
                'status' => 'error',
                'connection' => false,
                'error' => $e->getMessage()
            ];
        }

        return $queueStats;
    }

    private function checkStorageHealth(): array
    {
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = ($usedSpace / $totalSpace) * 100;

            return [
                'status' => $usagePercentage < 90 ? 'operational' : 'warning',
                'total_space' => $totalSpace,
                'free_space' => $freeSpace,
                'used_space' => $usedSpace,
                'usage_percentage' => round($usagePercentage, 2)
            ];
        } catch (Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    private function getMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $peakMemoryUsage = memory_get_peak_usage(true);

        return [
            'current_usage' => $memoryUsage,
            'peak_usage' => $peakMemoryUsage,
            'limit' => $memoryLimit
        ];
    }

    public function logSystemMetrics(): void
    {
        $metrics = [
            'memory_usage' => $this->getMemoryUsage(),
            'storage' => $this->checkStorageHealth(),
            'database' => $this->checkDatabaseConnection(),
            'timestamp' => now()
        ];

        Log::channel('metrics')->info('System metrics', $metrics);
    }
}