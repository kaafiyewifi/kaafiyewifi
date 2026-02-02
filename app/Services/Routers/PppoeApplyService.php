<?php

// app/Services/Routers/PppoeApplyService.php
namespace App\Services\Routers;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;

class PppoeApplyService
{
    public function apply(Router $router, array $cfg, RouterApi $api): void
    {
        $server = 'pppoe-kaafiye';
        $profile = 'ppp-prof-kaafiye';

        $api->connect($router);

        // PPP profile with RADIUS
        $profiles = $api->query('/ppp/profile/print', ['?name' => $profile]);
        if (count($profiles) === 0) {
            $api->command('/ppp/profile/add', [
                'name' => $profile,
                'use-radius' => 'yes',
                'only-one' => 'yes',
            ]);
        }

        // PPPoE server (interface = bridge)
        $servers = $api->query('/interface/pppoe-server/server/print', ['?service-name' => $server]);
        if (count($servers) === 0) {
            $api->command('/interface/pppoe-server/server/add', [
                'service-name' => $server,
                'interface' => $cfg['interface'] ?? 'br-kaafiye',
                'default-profile' => $profile,
                'one-session-per-host' => 'yes',
                'disabled' => 'no',
            ]);
        }

        $api->disconnect();
    }
}
