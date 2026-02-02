<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\Routers\RouterMetricsCollector;
use Illuminate\Console\Command;

class CollectRouterMetrics extends Command
{
    protected $signature = 'routers:collect-metrics {--limit=200}';
    protected $description = 'Collect RouterOS metrics (CPU/RAM/Uptime/Version) for connected routers';

    public function handle(RouterMetricsCollector $collector): int
    {
        $limit = (int) $this->option('limit');

        $routers = Router::query()
            ->orderByDesc('last_seen_at')
            ->limit($limit)
            ->get();

        $ok = 0;
        $fail = 0;

        foreach ($routers as $router) {
            $metric = $collector->collectForRouter($router);
            if ($metric) $ok++;
            else $fail++;
        }

        $this->info("Metrics collected: OK={$ok}, FAIL={$fail}");
        return self::SUCCESS;
    }
}
