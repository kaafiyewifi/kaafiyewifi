<?php

namespace App\Console;

use App\Jobs\Routers\CollectRouterMetricsJob;
use App\Jobs\Routers\PollRouterStatusJob;
use App\Models\Router;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /*
        |--------------------------------------------------------------------------
        | Router Monitoring Scheduler (Status)
        |--------------------------------------------------------------------------
        | - Polls MikroTik routers every minute
        | - Dispatches jobs to queue (NON-blocking)
        |--------------------------------------------------------------------------
        */
        $schedule->call(function (): void {
            Router::query()
                ->whereNotNull('mgmt_host')
                ->whereIn('status', ['connected', 'offline', 'provisioning'])
                ->pluck('id')
                ->each(fn ($routerId) => PollRouterStatusJob::dispatch((int) $routerId));
        })
        ->name('poll-mikrotik-routers-status')
        ->everyMinute()
        ->onOneServer()
        ->withoutOverlapping(5);

        /*
        |--------------------------------------------------------------------------
        | Router Metrics Scheduler (CPU/RAM/Uptime)
        |--------------------------------------------------------------------------
        | - Collects metrics every minute
        | - Stores in router_metrics table
        |--------------------------------------------------------------------------
        */
        $schedule->call(function (): void {
            Router::query()
                ->whereNotNull('mgmt_host')
                ->whereIn('status', ['connected', 'offline', 'provisioning'])
                ->pluck('id')
                ->each(fn ($routerId) => CollectRouterMetricsJob::dispatch((int) $routerId));
        })
        ->name('poll-mikrotik-routers-metrics')
        ->everyMinute()
        ->onOneServer()
        ->withoutOverlapping(5);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
