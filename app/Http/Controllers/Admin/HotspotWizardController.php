<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Location;
use Illuminate\Http\Request;

class HotspotController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q'));
        $locationId = $request->query('location_id');
        $status = $request->query('status');

        $locations = Location::query()->orderBy('name')->get(['id','name']);

        $hotspots = Hotspot::query()
            ->with('location:id,name,physical_address')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('router_id', 'like', "%{$q}%")
                      ->orWhere('vpn_ip', 'like', "%{$q}%");
            })
            ->when($locationId, fn($query) => $query->where('location_id', $locationId))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hotspots.index', compact('hotspots','locations','q','locationId','status'));
    }

    public function show(Hotspot $hotspot)
    {
        $hotspot->load('location');
        return view('admin.hotspots.show', compact('hotspot'));
    }

    public function destroy(Hotspot $hotspot)
    {
        $hotspot->delete();

        return redirect()
            ->route('admin.hotspots.index')
            ->with('success', 'Hotspot deleted.');
    }

    // haddii aad hore u lahayd testRouter(), halkan ha ku reebato (ama ku dar)
    // public function testRouter(Hotspot $hotspot) { ... }
}
