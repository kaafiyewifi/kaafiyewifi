<?php

namespace App\Jobs\Routers;

use App\Models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncHotspotFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $routerId) {}

    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);

        // TODO: call MikroTik script/API that re-downloads hotspot-files
        // e.g. run fetch for: /hotspot-files/{routerId}/login.html etc.
    }
}
