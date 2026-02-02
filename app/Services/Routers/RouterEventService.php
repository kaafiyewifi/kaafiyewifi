<?php

namespace App\Services\Routers;

use App\Models\Router;
use Illuminate\Support\Facades\Schema;

class RouterEventService
{
    public function log(
        Router $router,
        string $type,
        array $payload = []
    ): void {
        if (!Schema::hasTable('router_events')) {
            return;
        }

        if (!method_exists($router, 'events')) {
            return;
        }

        $router->events()->create([
            'type' => $type,
            'payload' => $payload ?: null,
            'created_at' => now(),
        ]);
    }
}
