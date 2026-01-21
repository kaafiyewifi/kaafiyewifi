<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Location;
use App\Services\MikrotikV7ScriptGenerator;
use Illuminate\Http\Request;

class LocationHotspotController extends Controller
{
    public function index(Location $location)
    {
        $hotspots = Hotspot::where('location_id', $location->id)
            ->latest()
            ->paginate(15);

        return view('admin.hotspots.index', compact('location','hotspots'));
    }

    public function create(Location $location)
    {
        return view('admin.hotspots.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $data = $request->validate([
            'ssid_name' => ['required','string','max:120'],
            'physical_address' => ['nullable','string','max:200'],
            'nat_type' => ['required','in:mikrotik,cambium,tanaza'],
            'setup_type' => ['required','in:simple,advanced'],
            'setup_profile' => ['required','in:full_wg,full_ovpn,vpn_wg,vpn_ovpn'],
            'status' => ['required','in:active,inactive'],
            'vpn_ip' => ['nullable','string','max:64'],
        ]);

        $data['location_id'] = $location->id;

        $hotspot = Hotspot::create($data);

        return redirect()
            ->route('admin.locations.hotspots.index', $location)
            ->with('success', 'Hotspot created.');
    }

    public function show(Location $location, Hotspot $hotspot)
    {
        $this->ensureSameLocation($location, $hotspot);
        return view('admin.hotspots.show', compact('location','hotspot'));
    }

    public function edit(Location $location, Hotspot $hotspot)
    {
        $this->ensureSameLocation($location, $hotspot);
        return view('admin.hotspots.edit', compact('location','hotspot'));
    }

    public function update(Request $request, Location $location, Hotspot $hotspot)
    {
        $this->ensureSameLocation($location, $hotspot);

        $data = $request->validate([
            'ssid_name' => ['required','string','max:120'],
            'physical_address' => ['nullable','string','max:200'],
            'nat_type' => ['required','in:mikrotik,cambium,tanaza'],
            'setup_type' => ['required','in:simple,advanced'],
            'setup_profile' => ['required','in:full_wg,full_ovpn,vpn_wg,vpn_ovpn'],
            'status' => ['required','in:active,inactive'],
            'vpn_ip' => ['nullable','string','max:64'],
        ]);

        $hotspot->update($data);

        return redirect()
            ->route('admin.locations.hotspots.show', [$location, $hotspot])
            ->with('success', 'Hotspot updated.');
    }

    public function destroy(Location $location, Hotspot $hotspot)
    {
        $this->ensureSameLocation($location, $hotspot);

        $hotspot->delete();

        return redirect()
            ->route('admin.locations.hotspots.index', $location)
            ->with('success', 'Hotspot deleted.');
    }

    public function generateScript(Location $location, Hotspot $hotspot, MikrotikV7ScriptGenerator $gen)
    {
        $this->ensureSameLocation($location, $hotspot);

        // one-time generate (strict)
        if ($hotspot->script_generated_at) {
            return redirect()
                ->route('admin.locations.hotspots.script', [$location, $hotspot])
                ->with('info', 'Script already generated. Showing snapshot.');
        }

        // Only Mikrotik supported for now
        if ($hotspot->nat_type !== 'mikrotik') {
            return back()->with('error', 'Script generation currently supports MikroTik only.');
        }

        $script = $gen->generate($location, $hotspot);

        $hotspot->forceFill([
            'script_snapshot' => $script,
            'script_generated_at' => now(),
            'script_version' => 1,
        ])->save();

        return redirect()
            ->route('admin.locations.hotspots.script', [$location, $hotspot])
            ->with('success', 'Script generated. Copy & paste into MikroTik.');
    }

    public function viewScript(Location $location, Hotspot $hotspot)
    {
        $this->ensureSameLocation($location, $hotspot);

        if (!$hotspot->script_snapshot) {
            return redirect()
                ->route('admin.locations.hotspots.show', [$location, $hotspot])
                ->with('info', 'No script yet. Click Generate Script.');
        }

        return view('admin.hotspots.script', compact('location','hotspot'));
    }

    private function ensureSameLocation(Location $location, Hotspot $hotspot): void
    {
        if ($hotspot->location_id !== $location->id) {
            abort(404);
        }
    }
}
