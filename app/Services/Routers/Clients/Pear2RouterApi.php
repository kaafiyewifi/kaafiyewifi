<?php

// app/Services/Routers/Clients/Pear2RouterApi.php
// app/Services/Routers/Clients/Pear2RouterApi.php
namespace App\Services\Routers\Clients;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;
use Exception;

// PEAR2
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Query;

class Pear2RouterApi implements RouterApi
{
    private ?Client $client = null;

    public function connect(Router $router): void
    {
        $cred = $router->credentials;

        if (!$router->mgmt_host || !$cred?->username || !$cred?->password_encrypted) {
            throw new Exception("Router connection info missing.");
        }

        $this->client = new Client(
            $router->mgmt_host,
            $cred->username,
            $cred->password_encrypted,
            $router->api_port,
            3 // timeout seconds
        );
    }

    public function command(string $path, array $params = []): array
    {
        if (!$this->client) throw new Exception("Not connected.");

        $q = new Query($path);
        foreach ($params as $k => $v) {
            $q->equal((string)$k, (string)$v);
        }

        $resp = $this->client->query($q)->read();
        return ['ok' => true, 'resp' => $resp];
    }

    public function query(string $path, array $params = []): array
    {
        if (!$this->client) throw new Exception("Not connected.");

        $q = new Query($path);
        foreach ($params as $k => $v) {
            $q->equal((string)$k, (string)$v);
        }

        return $this->client->query($q)->read();
    }

    public function disconnect(): void
    {
        $this->client = null;
    }
}
