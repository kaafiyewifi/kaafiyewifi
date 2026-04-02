<?php

namespace App\Services\Radius;

use App\Models\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusCoaService
{
    public function disconnect(string $username, ?string $ip = null, ?string $nasIp = null, ?string $secret = null): void
    {
        if (!$username) {
            return;
        }

        [$sessionIp, $sessionNasIp] = $this->resolveActiveSession($username);

        $ip = $ip ?: $sessionIp;

        [$nasIp, $secret] = $this->resolveNasAndSecret(
            username: $username,
            sessionNasIp: $sessionNasIp,
            nasIp: $nasIp,
            secret: $secret
        );

        $port = (int) config('radius.coa_port', 3799);

        if (!$ip || !$nasIp || !$secret) {
            Log::warning('RADIUS CoA disconnect skipped: missing active session, NAS IP, or secret', [
                'username' => $username,
                'ip' => $ip,
                'nas_ip' => $nasIp,
            ]);
            return;
        }

        $payload = 'User-Name="' . addslashes($username) . '"';
        $payload .= "\nFramed-IP-Address=" . $ip;

        $this->sendCoa($payload, $nasIp, $port, $secret, 'disconnect', $username, $ip);
    }

    public function changeAuthorization(string $username, ?string $ip = null, ?string $rateLimit = null, ?string $nasIp = null, ?string $secret = null): void
    {
        if (!$username) {
            return;
        }

        [$sessionIp, $sessionNasIp] = $this->resolveActiveSession($username);

        $ip = $ip ?: $sessionIp;

        [$nasIp, $secret] = $this->resolveNasAndSecret(
            username: $username,
            sessionNasIp: $sessionNasIp,
            nasIp: $nasIp,
            secret: $secret
        );

        $port = (int) config('radius.coa_port', 3799);

        if (!$ip || !$nasIp || !$secret) {
            Log::warning('RADIUS CoA update skipped: missing active session, NAS IP, or secret', [
                'username' => $username,
                'ip' => $ip,
                'nas_ip' => $nasIp,
            ]);
            return;
        }

        $payload = 'User-Name="' . addslashes($username) . '"';
        $payload .= "\nFramed-IP-Address=" . $ip;

        if ($rateLimit) {
            $payload .= "\nMikrotik-Rate-Limit=\"" . addslashes($rateLimit) . "\"";
        }

        $this->sendCoa($payload, $nasIp, $port, $secret, 'coa', $username, $ip, $rateLimit);
    }

    protected function resolveActiveSession(string $username): array
    {
        $session = DB::connection('radius')
            ->table('radacct')
            ->select(['framedipaddress', 'nasipaddress'])
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->first();

        return [
            $session?->framedipaddress ?: null,
            $session?->nasipaddress ?: null,
        ];
    }

    protected function resolveNasAndSecret(
        string $username,
        ?string $sessionNasIp = null,
        ?string $nasIp = null,
        ?string $secret = null
    ): array {
        if ($nasIp && $secret) {
            return [$nasIp, $secret];
        }

        $router = null;

        $lookupNasIp = $nasIp ?: $sessionNasIp;

        if ($lookupNasIp) {
            $router = Router::query()
                ->where('wg_ip', $lookupNasIp)
                ->orWhere('mgmt_host', $lookupNasIp)
                ->first();
        }

        if (!$router) {
            $sessionIdentity = DB::connection('radius')
                ->table('radacct')
                ->where('username', $username)
                ->whereNull('acctstoptime')
                ->orderByDesc('radacctid')
                ->value('calledstationid');

            if ($sessionIdentity) {
                $router = Router::query()
                    ->where('identity', $sessionIdentity)
                    ->orWhere('name', $sessionIdentity)
                    ->first();
            }
        }

        if (!$router) {
            $router = Router::query()
                ->whereNotNull('wg_ip')
                ->where('wg_ip', '!=', '')
                ->whereNotNull('radius_secret')
                ->where('radius_secret', '!=', '')
                ->orderByDesc('id')
                ->first();
        }

        $resolvedNasIp = $nasIp ?: ($router?->wg_ip ?: config('radius.nas_ip'));
        $resolvedSecret = $secret ?: ($router?->radius_secret ?: config('radius.secret'));

        Log::info('RADIUS CoA target resolved', [
            'username' => $username,
            'session_nas_ip' => $sessionNasIp,
            'resolved_nas_ip' => $resolvedNasIp,
            'router_id' => $router?->id,
            'router_identity' => $router?->identity,
        ]);

        return [$resolvedNasIp, $resolvedSecret];
    }

    protected function sendCoa(
        string $payload,
        string $nasIp,
        int $port,
        string $secret,
        string $type,
        string $username,
        ?string $ip = null,
        ?string $rateLimit = null
    ): void {
        $command = sprintf(
            'printf %s | radclient -x %s:%d %s %s 2>&1',
            escapeshellarg($payload . "\n"),
            escapeshellarg($nasIp),
            $port,
            $type,
            escapeshellarg($secret)
        );

        exec($command, $output, $exitCode);

        Log::info('RADIUS CoA executed', [
            'type' => $type,
            'username' => $username,
            'ip' => $ip,
            'rate_limit' => $rateLimit,
            'nas_ip' => $nasIp,
            'port' => $port,
            'exit_code' => $exitCode,
            'output' => $output,
        ]);
    }
}