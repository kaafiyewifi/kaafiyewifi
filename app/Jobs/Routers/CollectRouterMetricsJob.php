<?php

// app/Jobs/Routers/CollectRouterMetricsJob.php
namespace App\Jobs\Routers;

use App\Models\Router;
use App\Models\RouterMetric;
use App\Services\Routers\Contracts\RouterApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CollectRouterMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $routerId) {}

    public function handle(RouterApi $api): void
    {
        $router = Router::findOrFail($this->routerId);

        $api->connect($router);
        $rows = $api->query('/system/resource/print');
        $api->disconnect();

        $r = $rows[0] ?? [];

        RouterMetric::create([
            'router_id'   => $router->id,
            'cpu_load'    => isset($r['cpu-load']) ? (int)$r['cpu-load'] : null,
            'mem_total'   => isset($r['total-memory']) ? (int)$r['total-memory'] : null,
            'mem_free'    => isset($r['free-memory']) ? (int)$r['free-memory'] : null,
            'uptime'      => $this->uptimeToSeconds($r['uptime'] ?? null),
            'collected_at'=> now(),
        ]);

        // 🔒 Long-term performance: keep last 7 days only
        RouterMetric::where('router_id', $router->id)
            ->where('collected_at', '<', now()->subDays(7))
            ->delete();
    }

    private function uptimeToSeconds(?string $uptime): ?int
    {
        if (!$uptime) return null;

        $map = ['w'=>604800,'d'=>86400,'h'=>3600,'m'=>60,'s'=>1];
        $sec = 0;

        preg_match_all('/(\d+)([wdhms])/', $uptime, $m, PREG_SET_ORDER);
        foreach ($m as $p) {
            $sec += ((int)$p[1]) * ($map[$p[2]] ?? 0);
        }

        return $sec;
    }
}
