<?php


// app/Jobs/Routers/PollRouterStatusJob.php
namespace App\Jobs\Routers;

use App\Enums\RouterStatus;
use App\Models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollRouterStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $routerId) {}

    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);

        // Phase 4A: easiest baseline = ping mgmt_host (ICMP)
        // Later: SNMP get CPU/RAM uptime
        $host = $router->mgmt_host;

        if (!$host) return;

        $ok = $this->ping($host);

        $router->status = $ok ? RouterStatus::Connected : RouterStatus::Offline;
        $router->last_seen_at = $ok ? now() : $router->last_seen_at;
        $router->save();

        $router->events()->create([
            'type' => 'monitor.ping',
            'payload' => ['ok' => $ok],
            'created_at' => now(),
        ]);
    }

    private function ping(string $host): bool
    {
        $cmd = "ping -c 1 -W 1 " . escapeshellarg($host) . " >/dev/null 2>&1; echo $?";
        $code = (int) trim(shell_exec($cmd) ?? '1');
        return $code === 0;
    }
}
