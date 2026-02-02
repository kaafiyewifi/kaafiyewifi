<?php

namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Jobs\Routers\ApplyRouterServicesJob;
use App\Models\Router;
use Illuminate\Http\Request;

class RouterServiceSetupController extends Controller
{
    public function show(Router $router)
    {
        // Phase 4: dynamic ports via RouterOS API
        $ports = ['ether2', 'ether3', 'ether4', 'ether5', 'wlan1'];

        return view('admin.routers.service-setup', [
            'router' => $router,
            'ports'  => $ports,
        ]);
    }

    public function store(Request $request, Router $router)
    {
        $data = $request->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['in:pppoe,hotspot'],

            'use_custom_subnet' => ['nullable'],
            'subnet' => ['nullable', 'string', 'max:50'],

            'ports' => ['required', 'array', 'min:1'],
            'ports.*' => ['string', 'max:20'],

            'anti_sharing' => ['nullable'],
        ]);

        $useCustom = $request->boolean('use_custom_subnet');
        $subnet = ($useCustom && !empty($data['subnet'])) ? $data['subnet'] : '172.31.0.0/16';

        $pppoeEnabled = in_array('pppoe', $data['services'], true);
        $hotspotEnabled = in_array('hotspot', $data['services'], true);

        // ✅ Your schema: 1 row per router in router_services
        $router->services()->updateOrCreate(
            ['router_id' => $router->id],
            [
                'pppoe_enabled' => $pppoeEnabled,
                'hotspot_enabled' => $hotspotEnabled,
                'ethernet_ports' => [
                    'ports' => $data['ports'],
                    'subnet' => $subnet,
                    'use_custom_subnet' => $useCustom,
                ],
                'anti_sharing' => $request->boolean('anti_sharing'),
            ]
        );

        // ✅ event log
        $router->events()->create([
            'type' => 'services.setup.saved',
            'payload' => [
                'pppoe_enabled' => $pppoeEnabled,
                'hotspot_enabled' => $hotspotEnabled,
                'ports' => $data['ports'],
                'subnet' => $subnet,
                'use_custom_subnet' => $useCustom,
                'anti_sharing' => $request->boolean('anti_sharing'),
            ],
            'created_at' => now(),
        ]);

        // ✅ async apply (Phase 4: real RouterOS API apply)
        ApplyRouterServicesJob::dispatch($router->id);

        return redirect()
            ->route('admin.routers.show', $router)
            ->with('success', 'Services saved. Router configuration job started.');
    }
}
