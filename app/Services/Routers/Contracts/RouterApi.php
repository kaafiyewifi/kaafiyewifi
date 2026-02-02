<?php

// app/Services/Routers/Contracts/RouterApi.php
// app/Services/Routers/Contracts/RouterApi.php
namespace App\Services\Routers\Contracts;

use App\Models\Router;

interface RouterApi
{
    public function connect(Router $router): void;

    /** @return array<string,mixed> */
    public function command(string $path, array $params = []): array;

    /** @return array<int,array<string,mixed>> */
    public function query(string $path, array $params = []): array;

    public function disconnect(): void;
}
