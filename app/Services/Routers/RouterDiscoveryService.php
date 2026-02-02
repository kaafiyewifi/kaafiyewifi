<?php

// app/Services/Routers/RouterDiscoveryService.php
namespace App\Services\Routers;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;

class RouterDiscoveryService
{
    public function listPorts(Router $router, RouterApi $api): array
    {
        $api->connect($router);

        // Interfaces list
        $rows = $api->query('/interface/print');

        $api->disconnect();

        // filter typical LAN ports
        $ports = [];
        foreach ($rows as $r) {
            $name = $r['name'] ?? null;
            if (!$name) continue;

            // include ether*/wlan* by default
            if (str_starts_with($name, 'ether') || str_starts_with($name, 'wlan')) {
                $ports[] = $name;
            }
        }

        sort($ports);
        return $ports;
    }
}
