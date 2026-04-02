<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Router;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class SyncRouterStatuses extends Command
{
    protected $signature = 'routers:sync-status {--minutes=3}';
    protected $description = 'Mark routers online/offline based on last_seen_at';

    public function handle(): int
    {
        $routerTable = (new Router())->getTable();

        if (!Schema::hasColumn($routerTable, 'status') || !Schema::hasColumn($routerTable, 'last_seen_at')) {
            $this->error('routers table must contain status and last_seen_at columns.');
            return self::FAILURE;
        }

        $minutes = (int) $this->option('minutes');
        if ($minutes < 1) {
            $minutes = 3;
        }

        $threshold = Carbon::now()->subMinutes($minutes);

        // Routers seen recently => online
        $onlineCount = Router::query()
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', $threshold)
            ->whereIn('status', ['pending', 'provisioned', 'offline', 'error', 'connected'])
            ->update(['status' => 'online']);

        // Routers stale => offline
        $offlineCount = Router::query()
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', $threshold)
            ->whereIn('status', ['online', 'connected', 'provisioned'])
            ->update(['status' => 'offline']);

        $this->info("Routers marked online: {$onlineCount}");
        $this->info("Routers marked offline: {$offlineCount}");

        return self::SUCCESS;
    }
}