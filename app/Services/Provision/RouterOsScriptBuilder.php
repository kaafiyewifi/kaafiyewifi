<?php

declare(strict_types=1);

namespace App\Services\Provision;

use App\Models\Router;
use Illuminate\Support\Str;

class RouterOsScriptBuilder
{
    /**
     * @return array{api_user:string, api_pass:string, script:string}
     */
    public function buildBootstrapScript(Router $router, string $token): array
    {
        $apiUser = (string) config('routers.bootstrap_api_user', 'system_api');
        $apiPass = Str::password(16);

        $serverIp   = (string) config('routers.server_ip');
        $apiPort    = (int) config('routers.api_port');

        $radiusAddr = (string) config('routers.effective_radius_address');
        $radiusSec  = (string) config('routers.radius_secret');

        $snmpComm   = (string) config('routers.snmp_community');
        $timezone   = (string) config('routers.timezone');

        $portalDomain = (string) config('routers.portal_domain');
        $filesBase    = (string) config('routers.hotspot_files_base');

        $hsProfile  = (string) config('routers.hotspot_profile', 'hsprof-kaafiye');
        $hsServer   = (string) config('routers.hotspot_server', 'hotspot-kaafiye');

        $wgHosts = config('routers.walled_garden_hosts', []);
        if (!is_array($wgHosts) || count($wgHosts) === 0) {
            $wgHosts = ['app.kaafiye.online', 'login.kaafiye.online', 'kaafiye.online'];
        }

        $rid = (int) $router->id;
        $loginUrl  = "{$filesBase}/{$rid}/login.html";
        $aloginUrl = "{$filesBase}/{$rid}/alogin.html";
        $errorUrl  = "{$filesBase}/{$rid}/error.html";
        $logoutUrl = "{$filesBase}/{$rid}/logout.html";

        // Signed callback URL (GET)
        $sig = $this->makeSigForUrl($token);
        $callbackUrl = route('provision.callback', ['token' => $token], true) . '?sig=' . $sig;

        // Build walled-garden lines (idempotent)
        $wgLines = '';
        foreach ($wgHosts as $host) {
            $host = trim((string) $host);
            if ($host === '') continue;

            $wgLines .= <<<WG
:if ([:len [/ip hotspot walled-garden find dst-host="$host"]] = 0) do={
    /ip hotspot walled-garden add dst-host="$host" action=allow comment="Kaafiye WG";
}
WG;
            $wgLines .= "\n";
        }

        $script = <<<RSC
# --- Kaafiye Provision Bootstrap (RouterOS v7) ---
:log info "Starting Kaafiye provisioning...";

:global SERVERIP "{$serverIp}";
:global APIPORT "{$apiPort}";
:global APIUSER "{$apiUser}";
:global APIPASS "{$apiPass}";

# Timezone + NTP (helps HTTPS fetch)
:do { /system clock set time-zone-name="{$timezone}"; } on-error={ :log info "Timezone set failed"; };
:do { /system ntp client set enabled=yes server-dns-names=time.google.com; } on-error={ :log info "NTP client not available"; };

# 1) Enable API service
/ip service set api disabled=no;
:do { /ip service set api port=\$APIPORT; } on-error={ :log info "API port set failed"; };

# 2) Create/update API user (idempotent)
:if ([:len [/user find name=\$APIUSER]] > 0) do={ /user remove [find name=\$APIUSER]; };
/user add name=\$APIUSER group=full password=\$APIPASS;

# 3) Firewall allow API from server only (idempotent)
:if ([:len [/ip firewall filter find comment="Allow API from Kaafiye server"]] = 0) do={
    /ip firewall filter add chain=input src-address=\$SERVERIP protocol=tcp dst-port=\$APIPORT action=accept comment="Allow API from Kaafiye server";
}

# 4) SNMP enable + community (idempotent)
/snmp set enabled=yes;
:if ([:len [/snmp community find name="{$snmpComm}"]] = 0) do={
    /snmp community add name="{$snmpComm}" addresses=0.0.0.0/0;
}

# 5) RADIUS add (hotspot) (idempotent)
:if ([:len [/radius find service=hotspot address="{$radiusAddr}"]] = 0) do={
    /radius add service=hotspot address="{$radiusAddr}" secret="{$radiusSec}" timeout=300ms;
}

# 6) Hotspot profile (CHAP + HTTPS + RADIUS + dns-name) (idempotent)
:if ([:len [/ip hotspot profile find name="{$hsProfile}"]] = 0) do={
    /ip hotspot profile add name="{$hsProfile}" login-by=http-chap,https use-radius=yes dns-name="{$portalDomain}";
} else={
    /ip hotspot profile set [find name="{$hsProfile}"] login-by=http-chap,https use-radius=yes dns-name="{$portalDomain}";
}

# 7) If hotspot server exists, attach profile (safe)
:if ([:len [/ip hotspot find name="{$hsServer}"]] > 0) do={
    /ip hotspot set [find name="{$hsServer}"] profile="{$hsProfile}";
}

# 8) Download hotspot portal files
:log info "Downloading hotspot files...";
/tool fetch mode=https url="{$loginUrl}"  dst-path=hotspot/login.html;
/tool fetch mode=https url="{$aloginUrl}" dst-path=hotspot/alogin.html;
/tool fetch mode=https url="{$errorUrl}"  dst-path=hotspot/error.html;
/tool fetch mode=https url="{$logoutUrl}" dst-path=hotspot/logout.html;
:log info "Downloaded hotspot files successfully";

# 9) Walled garden rules
:log info "Applying walled garden rules...";
{$wgLines}
:log info "Walled garden rules applied";

# 10) Callback
:log info "Sending callback...";
/tool fetch mode=https url="{$callbackUrl}" keep-result=no;

:log info "Provision done.";
RSC;

        return [
            'api_user' => $apiUser,
            'api_pass' => $apiPass,
            'script'   => $script,
        ];
    }

    // ---- Signature helpers (same logic as ProvisionController) ----

    private function makeSigForUrl(string $token): string
    {
        $variants = $this->makeSigVariants($token);
        return $variants[1] ?? $variants[0];
    }

    private function makeSigVariants(string $token): array
    {
        $rawKey = (string) config('app.key');

        $sig1 = hash_hmac('sha256', $token, $rawKey);

        $sig2 = null;
        if (str_starts_with($rawKey, 'base64:')) {
            $decoded = base64_decode(substr($rawKey, 7), true);
            if ($decoded !== false) {
                $sig2 = hash_hmac('sha256', $token, $decoded);
            }
        }

        return array_values(array_filter([$sig1, $sig2]));
    }
}
