<?php

namespace App\Jobs\Routers;

use App\Models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReprovisionRouterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $routerId) {}

    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);

        // TODO: create new token, set provisioning_status, etc.
        $router->update([
            'provisioning_status' => 'command_pending',
        ]);

        // TODO: create event log
    }
}
