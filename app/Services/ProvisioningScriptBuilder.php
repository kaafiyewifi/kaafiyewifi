<?php

namespace App\Services;

use App\Models\Router;
use Illuminate\Support\Str;

class ProvisioningScriptBuilder
{
    /**
     * Build RouterOS provisioning script (compatible v6/v7 with safe commands).
     */
    public function build(Router $router, string $plainToken, string $apiUserPassword): string
    {
        $callbackUrl = rtrim(config('app.url'), '/') . '/api/router/callback';
        $captiveLogin = rtrim(config('app.url'), '/') . '/captive/login/' . $plainToken;

        // Domains allowed in walled garden (add as you like)
        $allowedDomains = [
            'kaafiye.online',
            // Payment gateways (edit to real ones)
            'evc.hormuud.com',
            'edahab.net',
        ];

        $domainsAsCsv = implode(',', $allowedDomains);

        $routerName = $this->escape($router->name);
        $locName    = $this->escape($router->location_name ?? '');
        $allowedApi = $this->escape($router->allowed_api_ip);
        $hsIf       = $this->escape($router->hotspot_interface);
        $pppoeIf    = $this->escape($router->pppoe_interface);
        $apiPort    = (int) $router->api_port;
        $apiSslPort = (int) ($router->api_ssl_port ?? 8729);

        // JSON payload (keep simple - RouterOS fetch likes compact strings)
        $json = sprintf(
            '{"token":"%s","router_name":"%s","location_name":"%s","hotspot_interface":"%s","pppoe_interface":"%s","api_port":%d,"api_ssl_port":%d}',
            $plainToken,
            $routerName,
            $locName,
            $hsIf,
            $pppoeIf,
            $apiPort,
            $apiSslPort
        );

        // RouterOS script
        return <<<RSC
# ================================
# KAAFIYE: PROVISIONING SCRIPT
# Single-tenant HOTSPOT + PPPoE
# One-time Token: {$plainToken}
# ================================
:local TOKEN "{$plainToken}";
:local CALLBACK_URL "{$callbackUrl}";
:local CAPTIVE_URL "{$captiveLogin}";
:local ROUTER_NAME "{$routerName}";
:local LOCATION_NAME "{$locName}";
:local ALLOWED_API "{$allowedApi}";
:local API_PORT "{$apiPort}";
:local API_SSL_PORT "{$apiSslPort}";
:local HOTSPOT_IF "{$hsIf}";
:local PPPOE_IF "{$pppoeIf}";
:local API_USER "kaafiye_api";
:local API_PASS "{$apiUserPassword}";
:local WG_DOMAINS "{$domainsAsCsv}";

# ---- Safety backups (won't break connectivity) ----
:do { /system backup save name=("kaafiye-before"); } on-error={ :log warning "KAAFIYE: backup save failed"; }
:do { /export file=("kaafiye-export-before"); } on-error={ :log warning "KAAFIYE: export failed"; }

# ---- Identity ----
/system identity set name=\$ROUTER_NAME;

# ---- Basic DNS (needed for hotspot) ----
/ip dns set servers=1.1.1.1,8.8.8.8 allow-remote-requests=yes;

# =====================================================
# 1) SERVICE HARDENING + API (restrict by ALLOWED_API)
# =====================================================
:log info "KAAFIYE: Setting services + API access";

# Disable risky services (safe defaults)
:foreach s in={"telnet";"ftp";"www";"www-ssl"} do={
  :do { /ip service set [find name=\$s] disabled=yes } on-error={}
}

# Enable API services
:do { /ip service set [find name="api"] disabled=no port=\$API_PORT address=\$ALLOWED_API } on-error={
  :log warning "KAAFIYE: cannot set api service";
}
:do { /ip service set [find name="api-ssl"] disabled=no port=\$API_SSL_PORT address=\$ALLOWED_API } on-error={
  :log warning "KAAFIYE: api-ssl not available or cannot set";
}

# Create / update API user
:local uId [/user find name=\$API_USER];
:if ([:len \$uId] = 0) do={
  /user add name=\$API_USER group=full password=\$API_PASS comment="KAAFIYE: API user";
} else={
  /user set \$uId password=\$API_PASS comment="KAAFIYE: API user";
}

# =====================================================
# 2) FIREWALL INPUT HARDENING (NO WAN changes)
# =====================================================
:log info "KAAFIYE: Applying firewall rules";

# Helper: add firewall rule if not exists by comment
:global ensureFw;
:set ensureFw do={
  :local c \$1;
  :local ruleId [/ip firewall filter find comment=\$c];
  :if ([:len \$ruleId] = 0) do={
    /ip firewall filter add chain=input comment=\$c action=\$2 protocol=\$3 dst-port=\$4 src-address=\$5 connection-state=\$6;
  }
};

# Accept established/related
:if ([:len [/ip firewall filter find comment="KAAFIYE: accept established,related"]] = 0) do={
  /ip firewall filter add chain=input action=accept connection-state=established,related comment="KAAFIYE: accept established,related";
}

# Drop invalid
:if ([:len [/ip firewall filter find comment="KAAFIYE: drop invalid"]] = 0) do={
  /ip firewall filter add chain=input action=drop connection-state=invalid comment="KAAFIYE: drop invalid";
}

# Allow ICMP (ping)
:if ([:len [/ip firewall filter find comment="KAAFIYE: allow icmp"]] = 0) do={
  /ip firewall filter add chain=input action=accept protocol=icmp comment="KAAFIYE: allow icmp";
}

# Allow API only from ALLOWED_API (tcp 8728 & 8729)
:if ([:len [/ip firewall filter find comment="KAAFIYE: allow api from allowed ip"]] = 0) do={
  /ip firewall filter add chain=input action=accept protocol=tcp dst-port=\$API_PORT src-address=\$ALLOWED_API comment="KAAFIYE: allow api from allowed ip";
}
:if ([:len [/ip firewall filter find comment="KAAFIYE: allow api-ssl from allowed ip"]] = 0) do={
  /ip firewall filter add chain=input action=accept protocol=tcp dst-port=\$API_SSL_PORT src-address=\$ALLOWED_API comment="KAAFIYE: allow api-ssl from allowed ip";
}

# Drop API from others
:if ([:len [/ip firewall filter find comment="KAAFIYE: drop api from others"]] = 0) do={
  /ip firewall filter add chain=input action=drop protocol=tcp dst-port=\$API_PORT comment="KAAFIYE: drop api from others";
}
:if ([:len [/ip firewall filter find comment="KAAFIYE: drop api-ssl from others"]] = 0) do={
  /ip firewall filter add chain=input action=drop protocol=tcp dst-port=\$API_SSL_PORT comment="KAAFIYE: drop api-ssl from others";
}

# =====================================================
# 3) HOTSPOT BASE (creates hotspot profile/server if missing)
#    NOTE: does NOT touch WAN. You must have IP/DHCP ready on HOTSPOT_IF.
# =====================================================
:log info "KAAFIYE: Setting Hotspot base";

:local hsProfileId [/ip hotspot profile find name="kaafiye-hs-profile"];
:if ([:len \$hsProfileId] = 0) do={
  /ip hotspot profile add name="kaafiye-hs-profile" hotspot-address=10.5.50.1 dns-name="login.kaafiye" login-by=http-chap,http-pap comment="KAAFIYE: hotspot profile";
} else={
  /ip hotspot profile set \$hsProfileId comment="KAAFIYE: hotspot profile";
}

# Hotspot server
:local hsServerId [/ip hotspot find name="kaafiye-hotspot"];
:if ([:len \$hsServerId] = 0) do={
  /ip hotspot add name="kaafiye-hotspot" interface=\$HOTSPOT_IF profile="kaafiye-hs-profile" comment="KAAFIYE: hotspot server";
} else={
  /ip hotspot set \$hsServerId interface=\$HOTSPOT_IF profile="kaafiye-hs-profile" comment="KAAFIYE: hotspot server";
}

# Walled garden domains
:foreach d in=[:toarray \$WG_DOMAINS] do={
  :local exists [/ip hotspot walled-garden find dst-host=\$d];
  :if ([:len \$exists] = 0) do={
    /ip hotspot walled-garden add dst-host=\$d action=allow comment="KAAFIYE: walled garden domain";
  }
}

# NOTE: Captive redirect: We keep it simple by allowing external portal usage via HTTP status redirect in your portal flow.
# You can also customize /hotspot/login.html later. This script focuses bootstrap + domain allowance.

# =====================================================
# 4) PPPoE BASE (server + profile)
# =====================================================
:log info "KAAFIYE: Setting PPPoE base";

:local pppProfileId [/ppp profile find name="kaafiye-ppp-profile"];
:if ([:len \$pppProfileId] = 0) do={
  /ppp profile add name="kaafiye-ppp-profile" only-one=yes use-encryption=required comment="KAAFIYE: ppp profile";
} else={
  /ppp profile set \$pppProfileId comment="KAAFIYE: ppp profile";
}

:local pppSrvId [/interface pppoe-server server find service-name="kaafiye-pppoe"];
:if ([:len \$pppSrvId] = 0) do={
  /interface pppoe-server server add service-name="kaafiye-pppoe" interface=\$PPPOE_IF default-profile="kaafiye-ppp-profile" one-session-per-host=yes disabled=no comment="KAAFIYE: pppoe server";
} else={
  /interface pppoe-server server set \$pppSrvId interface=\$PPPOE_IF default-profile="kaafiye-ppp-profile" disabled=no comment="KAAFIYE: pppoe server";
}

# =====================================================
# 5) CALLBACK TO LARAVEL (fetch POST)
# =====================================================
:log info "KAAFIYE: Calling back to Laravel";

:local payload "{$this->escapeForRouterOS($json)}";

:do {
  /tool fetch url=\$CALLBACK_URL http-method=post http-header-field="Content-Type: application/json" http-data=\$payload keep-result=no;
  :log info "KAAFIYE: Callback sent";
} on-error={
  :log warning "KAAFIYE: Callback failed (check DNS/WAN/firewall).";
}

# =====================================================
# 6) VERIFY OUTPUT
# =====================================================
:log info "KAAFIYE: Provisioning completed. Printing checks...";

/ip service print where name~"api";
/ip firewall filter print where comment~"KAAFIYE";
/ip hotspot print;
/interface pppoe-server server print;

:log info "KAAFIYE: DONE";

RSC;
    }

    private function escape(string $value): string
    {
        // minimal escaping for RouterOS quotes
        return str_replace(['\\', '"'], ['\\\\', '\"'], $value);
    }

    private function escapeForRouterOS(string $json): string
    {
        // RouterOS http-data likes quotes escaped
        return str_replace(['\\', '"'], ['\\\\', '\"'], $json);
    }
}
