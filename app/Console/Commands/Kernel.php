<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register custom Artisan commands
     */
    protected $commands = [
        \App\Console\Commands\CheckSubscriptions::class,
        \App\Console\Commands\ExpireSubscriptions::class,
    ];

    /**
     * Define the application's command schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        // Hubinta subscriptions (5 daqiiqo kasta)
        $schedule->command('subscriptions:check')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Expire subscriptions (daqiiqad kasta)
        $schedule->command('subscriptions:expire')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register commands for the application
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
