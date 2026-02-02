<?php

// app/Services/Routers/HotspotFilesApplyService.php
namespace App\Services\Routers;

use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;

class HotspotFilesApplyService
{
    public function apply(Router $router, RouterApi $api): void
    {
        $base = rtrim(config('routers.hotspot_files_base'), '/'); // https://app.kaafiye.online/hotspot-files
        $id = $router->id;

        $api->connect($router);

        // Ensure hotspot folder exists (RouterOS keeps "hotspot/" usually available; no harm)
        // Download files (idempotent: overwrite OK)
        $api->command('/tool/fetch', [
            'mode' => 'https',
            'url' => "{$base}/{$id}/login.html",
            'dst-path' => 'hotspot/login.html',
        ]);
        $api->command('/tool/fetch', [
            'mode' => 'https',
            'url' => "{$base}/{$id}/alogin.html",
            'dst-path' => 'hotspot/alogin.html',
        ]);
        $api->command('/tool/fetch', [
            'mode' => 'https',
            'url' => "{$base}/{$id}/error.html",
            'dst-path' => 'hotspot/error.html',
        ]);
        $api->command('/tool/fetch', [
            'mode' => 'https',
            'url' => "{$base}/{$id}/logout.html",
            'dst-path' => 'hotspot/logout.html',
        ]);

        $api->disconnect();
    }
}
