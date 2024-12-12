<?php

namespace App\Console\Commands;

use App\Services\SystemMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorSystemHealth extends Command
{
    protected $signature = 'system:monitor';
    protected $description = 'Monitor system health and log metrics';

    private SystemMonitoringService $monitoringService;

    public function __construct(SystemMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    public function handle()
    {
        $this->info('Starting system health check...');

        try {
            $healthStatus = $this->monitoringService->checkSystemHealth();
            $this->monitoringService->logSystemMetrics();

            if ($healthStatus['status'] === 'operational') {
                $this->info('System health check completed successfully.');
            } else {
                $this->error('System health check detected issues.');
                Log::error('System health issues detected', $healthStatus);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('System health check failed: ' . $e->getMessage());
            Log::error('System health check command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}