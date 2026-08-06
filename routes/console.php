<?php

use App\Jobs\Routers\CollectRouterMetricsJob;
use App\Jobs\Routers\PollRouterStatusJob;
use App\Models\Router;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Here you may define all of your Closure based console commands.
| Each Closure is bound to a command instance allowing a simple
| approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
|
| Laravel 11 loads the schedule from THIS file (see bootstrap/app.php
| `commands:` argument). app/Console/Kernel.php is never booted, so every
| task must be registered here.
|
| Driven by a single cron entry (/etc/cron.d/kaafiye):
|     * * * * * www-data php /var/www/kaafiye/artisan schedule:run
|
*/

/**
 * Statuses that still deserve an active poll. `online` is legacy data written
 * by older routers:sync-status runs; keep it so those rows are not stranded.
 */
$pollableStatuses = ['connected', 'online', 'offline', 'provisioning', 'provisioned', 'error'];

/*
|--------------------------------------------------------------------------
| Router Monitoring – Status
|--------------------------------------------------------------------------
| Pings every managed router. Dispatched to the queue so a dead router's
| timeout never blocks the scheduler.
*/
Schedule::call(function () use ($pollableStatuses): void {
    Router::query()
        ->whereNotNull('mgmt_host')
        ->whereIn('status', $pollableStatuses)
        ->pluck('id')
        ->each(fn ($routerId) => PollRouterStatusJob::dispatch((int) $routerId));
})
    ->name('poll-mikrotik-routers-status')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping(5);

/*
|--------------------------------------------------------------------------
| Router Monitoring – Metrics (CPU/RAM/Uptime)
|--------------------------------------------------------------------------
*/
Schedule::call(function () use ($pollableStatuses): void {
    Router::query()
        ->whereNotNull('mgmt_host')
        ->whereIn('status', $pollableStatuses)
        ->pluck('id')
        ->each(fn ($routerId) => CollectRouterMetricsJob::dispatch((int) $routerId));
})
    ->name('poll-mikrotik-routers-metrics')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping(5);

/*
|--------------------------------------------------------------------------
| Router Monitoring – Heartbeat reconciliation
|--------------------------------------------------------------------------
| Marks routers offline when last_seen_at goes stale (heartbeat/callback).
*/
Schedule::command('routers:sync-status')
    ->name('routers-sync-status')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping(5);

/*
|--------------------------------------------------------------------------
| Subscription Expiry
|--------------------------------------------------------------------------
| Expires finished subscriptions, disables radius access + clears speed.
*/
Schedule::command('subscriptions:check')
    ->name('expire-finished-subscriptions')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping(5);

/*
|--------------------------------------------------------------------------
| Radius Session Cleanup
|--------------------------------------------------------------------------
| Enforces customer device limits, disconnects extra active sessions.
*/
Schedule::command('radius:cleanup-sessions')
    ->name('radius-cleanup-sessions')
    ->everyMinute()
    ->onOneServer()
    ->withoutOverlapping(5);
