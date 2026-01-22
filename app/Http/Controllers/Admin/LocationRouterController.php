<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Router;
use Illuminate\Http\Request;

class LocationRouterController extends Controller
{
    public function create(Location $location)
    {
        return view('admin.routers.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => ['nullable','string','max:120'],
            'identity' => ['required','string','max:190','unique:routers,identity'],
            'mgmt_ip' => ['nullable','string','max:45'],
            'public_ip' => ['nullable','string','max:45'],
            'api_port' => ['nullable','integer','min:1','max:65535'],
        ]);

        $router = Router::create([
            'name' => $data['name'] ?? null,
            'identity' => $data['identity'],
            'mgmt_ip' => $data['mgmt_ip'] ?? null,
            'public_ip' => $data['public_ip'] ?? null,
            'api_port' => $data['api_port'] ?? 8728,
            'status' => 'pending',
            'location_id' => $location->id, // ✅ forced
        ]);

        // optional: create default router_services row
        $router->service()->create([
            'hotspot_enabled' => false,
            'pppoe_enabled' => false,
            'anti_sharing_enabled' => false,
            'selected_ports' => null,
        ]);

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Router added to this location.');
    }
}
