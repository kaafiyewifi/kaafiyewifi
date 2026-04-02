<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\RadiusClientSyncService;
use App\Services\Routers\ProvisionTokenService;
use App\Services\Routers\RouterEventService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProvisionController extends Controller
{
    public function __construct(
        private readonly ProvisionTokenService $tokens
    ) {}

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

        $this->ensureStableRadiusSecret($router);
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

    public function callback(Request $request, string $token): Response
    {
        [$row, $router] = $this->tokens->findValidTokenAndRouter($token);

        if (!$row || !$router) {
            return response("Invalid or expired token\n", 404, [
                'Content-Type'  => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-store',
            ]);
        }

        $identity = trim((string) $request->input('identity', $request->query('identity', '')));
        $mgmtIp   = trim((string) $request->input('mgmt_ip', $request->query('mgmt_ip', '')));
        $wgIp     = trim((string) $request->input('wg_ip', $request->query('wg_ip', '')));

        if ($identity !== '') {
            $router->identity = $identity;
        }

        if ($mgmtIp !== '') {
            $router->mgmt_host = $mgmtIp;
        }

        if ($wgIp !== '') {
            $router->wg_ip = $wgIp;
        }

        $this->ensureStableRadiusSecret($router, false);

        $router->status = 'provisioned';
        $router->provisioned_at = now();
        $router->last_seen_at = now();
        $router->save();

        try {
            app(RadiusClientSyncService::class)->sync();

            Log::info('Provision callback radius sync completed', [
                'router_id' => $router->id,
                'identity' => $router->identity,
                'wg_ip' => $router->wg_ip,
            ]);
        } catch (\Throwable $e) {
            Log::error('Provision callback radius sync failed', [
                'router_id' => $router->id,
                'token' => $token,
                'identity' => $router->identity,
                'wg_ip' => $router->wg_ip,
                'error' => $e->getMessage(),
            ]);
        }

        $this->tokens->markUsed($row);

        app(RouterEventService::class)->log(
            $router,
            'provision.callback_received',
            [
                'ip'       => $request->ip(),
                'identity' => $identity,
                'mgmt_ip'  => $mgmtIp,
                'wg_ip'    => $wgIp,
                'ua'       => (string) $request->userAgent(),
            ]
        );

        return response("OK\n", 200, [
            'Content-Type'  => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store',
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
        $wgListenPort      = (int) env('WG_ROUTER_LISTEN_PORT', 13231);

        $radiusIp = (string) env('RADIUS_IP', '10.9.0.1');

        $this->ensureStableRadiusSecret($router);
        $radiusSecret = (string) $router->radius_secret;

        $hotspotBaseRoot = rtrim((string) env('HOTSPOT_BASE_URL', 'https://kaafiye.online/hotspot-files'), '/');
        $hotspotBase = $hotspotBaseRoot . '/' . $router->getKey();

        $callbackUrl     = url('/api/provision/callback/' . $token);
        $heartbeatUrl    = url('/api/routers/heartbeat');
        $snmpCommunity   = (string) env('SNMP_COMMUNITY', 'kaafiye');
        $hotspotDnsName  = (string) env('HOTSPOT_DNS_NAME', 'login.kaafiye.online');
        $hotspotLanIp    = '10.10.0.1';
        $hotspotLanCidr  = '10.10.0.1/24';
        $hotspotNetwork  = '10.10.0.0/24';
        $hotspotPoolName = 'hs-pool';
        $hotspotServer   = 'hotspot1';
        $hotspotProfile  = 'hsprof1';
        $hotspotDhcp     = 'hs-dhcp';

        $template = <<<'RSC'
# ================================
# KAAFIYE PROVISION SCRIPT (FINAL)
# CLEAN RESET + STABLE WG/RADIUS
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
:global kfWgListenPort {{WG_LISTEN_PORT}};
:global kfWgServerPub "{{WG_SERVER_PUB}}";
:global kfWgRouterAddress "{{WG_ROUTER_ADDRESS}}";
:global kfWgServerAddress "{{WG_SERVER_ADDRESS}}";

:global kfRadiusIp "{{RADIUS_IP}}";
:global kfRadiusSecret "{{RADIUS_SECRET}}";

:global kfHotspotBase "{{HOTSPOT_BASE}}";
:global kfSnmpCommunity "{{SNMP_COMMUNITY}}";
:global kfHotspotDnsName "{{HOTSPOT_DNS_NAME}}";
:global kfHotspotLanIp "{{HOTSPOT_LAN_IP}}";
:global kfHotspotLanCidr "{{HOTSPOT_LAN_CIDR}}";
:global kfHotspotNetwork "{{HOTSPOT_NETWORK}}";
:global kfHotspotPoolName "{{HOTSPOT_POOL_NAME}}";
:global kfHotspotServer "{{HOTSPOT_SERVER}}";
:global kfHotspotProfile "{{HOTSPOT_PROFILE}}";
:global kfHotspotDhcp "{{HOTSPOT_DHCP}}";

:local kfProvisionOk true;
:local kfLanBridge "";
:local kfProvisionBridge "bridgeLocal";
:local kfBridgeCreated false;

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
  :foreach ifn in={"bridgeLocal";"bridge";"bridge1";"ether1";"ether2"} do={
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
# CLEAN OLD HOTSPOT/LAN CONFIG FIRST
# =====================================================================
:put "-----------------Cleaning old hotspot/LAN configuration-----------------";

:do { /ip hotspot disable [find]; } on-error={}
:do { /ip hotspot remove [find]; } on-error={}
:do { /ip hotspot user remove [find]; } on-error={}
:do { /ip hotspot host remove [find]; } on-error={}
:do { /ip hotspot ip-binding remove [find]; } on-error={}
:do { /ip hotspot service-port remove [find]; } on-error={}
:do { /ip hotspot profile remove [find where name=$kfHotspotProfile]; } on-error={}
:do { /ip dhcp-server disable [find where name=$kfHotspotDhcp]; } on-error={}
:do { /ip dhcp-server remove [find where name=$kfHotspotDhcp]; } on-error={}
:do { /ip dhcp-server network remove [find where address=$kfHotspotNetwork]; } on-error={}
:do { /ip pool remove [find where name=$kfHotspotPoolName]; } on-error={}
:do { /ip dns static remove [find where comment="KAAFIYE-HOTSPOT-DNS"]; } on-error={}
:do {
  :foreach wgRule in=[/ip hotspot walled-garden find where comment="KAAFIYE-WG"] do={
    /ip hotspot walled-garden remove $wgRule;
  }
} on-error={}
:do { /ip address remove [find where comment="KAAFIYE-HOTSPOT"]; } on-error={}

# Try remove old default LAN from common reset configs
:do { /ip dhcp-server disable [find where name="dhcp1"]; } on-error={}
:do { /ip dhcp-server remove [find where name="dhcp1"]; } on-error={}
:do { /ip pool remove [find where name="dhcp_pool"]; } on-error={}
:do { /ip dhcp-server network remove [find where address="192.168.88.0/24"]; } on-error={}
:do { /ip address remove [find where address="192.168.88.1/24"]; } on-error={}

# Detect existing bridge or create clean one
:if ([:len [/interface bridge find where name=$kfProvisionBridge]] > 0) do={
  :set kfLanBridge $kfProvisionBridge;
} else={
  :if ([:len [/interface bridge find where name="bridge1"]] > 0) do={
    :set kfLanBridge "bridge1";
  } else={
    :if ([:len [/interface bridge find where name="bridge"]] > 0) do={
      :set kfLanBridge "bridge";
    } else={
      /interface bridge add name=$kfProvisionBridge comment="KAAFIYE-LAN";
      :set kfLanBridge $kfProvisionBridge;
      :set kfBridgeCreated true;
    }
  }
}

# If we created new bridge, add common LAN ports
:if ($kfBridgeCreated = true) do={
  :foreach lanIf in={"ether2";"ether3";"ether4";"ether5";"wlan1"} do={
    :if ([:len [/interface find where name=$lanIf]] > 0) do={
      :if ([:len [/interface bridge port find where interface=$lanIf]] = 0) do={
        /interface bridge port add interface=$lanIf bridge=$kfLanBridge;
      }
    }
  }
}

:put ("-----------------Using LAN bridge=" . $kfLanBridge . "-----------------");

# =====================================================================
# WIREGUARD
# =====================================================================
:put "-----------------Downloading WireGuard configuration file-----------------";

:do {
  :if ([:len [/interface wireguard find where name=$kfWgName]] = 0) do={
    /interface wireguard add name=$kfWgName listen-port=$kfWgListenPort mtu=1420 comment="KAAFIYE";
  } else={
    /interface wireguard set [find where name=$kfWgName] listen-port=$kfWgListenPort mtu=1420 comment="KAAFIYE";
  }

  :do { /ip address remove [find where interface=$kfWgName and address!=$kfWgRouterAddress]; } on-error={}

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
      allowed-address=10.9.0.0/24 \
      persistent-keepalive=25s \
      comment="KAAFIYE-SERVER";
  } else={
    /interface wireguard peers set [find where interface=$kfWgName] \
      public-key=$kfWgServerPub \
      endpoint-address=$kfWgEndpoint \
      endpoint-port=$kfWgPort \
      allowed-address=10.9.0.0/24 \
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
    :foreach oldRadius in=[/radius find] do={
      /radius remove $oldRadius;
    }

    /radius add \
      service=hotspot,login \
      address=$kfRadiusIp \
      secret=$kfRadiusSecret \
      authentication-port=1812 \
      accounting-port=1813 \
      timeout=3s \
      src-address=$kfWgIp \
      require-message-auth=no \
      comment="KAAFIYE";

    :put "-----------------RADIUS updated successfully-----------------";

    :do { /radius incoming set accept=yes port=3799; } on-error={}
    :do { /ip hotspot profile set [find where name="default"] use-radius=yes radius-accounting=yes; } on-error={}

    :foreach r in=[/ip firewall filter find where comment="Allow RADIUS CoA from server"] do={
      /ip firewall filter remove $r;
    }

    /ip firewall filter add chain=input action=accept protocol=udp src-address=$kfRadiusIp dst-port=3799 comment="Allow RADIUS CoA from server";

    :put "-----------------RADIUS CoA configured successfully-----------------";
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
# DNS
# =====================================================================
:do {
  /ip dns set allow-remote-requests=yes servers=8.8.8.8,1.1.1.1;

  :foreach d in=[/ip dns static find where comment="KAAFIYE-HOTSPOT-DNS"] do={
    /ip dns static remove $d;
  }

  /ip dns static add name=$kfHotspotDnsName address=$kfHotspotLanIp ttl=5m comment="KAAFIYE-HOTSPOT-DNS";

  :put "-----------------DNS configured successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------DNS configuration FAILED-----------------";
}

# =====================================================================
# HOTSPOT NETWORK / DHCP / SERVER
# =====================================================================
:put "-----------------Hotspot profile configured successfully-----------------";

# =====================================================================
# DEVICE LIMIT (LET RADIUS CONTROL)
# =====================================================================
:put "-----------------Setting shared-users=10000 (RADIUS control)-----------------";

:do {
  /ip hotspot user profile set [find] shared-users=10000;
  :put "-----------------shared-users set to 10000 successfully-----------------";
} on-error={
  :put "-----------------FAILED to set shared-users-----------------";
}

:do {
  /ip address add address=$kfHotspotLanCidr interface=$kfLanBridge comment="KAAFIYE-HOTSPOT";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot LAN IP add FAILED-----------------";
}

:do {
  /ip pool add name=$kfHotspotPoolName ranges=10.10.0.2-10.10.0.254;
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot pool add FAILED-----------------";
}

:do {
  /ip dhcp-server network add address=$kfHotspotNetwork gateway=$kfHotspotLanIp dns-server=$kfHotspotLanIp domain=$kfHotspotDnsName;
} on-error={
  :set kfProvisionOk false;
  :put "-----------------DHCP network add FAILED-----------------";
}

:do {
  /ip dhcp-server add name=$kfHotspotDhcp interface=$kfLanBridge address-pool=$kfHotspotPoolName lease-time=1h disabled=no;
} on-error={
  :set kfProvisionOk false;
  :put "-----------------DHCP server add FAILED-----------------";
}

:do {
  :if ([:len [/ip hotspot profile find where name=$kfHotspotProfile]] = 0) do={
    /ip hotspot profile add \
      name=$kfHotspotProfile \
      hotspot-address=$kfHotspotLanIp \
      html-directory=hotspot \
      login-by=http-pap,http-chap,cookie \
      use-radius=yes \
      radius-accounting=yes \
      dns-name=$kfHotspotDnsName;
  } else={
    /ip hotspot profile set [find where name=$kfHotspotProfile] \
      hotspot-address=$kfHotspotLanIp \
      html-directory=hotspot \
      login-by=http-pap,http-chap,cookie \
      use-radius=yes \
      radius-accounting=yes \
      dns-name=$kfHotspotDnsName;
  }
  :put "-----------------Hotspot profile configured successfully-----------------";
} on-error={
  :set kfProvisionOk false;
  :put "-----------------Hotspot profile configuration FAILED-----------------";
}

:do {
  /ip hotspot add \
    name=$kfHotspotServer \
    interface=$kfLanBridge \
    address-pool=$kfHotspotPoolName \
    profile=$kfHotspotProfile \
    disabled=no;
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
  :foreach wgRule in=[/ip hotspot walled-garden find where comment="KAAFIYE-WG"] do={
    /ip hotspot walled-garden remove $wgRule;
  }
} on-error={}

:do {
  /ip hotspot walled-garden add dst-host=$kfHotspotDnsName action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="app.kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="kaafiye.online" action=allow comment="KAAFIYE-WG";
  /ip hotspot walled-garden add dst-host="*.kaafiye.online" action=allow comment="KAAFIYE-WG";
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
:local kfCbUrl ($kfCallbackUrl . "?identity=" . $kfIdent . "&mgmt_ip=" . $kfMgmtIp);

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
            '{{CALLBACK_URL}}'        => $this->rosEscape($callbackUrl),
            '{{HEARTBEAT_URL}}'       => $this->rosEscape($heartbeatUrl),
            '{{API_PORT}}'            => (string) $apiPort,
            '{{API_USER}}'            => $this->rosEscape($apiUser),
            '{{API_PASS}}'            => $this->rosEscape($apiPass),
            '{{TZ}}'                  => $this->rosEscape($timezone),
            '{{WG_ENDPOINT}}'         => $this->rosEscape($wgEndpoint),
            '{{WG_PORT}}'             => (string) $wgPort,
            '{{WG_LISTEN_PORT}}'      => (string) $wgListenPort,
            '{{WG_SERVER_PUB}}'       => $this->rosEscape($wgServerPublicKey),
            '{{WG_ROUTER_ADDRESS}}'   => $this->rosEscape($wgRouterAddress),
            '{{WG_SERVER_ADDRESS}}'   => $this->rosEscape($wgServerAddress),
            '{{RADIUS_IP}}'           => $this->rosEscape($radiusIp),
            '{{RADIUS_SECRET}}'       => $this->rosEscape($radiusSecret),
            '{{HOTSPOT_BASE}}'        => $this->rosEscape($hotspotBase),
            '{{SNMP_COMMUNITY}}'      => $this->rosEscape($snmpCommunity),
            '{{HOTSPOT_DNS_NAME}}'    => $this->rosEscape($hotspotDnsName),
            '{{HOTSPOT_LAN_IP}}'      => $this->rosEscape($hotspotLanIp),
            '{{HOTSPOT_LAN_CIDR}}'    => $this->rosEscape($hotspotLanCidr),
            '{{HOTSPOT_NETWORK}}'     => $this->rosEscape($hotspotNetwork),
            '{{HOTSPOT_POOL_NAME}}'   => $this->rosEscape($hotspotPoolName),
            '{{HOTSPOT_SERVER}}'      => $this->rosEscape($hotspotServer),
            '{{HOTSPOT_PROFILE}}'     => $this->rosEscape($hotspotProfile),
            '{{HOTSPOT_DHCP}}'        => $this->rosEscape($hotspotDhcp),
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
    <p class="subtitle">Login to continue browsing</p>

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

    private function ensureStableRadiusSecret(Router $router, bool $saveImmediately = true): void
    {
        if (!empty($router->radius_secret)) {
            return;
        }

        $router->radius_secret = bin2hex(random_bytes(16));

        if ($saveImmediately) {
            $router->save();
        }
    }

    private function rosEscape(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\"'], $value);
    }
}