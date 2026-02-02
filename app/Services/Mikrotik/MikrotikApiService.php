<?php

namespace App\Services\Mikrotik;

use App\Models\Router;
use Illuminate\Support\Facades\Crypt;

class MikrotikApiService
{
    public function creds(Router $router): array
    {
        return [
            'host' => $router->mgmt_ip,
            'port' => $router->api_port,
            'user' => $router->api_username,
            'pass' => Crypt::decryptString($router->api_password),
        ];
    }

    // Next step: connect using your chosen RouterOS API client library
    // Then implement:
    // - addHotspotUser()
    // - disableHotspotUser()
    // - disconnectHotspotActive()
    // - addPppSecret()
    // - disablePppSecret()
}
