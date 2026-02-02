<?php

namespace App\Jobs\Routers;

use App\Enums\RouterStatus;
use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;
use App\Services\Routers\HotspotApplyService;
use App\Services\Routers\HotspotFilesApplyService;
use App\Services\Routers\PppoeApplyService;
use App\Services\Routers\WalledGardenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ApplyRouterServicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;
    public int $tries = 3;

    public function __construct(public int $routerId)
    {
    }

    public function handle(
        RouterApi $api,
        HotspotApplyService $hotspot,
        PppoeApplyService $pppoe,
        HotspotFilesApplyService $files,
        WalledGardenService $wg
    ): void {
        /** @var Router $router */
        $router = Router::query()
            ->with(['services', 'credentials'])
            ->findOrFail($this->routerId);

        // Enabled services
        /** @var EloquentCollection $enabled */
        $enabled = $router->services->where('is_enabled', true)->values();

        $services = $enabled->pluck('service')->all();

        if (count($services) === 0) {
            $router->events()->create([
                'type' => 'services.apply.skipped',
                'payload' => ['reason' => 'No enabled services'],
                'created_at' => now(),
            ]);
            return;
        }

        // Base config from first enabled service (shared step config)
        $cfg = (array) ($enabled->first()?->config ?? []);

        // Defaults (safe)
        $ports = (array) ($cfg['ports'] ?? []);
        $useCustom = (bool) ($cfg['use_custom_subnet'] ?? false);
        $subnet = ($useCustom && !empty($cfg['subnet'])) ? (string) $cfg['subnet'] : '172.31.0.0/16';

        // Normalize cfg for downstream services
        $cfg['ports'] = $ports;
        $cfg['subnet'] = $subnet;

        // Mark router provisioning
        $router->status = RouterStatus::Provisioning;
        $router->save();

        // Log start
        $router->events()->create([
            'type' => 'services.apply.started',
            'payload' => [
                'services' => $services,
                'ports' => $ports,
                'subnet' => $subnet,
            ],
            'created_at' => now(),
        ]);

        try {
            // Connect once, reuse session for all operations
            $api->connect($router);

            // HOTSPOT
            if (in_array('hotspot', $services, true)) {
                // Phase 4: hotspot setup
                $hotspot->apply($router, $cfg, $api);

                // Phase 5.5.1: download portal files
                $files->apply($router, $api);

                // Phase 5.5.2: walled garden allow-list
                $hosts = config('routers.walled_garden_hosts', []);
                if (is_string($hosts)) {
                    $hosts = array_filter(array_map('trim', explode(',', $hosts)));
                }
                if (!is_array($hosts)) {
                    $hosts = [];
                }

                $wg->applyHosts($router, $api, $hosts);

                $router->events()->create([
                    'type' => 'services.hotspot.applied',
                    'payload' => [
                        'subnet' => $subnet,
                        'ports' => $ports,
                        'portal_files' => true,
                        'walled_garden_hosts' => $hosts,
                    ],
                    'created_at' => now(),
                ]);
            }

            // PPPOE
            if (in_array('pppoe', $services, true)) {
                // PPPoE generally runs on bridge interface
                $cfg['interface'] = $cfg['interface'] ?? 'br-kaafiye';

                $pppoe->apply($router, $cfg, $api);

                $router->events()->create([
                    'type' => 'services.pppoe.applied',
                    'payload' => [
                        'interface' => $cfg['interface'],
                    ],
                    'created_at' => now(),
                ]);
            }

            // Disconnect cleanly
            $api->disconnect();

            // Mark connected (monitoring will keep checking)
            $router->status = RouterStatus::Connected;
            $router->last_seen_at = now();
            $router->save();

            $router->events()->create([
                'type' => 'services.apply.success',
                'payload' => [
                    'services' => $services,
                ],
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Always disconnect on error (best-effort)
            try {
                $api->disconnect();
            } catch (Throwable $ignored) {
            }

            $router->status = RouterStatus::Error;
            $router->save();

            $router->events()->create([
                'type' => 'services.apply.failed',
                'payload' => [
                    'services' => $services,
                    'message' => $e->getMessage(),
                    'class' => get_class($e),
                ],
                'created_at' => now(),
            ]);

            // Allow queue retries (tries=3)
            throw $e;
        }
    }
}
