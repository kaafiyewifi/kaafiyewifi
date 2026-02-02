<?php

// app/Services/Routers/WalledGardenService.php
namespace App\Services\Routers;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;

class WalledGardenService
{
    public function applyHosts(Router $router, RouterApi $api, array $hosts): void
    {
        $api->connect($router);

        foreach ($hosts as $host) {
            $host = trim((string)$host);
            if ($host === '') continue;

            // Check if exists
            $existing = $api->query('/ip/hotspot/walled-garden/print', ['?dst-host' => $host]);
            if (count($existing) === 0) {
                $api->command('/ip/hotspot/walled-garden/add', [
                    'dst-host' => $host,
                    'action' => 'allow',
                    'comment' => 'Kaafiye WG',
                ]);
            }
        }

        $api->disconnect();
    }
}
