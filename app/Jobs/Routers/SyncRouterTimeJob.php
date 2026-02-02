<?php

namespace App\Jobs\Routers;

use App\Models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRouterTimeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $routerId) {}

    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);

        // TODO: MikroTik: /system/clock/set time-zone-name=...
        // TODO: event log
    }
}
