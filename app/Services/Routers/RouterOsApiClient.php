<?php

// app/Services/Routers/RouterOsApiClient.php
namespace App\Services\Routers;

use App\Models\Router;
use Exception;

class RouterOsApiClient
{
    public function connect(Router $router): void
    {
        // Phase 4: ku xiro real RouterOS API library
        // Hadda: structure only
        if (!$router->mgmt_host) {
            throw new Exception('Router mgmt_host is missing.');
        }
    }

    public function run(Router $router, array $commands): array
    {
        $this->connect($router);

        // Phase 4: implement real API calls, return results
        // For now, we return dummy to keep architecture ready.
        return [
            'ok' => true,
            'executed' => $commands,
        ];
    }
}
