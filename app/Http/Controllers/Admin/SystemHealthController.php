<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemHealthController extends Controller
{
    private SystemMonitoringService $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
        $this->monitoringService = $monitoringService;
    }

    public function status()
    {
        try {
            $healthStatus = $this->monitoringService->checkSystemHealth();

            return response()->json([
                'status' => 'success',
                'data' => $healthStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve system health status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve system health status'
            ], 500);
        }
    }

    public function logs(Request $request)
    {
        $request->validate([
            'channel' => 'nullable|string|in:daily,slack,metrics',
            'level' => 'nullable|string|in:emergency,alert,critical,error,warning,notice,info,debug',
            'date' => 'nullable|date',
            'search' => 'nullable|string'
        ]);

        try {
            $logPath = storage_path('logs/laravel-' . ($request->date ?? now()->format('Y-m-d')) . '.log');
            
            if (!file_exists($logPath)) {
                return response()->json([
                    'status' => 'success',
                    'data' => []
                ]);
            }

            $logs = collect(file($logPath))
                ->filter(function ($line) use ($request) {
                    return empty($request->level) || str_contains($line, "].$request->level:");
                })
                ->when($request->search, function ($collection, $search) {
                    return $collection->filter(fn($line) => str_contains(strtolower($line), strtolower($search)));
                })
                ->values();

            return response()->json([
                'status' => 'success',
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve system logs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve system logs'
            ], 500);
        }
    }
}