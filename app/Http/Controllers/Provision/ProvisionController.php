<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use App\Services\Routers\RouterEventService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class ProvisionController extends Controller
{
    public function __construct(
        private readonly ProvisionTokenService $tokens
    ) {}

    /**
     * GET /provision/{token}
     * MikroTik fetches provisioning script here (text/plain).
     */
    public function script(Request $request, string $token): Response
    {
        [$row, $router] = $this->tokens->findValidTokenAndRouter($token);

        if (!$row || !$router) {
            return response("Invalid or expired token\n", 404, [
                'Content-Type'  => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-store',
            ]);
        }

        $this->tokens->markServed($row);

        $this->ensureHotspotAssets($router);

        $script = $this->buildRouterOsScript($router, $token);

        app(RouterEventService::class)->log(
            $router,
            'provision.script_served',
            ['ip' => $request->ip(), 'ua' => (string) $request->userAgent()]
        );

        return response($script . "\n", 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }

    private function buildRouterOsScript(Router $router, string $token): string
    {
        $apiPort  = (int) env('MIKROTIK_API_PORT', 8728);
        $apiUser  = (string) env('ROUTER_API_USER', 'kaafiye');
        $apiPass  = (string) env('ROUTER_API_PASS', 'SuperStrongPasswordHere');
        $timezone = (string) env('ROUTER_TIMEZONE', 'Africa/Mogadishu');

        $wgEndpoint        = (string) env('WG_ENDPOINT', 'vpn.kaafiye.online');
        $wgPort            = (int) env('WG_PORT', 51820);
        $wgServerPublicKey = (string) env('WG_SERVER_PUBLIC_KEY', '4EAWup9It8nUiZyqsgsRMgocPq8O6/5q+0CtYrs173k=');
        $wgRouterAddress   = (string) env('WG_ROUTER_ADDRESS', '10.9.0.2/24');
        $wgServerAddress   = (string) env('WG_SERVER_ADDRESS', '10.9.0.1/32');

        $radiusIp = (string) env('RADIUS_IP', '10.9.0.1');

        if (empty($router->radius_secret)) {
            $router->radius_secret = bin2hex(random_bytes(16));
            $router->save();
        }

        $radiusSecret = (string) $router->radius_secret;

        $hotspotBaseRoot = rtrim((string) env('HOTSPOT_BASE_URL', 'https://kaafiye.online/hotspot-files'), '/');
        $hotspotBase = $hotspotBaseRoot . '/' . $router->getKey();

        $callbackUrl   = url('/api/provision/callback/' . $token);
        $heartbeatUrl  = url('/api/routers/heartbeat');
        $snmpCommunity = (string) env('SNMP_COMMUNITY', 'kaafiye');

        $template = <<<'RSC'
# ================================
# KAAFIYE PROVISION SCRIPT (FINAL)
# RouterOS v7 compatible
# ================================

:global kfCallbackUrl "{{CALLBACK_URL}}";
:global kfHeartbeatUrl "{{HEARTBEAT_URL}}";

:global kfApiPort {{API_PORT}};
:global kfApiUser "{{API_USER}}";
:global kfApiPass "{{API_PASS}}";
:global kfTz "{{TZ}}";

:global kfWgEndpoint "{{WG_ENDPOINT}}";
:global kfWgPort {{WG_PORT}};
:global kfWgServerPub "{{WG_SERVER_PUB}}";
:global kfWgRouterAddress "{{WG_ROUTER_ADDRESS}}";
:global kfWgServerAddress "{{WG_SERVER_ADDRESS}}";

:global kfRadiusIp "{{RADIUS_IP}}";
:global kfRadiusSecret "{{RADIUS_SECRET}}";

:global kfHotspotBase "{{HOTSPOT_BASE}}";
:global kfSnmpCommunity "{{SNMP_COMMUNITY}}";

:local kfProvisionOk true;

:put "-----------------Downloading configuration-----------------";
:put "Downloading configuration...";

:do {
  /ping 8.8.8.8 count=3;
} on-error={}

:put "-----------------Applying configuration-----------------";
:put "Applying configuration...";

:log info "Starting Kaafiye provisioning...";

:local kfMgmtIp "";
:local kfWgIp "";
:local kfWgName "kaafiye-wg";
:local kfRouterWgPub "";

:do {
  :local idx [/ip address find where dynamic=yes disabled=no];
  :if ([:len $idx] > 0) do={
    :local a [/ip address get [:pick $idx 0] address];
    :set kfMgmtIp [:pick $a 0 [:find $a "/"]];
  }
} on-error={}

:if ($kfMgmtIp = "") do={
  :foreach ifn in={"bridgeLocal";"bridge";"ether1";"ether2"} do={
    :do {
      :local idx2 [/ip address find where interface=$ifn disabled=no];
      :if ([:len $idx2] > 0) do={
        :local a2 [/ip address get [:pick $idx2 0] address];
        :set kfMgmtIp [:pick $a2 0 [:find $a2 "/"]];
      }
    } on-error={}
    :if ($kfMgmtIp != "") do={ :break; }
  }
}

:put ("-----------------Detected mgmt_ip=" . $kfMgmtIp . "-----------------");

# =====================================================================
# WIREGUARD
# =====================================================================
:put "-----------------Downloading WireGuard configuration file-----------------";

:do {
  :if ([:len [/interface wireguard find where name=$kfWgName]] = 0) do={
    /interface wireguard add name=$kfWgName listen-port=0 mtu=1420 comment="KAAFIYE";
  }

  :if ([:len [/ip address find where address=$kfWgRouterAddress interface=$kfWgName]] = 0) do={
    /ip address add address=$kfWgRouterAddress interface=$kfWgName comment="KAAFIYE-WG";
  }

  :set kfWgIp [:pick $kfWgRouterAddress 0 [:find $kfWgRouterAddress "/"]];
  :set kfRouterWgPub [/interface wireguard get [find where name=$kfWgName] public-key];

  :if ([:len [/interface wireguard peers find where interface=$kfWgName]] = 0) do={
    /interface wireguard peers add interface=$kfWgName \
      public-key=$kfWgServerPub \
      endpoint-address=$kfWgEndpoint \
      endpoint-port=$kfWgPort \
      allowed-address=$kfWgServerAddress \
      persistent-keepalive=25s \
      comment="KAAFIYE-SERVER";
  } else={
    /interface wireguard peers set [find where interface=$kfWgName] \
      public-key=$kfWgServerPub \
      endpoint-address=$kfWgEndpoint \
      endpoint-port=$kfWgPort \
      allowed-address=$kfWgServerAddress \
      persistent-keepalive=25s \
      comment="KAAFIYE-SERVER";
  }

  :put "-----------------Applying WireGuard configuration-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------WireGuard configuration FAILED-----------------";
}

# =====================================================================
# SNMP
# =====================================================================
:do {
  /snmp set enabled=yes;
  :local cId [/snmp community find where name=$kfSnmpCommunity];
  :if ([:len $cId] > 0) do={
    :if ($kfMgmtIp != "") do={
      /snmp community set [:pick $cId 0] name=$kfSnmpCommunity addresses=($kfMgmtIp . "/32") security=none read-access=yes write-access=no authentication-protocol=MD5 encryption-protocol=DES;
    } else={
      /snmp community set [:pick $cId 0] name=$kfSnmpCommunity security=none read-access=yes write-access=no authentication-protocol=MD5 encryption-protocol=DES;
    }
  } else={
    :if ($kfMgmtIp != "") do={
      /snmp community add name=$kfSnmpCommunity addresses=($kfMgmtIp . "/32") security=none read-access=yes write-access=no authentication-protocol=MD5 encryption-protocol=DES;
    } else={
      /snmp community add name=$kfSnmpCommunity security=none read-access=yes write-access=no authentication-protocol=MD5 encryption-protocol=DES;
    }
  }
  :put "-----------------SNMP community added successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------SNMP community add FAILED-----------------";
}

# =====================================================================
# RADIUS
# =====================================================================
:do {
  :if ($kfRadiusSecret = "") do={
    :set kfProvisionOk false;
    :put "-----------------RADIUS secret missing-----------------";
  } else={
    :local kfRid [/radius find where address=$kfRadiusIp];

    :if ([:len $kfRid] > 0) do={
      /radius set [:pick $kfRid 0] \
        address=$kfRadiusIp \
        secret=$kfRadiusSecret \
        authentication-port=1812 \
        accounting-port=1813 \
        timeout=300ms \
        src-address=$kfWgIp \
        service=hotspot,login \
        comment="KAAFIYE";
      :put "-----------------RADIUS updated successfully-----------------";
    } else={
      /radius add \
        service=hotspot,login \
        address=$kfRadiusIp \
        secret=$kfRadiusSecret \
        authentication-port=1812 \
        accounting-port=1813 \
        timeout=300ms \
        src-address=$kfWgIp \
        comment="KAAFIYE";
      :put "-----------------RADIUS server added successfully-----------------";
    }

    :do { /ip hotspot profile set [find where name="default"] use-radius=yes radius-accounting=yes; } on-error={}
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------RADIUS configuration FAILED-----------------";
}

# =====================================================================
# API USER
# =====================================================================
:do {
  :local uId [/user find where name=$kfApiUser];
  :if ([:len $uId] > 0) do={
    /user set [:pick $uId 0] password=$kfApiPass group=full;
  } else={
    /user add name=$kfApiUser password=$kfApiPass group=full comment="KAAFIYE";
  }
  :put "-----------------Kaafiye user added successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Kaafiye user add FAILED-----------------";
}

# =====================================================================
# NAT / INTERNET SHARING
# =====================================================================
:put "-----------------Removed existing masquerade rules-----------------";

:do {
  :foreach n in=[/ip firewall nat find chain=srcnat action=masquerade] do={
    /ip firewall nat remove $n;
  }
} on-error={}

:put "-----------------Added masquerade rule for entire network-----------------";

:do {
  /ip firewall nat add chain=srcnat action=masquerade out-interface=ether1 comment="KAAFIYE-WIFI";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Masquerade rule add FAILED-----------------";
}

# =====================================================================
# HOTSPOT NETWORK / DHCP / SERVER
# =====================================================================
:put "-----------------Configuring Hotspot Profile-----------------";

:do {
  :if ([:len [/ip address find where address="10.10.0.1/24" interface="bridgeLocal"]] = 0) do={
    /ip address add address=10.10.0.1/24 interface=bridgeLocal comment="KAAFIYE-HOTSPOT";
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot LAN IP add FAILED-----------------";
}

:do {
  :if ([:len [/ip pool find where name="hs-pool"]] = 0) do={
    /ip pool add name="hs-pool" ranges=10.10.0.2-10.10.0.254;
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot pool add FAILED-----------------";
}

:do {
  :if ([:len [/ip dhcp-server network find where address="10.10.0.0/24"]] = 0) do={
    /ip dhcp-server network add address=10.10.0.0/24 gateway=10.10.0.1 dns-server=8.8.8.8,1.1.1.1;
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------DHCP network add FAILED-----------------";
}

:do {
  :if ([:len [/ip dhcp-server find where name="hs-dhcp"]] = 0) do={
    /ip dhcp-server add name="hs-dhcp" interface=bridgeLocal address-pool="hs-pool" lease-time=1h disabled=no;
  } else={
    /ip dhcp-server set [find where name="hs-dhcp"] interface=bridgeLocal address-pool="hs-pool" lease-time=1h disabled=no;
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------DHCP server add FAILED-----------------";
}

:do {
  :if ([:len [/ip hotspot profile find where name="hsprof1"]] = 0) do={
    /ip hotspot profile add \
      name="hsprof1" \
      hotspot-address=10.10.0.1 \
      html-directory=hotspot \
      login-by=cookie,http-chap,http-pap \
      use-radius=yes \
      radius-accounting=yes \
      dns-name="";
  } else={
    /ip hotspot profile set [find where name="hsprof1"] \
      hotspot-address=10.10.0.1 \
      html-directory=hotspot \
      login-by=cookie,http-chap,http-pap \
      use-radius=yes \
      radius-accounting=yes \
      dns-name="";
  }
  :put "-----------------Hotspot profile configured successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot profile configuration FAILED-----------------";
}

:do {
  :if ([:len [/ip hotspot find where name="hotspot1"]] = 0) do={
    /ip hotspot add \
      name="hotspot1" \
      interface=bridgeLocal \
      address-pool="hs-pool" \
      profile="hsprof1" \
      disabled=no;
  } else={
    /ip hotspot set [find where name="hotspot1"] \
      interface=bridgeLocal \
      address-pool="hs-pool" \
      profile="hsprof1" \
      disabled=no;
  }
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot server configuration FAILED-----------------";
}

# =====================================================================
# HOTSPOT FILES
# =====================================================================
:put "---------------Downloading hotspot files-----------------";

:do { /file make-directory hotspot; } on-error={}
:do { /file make-directory hotspot/css; } on-error={}
:do { /file make-directory hotspot/img; } on-error={}
:do { /file make-directory hotspot/xml; } on-error={}

:local base $kfHotspotBase;
:local kfFetchDuration 40s;
:local kfFetchIdle 20s;
:local hotspotOk true;

:do {
  :local u ($base . "/login.html");
  /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/login.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle;
} on-error={
  :set hotspotOk false;
  :put "-----------------FAILED: login.html-----------------";
}

:do {
  :local u ($base . "/alogin.html");
  /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/alogin.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle;
} on-error={
  :set hotspotOk false;
  :put "-----------------FAILED: alogin.html-----------------";
}

:do {
  :local u ($base . "/logout.html");
  /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/logout.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle;
} on-error={
  :set hotspotOk false;
  :put "-----------------FAILED: logout.html-----------------";
}

:do {
  :local u ($base . "/status.html");
  /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/status.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle;
} on-error={
  :put "-----------------WARNING: status.html-----------------";
}

:do { :local u ($base . "/error.html"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/error.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/redirect.html"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/redirect.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/md5.js"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/md5.js" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/favicon.ico"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/favicon.ico" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/css/style.css"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/css/style.css" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/img/user.svg"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/img/user.svg" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/img/password.svg"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/img/password.svg" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/xml/WISPAccessGatewayParam.xsd"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/xml/WISPAccessGatewayParam.xsd" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/xml/alogin.html"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/xml/alogin.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}
:do { :local u ($base . "/xml/error.html"); /tool fetch url=$u mode=https check-certificate=no output=file dst-path="hotspot/xml/error.html" duration=$kfFetchDuration idle-timeout=$kfFetchIdle; } on-error={}

:if ($hotspotOk = true) do={
  :put "-----------------Downloaded hotspot files successfully-----------------";
} else={
  :set kfProvisionOk false;
  :put "-----------------Hotspot files download FAILED-----------------";
}

# =====================================================================
# WALLED GARDEN
# =====================================================================
:do {
  /ip hotspot walled-garden add dst-host="login.kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="app.kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="kaafiye.online" action=allow comment="KAAFIYE-WG";
} on-error={}

:put "-----------------Walled garden rules added successfully-----------------";

# =====================================================================
# SERVICES
# =====================================================================
:do {
  /ip service set api disabled=no port=$kfApiPort;
  /ip service set winbox disabled=no;

  /ip service set telnet disabled=yes;
  /ip service set ftp disabled=yes;
  /ip service set www disabled=yes;
  /ip service set www-ssl disabled=yes;
  /ip service set api-ssl disabled=yes;

  :do { /ip service set ssh disabled=no; } on-error={}

  :if ($kfMgmtIp != "") do={
    :do { /ip service set winbox address=($kfMgmtIp . "/32"); } on-error={}
    :do { /ip service set api address=($kfMgmtIp . "/32"); } on-error={}
    :do { /ip service set ssh address=($kfMgmtIp . "/32"); } on-error={}
  }

  :put "-----------------Services configured successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Services configuration FAILED-----------------";
}

# =====================================================================
# TIMEZONE
# =====================================================================
:do {
  /system clock set time-zone-name=$kfTz;
  :put "-----------------Timezone configured successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Timezone set FAILED-----------------";
}

# =====================================================================
# CALLBACK
# =====================================================================
:put "-----------------Notifying server (callback)-----------------";

:local kfIdent [/system identity get name];
:local kfCbUrl ($kfCallbackUrl . "?identity=" . $kfIdent . "&mgmt_ip=" . $kfMgmtIp . "&api_port=" . $kfApiPort);

:if ($kfRouterWgPub != "") do={
  :set kfCbUrl ($kfCbUrl . "&wg_pub=" . $kfRouterWgPub);
}

:if ($kfWgIp != "") do={
  :set kfCbUrl ($kfCbUrl . "&wg_ip=" . $kfWgIp);
}

:do {
  /tool fetch mode=https check-certificate=no output=file dst-path=cb.txt url=$kfCbUrl;
  :put "-----------------Callback sent successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Callback FAILED-----------------";
}

:do { /file remove cb.txt; } on-error={}

# =====================================================================
# HEARTBEAT
# =====================================================================
:put "-----------------Configuring heartbeat-----------------";

:do { /system scheduler remove [find name="kaafiye-heartbeat"]; } on-error={}

:do {
  /system scheduler add name="kaafiye-heartbeat" start-time=startup interval=1m on-event="\
:local ident [/system identity get name];\
:local cpu [/system resource get cpu-load];\
:local freeMem [/system resource get free-memory];\
:local totalMem [/system resource get total-memory];\
:local freeHdd [/system resource get free-hdd-space];\
:local totalHdd [/system resource get total-hdd-space];\
:local uptime [/system resource get uptime];\
:local verFull [/system resource get version];\
:local sp [:find \$verFull \" \"];\
:local ver \$verFull;\
:if (\$sp != nil) do={ :set ver [:pick \$verFull 0 \$sp]; };\
:local board [/system resource get board-name];\
:local arch [/system resource get architecture-name];\
:local hbUrl \$kfHeartbeatUrl;\
:set hbUrl (\$hbUrl . \"?identity=\" . \$ident);\
:set hbUrl (\$hbUrl . \"&cpu_load=\" . \$cpu);\
:set hbUrl (\$hbUrl . \"&free_memory=\" . \$freeMem);\
:set hbUrl (\$hbUrl . \"&total_memory=\" . \$totalMem);\
:set hbUrl (\$hbUrl . \"&free_hdd_space=\" . \$freeHdd);\
:set hbUrl (\$hbUrl . \"&total_hdd_space=\" . \$totalHdd);\
:set hbUrl (\$hbUrl . \"&uptime=\" . \$uptime);\
:set hbUrl (\$hbUrl . \"&version=\" . \$ver);\
:set hbUrl (\$hbUrl . \"&board_name=\" . \$board);\
:set hbUrl (\$hbUrl . \"&architecture_name=\" . \$arch);\
:do { /tool fetch mode=https check-certificate=no output=file dst-path=hb.txt url=\$hbUrl; } on-error={ :log warning \"Kaafiye heartbeat failed\"; };\
:do { /file remove hb.txt; } on-error={};";
  :put "-----------------Heartbeat scheduler added-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Heartbeat scheduler add FAILED-----------------";
}

:if ($kfProvisionOk = true) do={
  :put "-----------------Configuration completed successfully-----------------";
  :log info "Configuration completed successfully.";
} else={
  :put "-----------------Configuration completed with warnings-----------------";
  :log warning "Configuration completed with warnings.";
}
RSC;

        return strtr($template, [
            '{{CALLBACK_URL}}'     => $this->rosEscape($callbackUrl),
            '{{HEARTBEAT_URL}}'    => $this->rosEscape($heartbeatUrl),
            '{{API_PORT}}'         => (string) $apiPort,
            '{{API_USER}}'         => $this->rosEscape($apiUser),
            '{{API_PASS}}'         => $this->rosEscape($apiPass),
            '{{TZ}}'               => $this->rosEscape($timezone),
            '{{WG_ENDPOINT}}'      => $this->rosEscape($wgEndpoint),
            '{{WG_PORT}}'          => (string) $wgPort,
            '{{WG_SERVER_PUB}}'    => $this->rosEscape($wgServerPublicKey),
            '{{WG_ROUTER_ADDRESS}}'=> $this->rosEscape($wgRouterAddress),
            '{{WG_SERVER_ADDRESS}}'=> $this->rosEscape($wgServerAddress),
            '{{RADIUS_IP}}'        => $this->rosEscape($radiusIp),
            '{{RADIUS_SECRET}}'    => $this->rosEscape($radiusSecret),
            '{{HOTSPOT_BASE}}'     => $this->rosEscape($hotspotBase),
            '{{SNMP_COMMUNITY}}'   => $this->rosEscape($snmpCommunity),
        ]);
    }

    private function ensureHotspotAssets(Router $router): void
    {
        $routerId = (string) $router->getKey();
        $baseDir = public_path('hotspot-files/' . $routerId);

        File::ensureDirectoryExists($baseDir . '/css');
        File::ensureDirectoryExists($baseDir . '/img');
        File::ensureDirectoryExists($baseDir . '/xml');

        File::put($baseDir . '/login.html', $this->loginHtml($routerId));
        File::put($baseDir . '/alogin.html', $this->aloginHtml($routerId));
        File::put($baseDir . '/logout.html', $this->logoutHtml());
        File::put($baseDir . '/status.html', $this->statusHtml());
        File::put($baseDir . '/error.html', $this->errorHtml());
        File::put($baseDir . '/redirect.html', $this->redirectHtml());
        File::put($baseDir . '/md5.js', $this->md5Js());
        File::put($baseDir . '/favicon.ico', '');
        File::put($baseDir . '/css/style.css', $this->styleCss());
        File::put($baseDir . '/img/user.svg', $this->userSvg());
        File::put($baseDir . '/img/password.svg', $this->passwordSvg());
        File::put($baseDir . '/xml/WISPAccessGatewayParam.xsd', $this->wispXsd());
        File::put($baseDir . '/xml/alogin.html', $this->xmlAloginHtml());
        File::put($baseDir . '/xml/error.html', $this->xmlErrorHtml());
    }

    private function loginHtml(string $routerId): string
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kaafiye WiFi Login</title>
<link rel="stylesheet" href="css/style.css">
<script src="md5.js"></script>
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Kaafiye WiFi</h1>
    <p class="subtitle">Hotspot Login Page (Router ID: ROUTER_ID)</p>

    $(if error)
    <div class="alert">$(error)</div>
    $(endif)

    <form action="$(link-login-only)" method="post" class="form">
      <input type="hidden" name="dst" value="$(link-orig)">
      <input type="hidden" name="popup" value="true">

      <label>Username</label>
      <div class="input-wrap">
        <img src="img/user.svg" alt="">
        <input name="username" type="text" placeholder="Enter username" required>
      </div>

      <label>Password</label>
      <div class="input-wrap">
        <img src="img/password.svg" alt="">
        <input name="password" type="password" placeholder="Enter password" required>
      </div>

      <button type="submit">Connect Internet</button>
    </form>

    <div class="footer">Powered by Kaafiye ISP</div>
  </div>
</div>
</body>
</html>
HTML;

        return str_replace('ROUTER_ID', $routerId, $html);
    }

    private function aloginHtml(string $routerId): string
    {
        return $this->loginHtml($routerId);
    }

    private function logoutHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kaafiye WiFi Logout</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Kaafiye WiFi</h1>
    <p class="subtitle">You are logged out.</p>
    <a class="button-link" href="$(link-login)">Login again</a>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function statusHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kaafiye WiFi Status</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Kaafiye WiFi</h1>
    <p class="subtitle">Connection status</p>

    <div class="status-grid">
      <div><strong>User:</strong> $(username)</div>
      <div><strong>IP:</strong> $(ip)</div>
      <div><strong>MAC:</strong> $(mac)</div>
      <div><strong>Uptime:</strong> $(uptime)</div>
      <div><strong>Bytes In:</strong> $(bytes-in-nice)</div>
      <div><strong>Bytes Out:</strong> $(bytes-out-nice)</div>
    </div>

    <a class="button-link danger" href="$(link-logout)">Logout</a>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function errorHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kaafiye WiFi Error</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Kaafiye WiFi</h1>
    <div class="alert">$(error)</div>
    <a class="button-link" href="$(link-login)">Back to login</a>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function redirectHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="0; url=$(link-redirect)">
<title>Redirecting</title>
</head>
<body>Redirecting...</body>
</html>
HTML;
    }

    private function md5Js(): string
    {
        return <<<'JS'
function hexMD5(s){ return s; }
JS;
    }

    private function styleCss(): string
    {
        return <<<'CSS'
*{box-sizing:border-box}
body{
  margin:0;
  font-family:Arial,sans-serif;
  background:#f4f7fb;
  color:#1e293b;
}
.page{
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:20px;
}
.card{
  width:100%;
  max-width:380px;
  background:#fff;
  border-radius:16px;
  padding:28px;
  box-shadow:0 10px 30px rgba(0,0,0,.08);
}
h1{
  margin:0 0 8px;
  font-size:30px;
  text-align:center;
}
.subtitle{
  margin:0 0 22px;
  text-align:center;
  color:#64748b;
}
.form label{
  display:block;
  margin:10px 0 6px;
  font-size:14px;
  font-weight:700;
}
.input-wrap{
  display:flex;
  align-items:center;
  gap:8px;
  border:1px solid #dbe2ea;
  border-radius:10px;
  padding:10px 12px;
  margin-bottom:12px;
  background:#fff;
}
.input-wrap img{
  width:18px;
  height:18px;
  opacity:.75;
}
.input-wrap input{
  width:100%;
  border:none;
  outline:none;
  font-size:15px;
  background:transparent;
}
button,.button-link{
  display:block;
  width:100%;
  border:none;
  border-radius:10px;
  background:#0f62fe;
  color:#fff;
  text-decoration:none;
  text-align:center;
  font-size:16px;
  font-weight:700;
  padding:12px 14px;
  cursor:pointer;
  margin-top:10px;
}
.button-link.danger{
  background:#dc2626;
}
.alert{
  background:#fee2e2;
  color:#991b1b;
  padding:12px;
  border-radius:10px;
  margin-bottom:14px;
  font-size:14px;
}
.footer{
  margin-top:16px;
  font-size:12px;
  text-align:center;
  color:#94a3b8;
}
.status-grid{
  display:grid;
  gap:10px;
  background:#f8fafc;
  padding:14px;
  border-radius:12px;
  margin-bottom:14px;
}
CSS;
    }

    private function userSvg(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#64748b"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/></svg>
SVG;
    }

    private function passwordSvg(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#64748b"><path d="M17 10V8A5 5 0 0 0 7 8v2H5v10h14V10Zm-8-2a3 3 0 0 1 6 0v2H9Zm4 8.73V18h-2v-1.27a2 2 0 1 1 2 0Z"/></svg>
SVG;
    }

    private function wispXsd(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema"></xs:schema>
XML;
    }

    private function xmlAloginHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Kaafiye WiFi</title></head>
<body>Hotspot XML Login</body>
</html>
HTML;
    }

    private function xmlErrorHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Kaafiye WiFi Error</title></head>
<body>Hotspot XML Error</body>
</html>
HTML;
    }

    private function rosEscape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\"'], $value);
    }
}