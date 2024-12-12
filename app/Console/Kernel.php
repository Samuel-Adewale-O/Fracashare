<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // System Health Monitoring
        $schedule->command('system:monitor')
                ->everyFiveMinutes()
                ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Clean up old logs
        $schedule->command('log:clear --keep-last=30')
                ->daily();

        // Manage proposals
        $schedule->command('proposals:manage')
                ->everyMinute()
                ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}