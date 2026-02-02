<?php

namespace App\Services\Routers;

use App\Models\Router;
use App\Models\RouterProvision;

class RscBuilderService
{
    public function build(Router $router, RouterProvision $provision): string
    {
        /**
         * IMPORTANT (Option 1):
         * Existing WireGuard network is 10.50.0.0/24
         * VPS WireGuard IP (RADIUS server) is 10.50.0.2
         */
        $radiusHost   = config('routers.radius_host'); // MUST be 10.50.0.2 in .env
        $radiusSecret = config('routers.radius_secret');

        $hsProfile = config('routers.hotspot_profile', 'hsprof-kaafiye');
        $filesBase = rtrim(config('routers.hotspot_files_base'), '/');

        // WireGuard server info
        $wgIf       = config('routers.wg.ifname', 'wg0'); // Use existing wg0 by default
        $wgEndpoint = config('routers.wg.vps_endpoint');  // 62.171.140.146:51820
        $wgVpsPubKey= config('routers.wg.vps_public_key'); // RApWo...
        // Existing WG network
        $wgAllowed  = '10.50.0.0/24';

        // Router WireGuard IP (deterministic): 10.50.0.(100 + router_id)/32
        $routerIpLast = 100 + (int) $router->id;
        $wgRouterIp   = "10.50.0.$routerIpLast/32";

        // Parse endpoint (host:port)
        $endpointHost = $wgEndpoint ? explode(':', $wgEndpoint)[0] : '';
        $endpointPort = $wgEndpoint ? (int) (explode(':', $wgEndpoint)[1] ?? 51820) : 51820;

        $lines = [];

        $lines[] = '# Kaafiye Provision Script (WireGuard 10.50.0.0/24 + RADIUS + Hotspot files)';
        $lines[] = ':log info "Kaafiye provisioning started...";';

        // --- WireGuard ---
        $lines[] = ':log info "Configuring WireGuard (10.50.0.0/24)...";';
        $lines[] = ':if ([:len [/interface wireguard find name="'.$wgIf.'"]] = 0) do={';
        $lines[] = '  /interface wireguard add name="'.$wgIf.'" listen-port=51820 comment="Kaafiye WG";';
        $lines[] = '  :log info "WireGuard interface created";';
        $lines[] = '} else={ :log info "WireGuard interface exists"; }';

        // Set router wg address
        $lines[] = ':if ([:len [/ip address find interface="'.$wgIf.'"]] = 0) do={';
        $lines[] = '  /ip address add address="'.$wgRouterIp.'" interface="'.$wgIf.'" comment="Kaafiye WG IP";';
        $lines[] = '  :log info "WireGuard IP assigned";';
        $lines[] = '}';

        // Add VPS peer (ensure only one peer on that interface)
        $lines[] = ':if ([:len [/interface wireguard peers find interface="'.$wgIf.'" ]] = 0) do={';
        $lines[] = '  /interface wireguard peers add interface="'.$wgIf.'" name="kaafiye-vps" public-key="'.$wgVpsPubKey.'" endpoint-address="'.$endpointHost.'" endpoint-port='.$endpointPort.' allowed-address="'.$wgAllowed.'" persistent-keepalive=25s comment="Kaafiye VPS Peer";';
        $lines[] = '  :log info "WireGuard peer added";';
        $lines[] = '} else={ :log info "WireGuard peer exists"; }';

        // --- RADIUS ---
        $lines[] = ':log info "Configuring RADIUS over WireGuard...";';
        $lines[] = ':if ([:len [/radius find service=hotspot address="'.$radiusHost.'"]] = 0) do={';
        $lines[] = '  /radius add service=hotspot address="'.$radiusHost.'" secret="'.$radiusSecret.'" authentication-port=1812 accounting-port=1813 timeout=1s;';
        $lines[] = '  :log info "RADIUS hotspot added";';
        $lines[] = '} else={ :log info "RADIUS hotspot exists"; }';

        $lines[] = ':if ([:len [/radius find service=ppp address="'.$radiusHost.'"]] = 0) do={';
        $lines[] = '  /radius add service=ppp address="'.$radiusHost.'" secret="'.$radiusSecret.'" authentication-port=1812 accounting-port=1813 timeout=1s;';
        $lines[] = '  :log info "RADIUS ppp added";';
        $lines[] = '} else={ :log info "RADIUS ppp exists"; }';

        // PPP AAA + interim update
        $lines[] = '/ppp aaa set use-radius=yes accounting=yes interim-update=1m;';
        $lines[] = ':log info "PPP AAA enabled + interim update 1m";';

        // Hotspot profile use-radius
        $lines[] = ':if ([:len [/ip hotspot profile find name="'.$hsProfile.'"]] > 0) do={';
        $lines[] = '  /ip hotspot profile set [find name="'.$hsProfile.'"] use-radius=yes;';
        $lines[] = '  :log info "Hotspot profile use-radius enabled";';
        $lines[] = '} else={ :log warning "Hotspot profile not found: '.$hsProfile.'"; }';

        // CoA incoming optional
        $lines[] = '/radius incoming set accept=yes port=3799;';
        $lines[] = ':log info "CoA incoming enabled (3799)";';

        // --- Hotspot portal files download ---
        $lines[] = ':log info "Downloading hotspot files...";';
        $lines[] = '/tool fetch mode=https url="'.$filesBase.'/'.$router->id.'/login.html" dst-path=hotspot/login.html;';
        $lines[] = '/tool fetch mode=https url="'.$filesBase.'/'.$router->id.'/alogin.html" dst-path=hotspot/alogin.html;';
        $lines[] = '/tool fetch mode=https url="'.$filesBase.'/'.$router->id.'/error.html" dst-path=hotspot/error.html;';
        $lines[] = ':log info "Hotspot files downloaded";';

        // --- Walled garden allow your app domain ---
        $lines[] = ':if ([:len [/ip hotspot walled-garden find dst-host="app.kaafiye.online"]] = 0) do={';
        $lines[] = '  /ip hotspot walled-garden add dst-host="app.kaafiye.online" action=allow;';
        $lines[] = '  :log info "Walled garden added: app.kaafiye.online";';
        $lines[] = '}';

        $lines[] = ':log info "Kaafiye provisioning completed successfully";';

        return implode("\n", $lines)."\n";
    }
}
