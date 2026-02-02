<?php

namespace App\Services\Routers;

use App\Enums\RouterStatus;
use App\Models\Router;
use App\Models\RouterMetric;
use App\Services\Mikrotik\RouterOsApiClient;
use Throwable;

class RouterMetricsCollector
{
    public function collectForRouter(Router $router): ?RouterMetric
    {
        $host = $router->mgmt_host;
        if (!$host) {
            return null;
        }

        $port = (int) ($router->api_port ?? (int) env('MIKROTIK_API_PORT', 8728));
        $timeout = (int) env('MIKROTIK_API_TIMEOUT', 10);

        $username = $router->credentials?->username ?? env('ROUTER_API_USER', 'kaafiye');
        $password = $router->credentials?->password_encrypted ?? env('ROUTER_API_PASS', '');

        $api = new RouterOsApiClient();

        try {
            $api->connect($host, $port, $username, $password, $timeout);

            $res = $api->resource();

            $metric = RouterMetric::create([
                'router_id' => $router->id,
                'cpu_load' => isset($res['cpu-load']) ? (int) $res['cpu-load'] : null,
                'free_memory' => isset($res['free-memory']) ? (int) $res['free-memory'] : null,
                'total_memory' => isset($res['total-memory']) ? (int) $res['total-memory'] : null,
                'free_hdd_space' => isset($res['free-hdd-space']) ? (int) $res['free-hdd-space'] : null,
                'total_hdd_space' => isset($res['total-hdd-space']) ? (int) $res['total-hdd-space'] : null,
                'uptime' => $res['uptime'] ?? null,
                'version' => $res['version'] ?? null,
                'board_name' => $res['board-name'] ?? null,
                'architecture_name' => $res['architecture-name'] ?? null,
                'collected_at' => now(),
            ]);

            // Update router status
            $router->status = RouterStatus::Connected;
            $router->last_seen_at = now();

            // Optional: store model/routeros if empty
            if (!$router->model && isset($res['board-name'])) {
                $router->model = $res['board-name'];
            }
            if (!$router->routeros && isset($res['version'])) {
                $router->routeros = $res['version'];
            }

            $router->save();

            $router->events()->create([
                'type' => 'metrics.collected',
                'payload' => [
                    'cpu' => $metric->cpu_load,
                    'free_memory' => $metric->free_memory,
                ],
                'created_at' => now(),
            ]);

            $api->close();
            return $metric;
        } catch (Throwable $e) {
            try { $api->close(); } catch (Throwable $t) {}

            $router->status = RouterStatus::Offline;
            $router->save();

            $router->events()->create([
                'type' => 'metrics.failed',
                'payload' => [
                    'error' => $e->getMessage(),
                ],
                'created_at' => now(),
            ]);

            return null;
        }
    }
}
