<?php

namespace App\Services\Radius;

use App\Models\Router;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class RadiusSessionService
{
    public function enforceDeviceLimit(string $username, int $limit): void
    {
        $username = trim($username);
        $limit = max(1, $limit);

        if ($username === '') {
            return;
        }

        $sessions = DB::connection('radius')
            ->table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('acctstarttime')
            ->orderByDesc('radacctid')
            ->get();

        if ($sessions->count() <= $limit) {
            return;
        }

        $sessionsToKill = $sessions->slice($limit);

        foreach ($sessionsToKill as $session) {
            $ip = $this->normalizeIp($session->framedipaddress ?? null);

            try {
                app(RadiusCoaService::class)->disconnect($username, $ip);
            } catch (\Throwable $e) {
                Log::warning('Radius CoA disconnect failed', [
                    'username' => $username,
                    'radacctid' => $session->radacctid ?? null,
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            usleep(500000);

            if ($this->hasActiveSessionOnRouters($username, $ip)) {
                $this->disconnectFromRouters($username, $ip);
                usleep(500000);
            }

            $this->markSessionStopped($session);

            Log::info('Radius extra session cleaned', [
                'username' => $username,
                'radacctid' => $session->radacctid ?? null,
                'ip' => $ip,
                'limit' => $limit,
            ]);
        }
    }

    private function hasActiveSessionOnRouters(string $username, ?string $ip = null): bool
    {
        foreach ($this->getCandidateRouters() as $router) {
            $host = $this->resolveRouterHost($router);

            if (!$host) {
                continue;
            }

            try {
                $client = $this->makeClient($host, (int) ($router->api_port ?: env('MIKROTIK_API_PORT', 8728)));

                $query = new Query('/ip/hotspot/active/print');
                $query->where('user', $username);

                $activeSessions = $client->query($query)->read();

                foreach ($activeSessions as $active) {
                    $activeIp = $this->normalizeIp($active['address'] ?? null);

                    if ($ip !== null && $activeIp !== null && $activeIp !== $ip) {
                        continue;
                    }

                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning('Router API active-session check failed', [
                    'username' => $username,
                    'ip' => $ip,
                    'router_id' => $router->id ?? null,
                    'host' => $host,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return false;
    }

    private function disconnectFromRouters(string $username, ?string $ip = null): void
    {
        foreach ($this->getCandidateRouters() as $router) {
            $host = $this->resolveRouterHost($router);

            if (!$host) {
                continue;
            }

            try {
                $client = $this->makeClient($host, (int) ($router->api_port ?: env('MIKROTIK_API_PORT', 8728)));

                $print = new Query('/ip/hotspot/active/print');
                $print->where('user', $username);

                $activeSessions = $client->query($print)->read();

                foreach ($activeSessions as $active) {
                    $activeIp = $this->normalizeIp($active['address'] ?? null);

                    if ($ip !== null && $activeIp !== null && $activeIp !== $ip) {
                        continue;
                    }

                    if (!isset($active['.id']) || trim((string) $active['.id']) === '') {
                        continue;
                    }

                    try {
                        $remove = new Query('/ip/hotspot/active/remove');
                        $remove->equal('.id', $active['.id']);
                        $client->query($remove)->read();
                    } catch (\Throwable $e) {
                        Log::warning('Router API remove active session failed', [
                            'username' => $username,
                            'ip' => $ip,
                            'router_id' => $router->id ?? null,
                            'host' => $host,
                            'active_id' => $active['.id'] ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Log::info('Router API fallback disconnect succeeded', [
                    'username' => $username,
                    'ip' => $ip,
                    'router_id' => $router->id ?? null,
                    'host' => $host,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Router API fallback disconnect failed', [
                    'username' => $username,
                    'ip' => $ip,
                    'router_id' => $router->id ?? null,
                    'host' => $host,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getCandidateRouters()
    {
        return Router::query()
            ->where(function ($q) {
                $q->whereNotNull('wg_ip')
                    ->orWhereNotNull('mgmt_host');
            })
            ->get(['id', 'wg_ip', 'mgmt_host', 'api_port']);
    }

    private function resolveRouterHost(object $router): ?string
    {
        $wgIp = trim((string) ($router->wg_ip ?? ''));
        if ($wgIp !== '') {
            return $wgIp;
        }

        $mgmtHost = trim((string) ($router->mgmt_host ?? ''));
        if ($mgmtHost !== '') {
            return $mgmtHost;
        }

        return null;
    }

    private function makeClient(string $host, int $port): Client
    {
        $config = new Config([
            'host' => $host,
            'user' => (string) env('ROUTER_API_USER', 'kaafiye'),
            'pass' => (string) env('ROUTER_API_PASS', 'SuperStrongPasswordHere'),
            'port' => $port,
            'timeout' => 3,
            'attempts' => 1,
        ]);

        return new Client($config);
    }

    private function markSessionStopped(object $session): void
    {
        if (!isset($session->radacctid)) {
            return;
        }

        $stopTime = now();

        $startTime = isset($session->acctstarttime) && $session->acctstarttime
            ? Carbon::parse($session->acctstarttime)
            : null;

        $sessionTime = $startTime ? max(0, $startTime->diffInSeconds($stopTime)) : 0;

        DB::connection('radius')
            ->table('radacct')
            ->where('radacctid', $session->radacctid)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime' => $stopTime,
                'acctsessiontime' => $sessionTime,
                'acctterminatecause' => 'Admin-Reset',
            ]);
    }

    private function normalizeIp(?string $ip): ?string
    {
        $ip = trim((string) $ip);

        return $ip !== '' ? $ip : null;
    }
}