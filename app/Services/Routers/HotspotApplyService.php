<?php

// app/Services/Routers/HotspotApplyService.php
namespace App\Services\Routers;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;

class HotspotApplyService
{
    public function apply(Router $router, array $cfg, RouterApi $api): void
    {
        $bridge = 'br-kaafiye';
        $pool   = 'pool-kaafiye-hotspot';
        $profile= 'hsprof-kaafiye';
        $server = 'hotspot-kaafiye';

        $subnet = $cfg['subnet'] ?? '172.31.0.0/16';
        // Simple default gateway: 172.31.0.1
        $gateway = '172.31.0.1';
        $netmask = '16';

        $dns1 = $cfg['dns1'] ?? '8.8.8.8';
        $dns2 = $cfg['dns2'] ?? '1.1.1.1';

        $api->connect($router);

        // 1) Bridge exists (Phase 3 created it, but keep safe)
        $api->command('/interface/bridge/add', ['name' => $bridge]);

        // 2) IP address on bridge (skip if exists)
        // (PEAR2: better to query then add)
        $ips = $api->query('/ip/address/print', ['?interface' => $bridge]);
        if (count($ips) === 0) {
            $api->command('/ip/address/add', ['address' => "{$gateway}/{$netmask}", 'interface' => $bridge]);
        }

        // 3) Pool + DHCP (Hotspot needs IP distribution; DHCP is simplest)
        $pools = $api->query('/ip/pool/print', ['?name' => $pool]);
        if (count($pools) === 0) {
            $api->command('/ip/pool/add', ['name' => $pool, 'ranges' => '172.31.0.10-172.31.255.254']);
        }

        // DHCP server
        $dhcps = $api->query('/ip/dhcp-server/print', ['?name' => 'dhcp-kaafiye']);
        if (count($dhcps) === 0) {
            $api->command('/ip/dhcp-server/add', [
                'name' => 'dhcp-kaafiye',
                'interface' => $bridge,
                'address-pool' => $pool,
                'disabled' => 'no',
            ]);
        }

        // DHCP network
        $nets = $api->query('/ip/dhcp-server/network/print', ['?address' => '172.31.0.0/16']);
        if (count($nets) === 0) {
            $api->command('/ip/dhcp-server/network/add', [
                'address' => '172.31.0.0/16',
                'gateway' => $gateway,
                'dns-server' => "{$dns1},{$dns2}",
            ]);
        }

        // 4) Hotspot profile
        $profiles = $api->query('/ip/hotspot/profile/print', ['?name' => $profile]);
        if (count($profiles) === 0) {
            $api->command('/ip/hotspot/profile/add', [
                'name' => $profile,
                'hotspot-address' => $gateway,
                'dns-name' => $cfg['dns_name'] ?? 'login.kaafiye.online',
                'login-by' => 'http-chap,https',
                'use-radius' => 'yes',
            ]);
        }

        // 5) Hotspot server
        $servers = $api->query('/ip/hotspot/print', ['?name' => $server]);
        if (count($servers) === 0) {
            $api->command('/ip/hotspot/add', [
                'name' => $server,
                'interface' => $bridge,
                'profile' => $profile,
                'address-pool' => $pool,
                'disabled' => 'no',
            ]);
        }

        // 6) Walled garden minimal (Phase 5 we’ll extend)
        // Example: allow your portal domain
        $wg = $api->query('/ip/hotspot/walled-garden/print', ['?dst-host' => 'app.kaafiye.online']);
        if (count($wg) === 0) {
            $api->command('/ip/hotspot/walled-garden/add', [
                'dst-host' => 'app.kaafiye.online',
                'action' => 'allow',
            ]);
        }

        $api->disconnect();
    }
}
