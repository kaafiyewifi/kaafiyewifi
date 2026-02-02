<?php

namespace App\Services\Routers;

use App\Models\Router;
use Illuminate\Support\Facades\DB;
use Throwable;

class RouterHealthService
{
    public function testConnection(Router $router): array
    {
        $host = $router->mgmt_ip ?: $router->public_ip;

        if (!$host) {
            $this->log($router->id, 'error', 'api.test', 'Missing host (mgmt_ip/public_ip)');
            $this->statusCheck($router->id, 'api', false, null, 'Missing host');
            $router->forceFill(['status' => 'failed', 'last_error' => 'Missing host'])->save();

            return ['ok' => false, 'message' => 'Set mgmt_ip or public_ip first.'];
        }

        // haddii mgmt_ip uu leeyahay /24, ka jar
        if (str_contains($host, '/')) {
            $host = explode('/', $host)[0];
        }

        if (!$router->api_user || !$router->api_pass_enc) {
            $this->log($router->id, 'error', 'api.test', 'Missing api_user/api_pass_enc');
            $this->statusCheck($router->id, 'api', false, null, 'Missing API credentials');
            $router->forceFill(['status' => 'failed', 'last_error' => 'Missing API credentials'])->save();

            return ['ok' => false, 'message' => 'Provision router first (api_user/api_pass missing).'];
        }

        $pass = decrypt($router->api_pass_enc);
        $port = (int) ($router->api_port ?: 8728);

        $t0 = microtime(true);

        try {
            // RouterOS client (Laravel facade from evilfreelancer/routeros-api-php)
            $client = \RouterOS::client([
                'host' => $host,
                'user' => $router->api_user,
                'pass' => $pass,
                'port' => $port,
                'timeout' => 3, // seconds
            ]);

            // Simple safe read
            $res = $client->query('/system/identity/print')->read();

            $latency = (int) round((microtime(true) - $t0) * 1000);

            $router->forceFill([
                'status' => 'connected',
                'last_seen_at' => now(),
                'last_error' => null,
            ])->save();

            $this->statusCheck($router->id, 'api', true, $latency, null);
            $this->log($router->id, 'info', 'api.test', 'API connection OK', [
                'host' => $host,
                'port' => $port,
                'latency_ms' => $latency,
            ]);

            return [
                'ok' => true,
                'message' => 'Connected successfully.',
                'latency_ms' => $latency,
                'identity' => $res[0]['name'] ?? null,
            ];
        } catch (Throwable $e) {
            $latency = (int) round((microtime(true) - $t0) * 1000);

            $router->forceFill([
                'status' => 'offline',
                'last_error' => $e->getMessage(),
            ])->save();

            $this->statusCheck($router->id, 'api', false, $latency, $e->getMessage());
            $this->log($router->id, 'error', 'api.test', 'API connection FAILED', [
                'host' => $host,
                'port' => $port,
                'latency_ms' => $latency,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'latency_ms' => $latency,
                'host' => $host,
                'port' => $port,
            ];
        }
    }

    private function statusCheck(int $routerId, string $type, bool $ok, ?int $latencyMs, ?string $error): void
    {
        DB::table('router_status_checks')->insert([
            'router_id' => $routerId,
            'check_type' => $type,
            'is_ok' => $ok ? 1 : 0,
            'latency_ms' => $latencyMs,
            'error' => $error,
            'created_at' => now(),
        ]);
    }

    private function log(int $routerId, string $level, string $action, string $message, array $context = []): void
    {
        unset($context['pass'], $context['password']);

        DB::table('router_logs')->insert([
            'router_id' => $routerId,
            'level' => $level,
            'action' => $action,
            'message' => $message,
            'context' => empty($context) ? null : json_encode($context),
            'created_at' => now(),
        ]);
    }
}
