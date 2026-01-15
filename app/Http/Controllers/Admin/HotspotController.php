<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\MikroTikService;

class HotspotController extends Controller
{
    public function index(Request $request)
    {
        $query = Hotspot::with('location')->latest();

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        $hotspots  = $query->paginate(10)->withQueryString();
        $locations = Location::orderBy('name')->get();

        return view('admin.hotspots.index', compact('hotspots', 'locations'));
    }

    public function store(Request $request)
    {
        Hotspot::create($request->validate([
            'location_id'=>'required|exists:locations,id',
            'name'=>'required',
            'ssid'=>'required',

            'max_users'=>'nullable|integer',
            'download_speed'=>'nullable|integer',
            'upload_speed'=>'nullable|integer',
            'speed_unit'=>'required',

            // PHASE 5
            'nas_type'=>'required',
            'physical_address'=>'nullable',
            'router_ip'=>'nullable',
            'api_port'=>'nullable',
            'api_user'=>'nullable',
            'api_pass'=>'nullable',
        ]));

        return back()->with('toast',[
            'type'=>'success',
            'message'=>'Hotspot created successfully'
        ]);
    }

    public function update(Request $r, Hotspot $hotspot)
    {
        $hotspot->update($r->validate([
            'name'=>'required',
            'ssid'=>'required',
            'max_users'=>'nullable|integer',
            'download_speed'=>'nullable|integer',
            'upload_speed'=>'nullable|integer',
            'speed_unit'=>'required',

            // phase 5
            'nas_type'=>'required',
            'physical_address'=>'nullable',
            'router_ip'=>'nullable',
            'api_port'=>'nullable',
            'api_user'=>'nullable',
            'api_pass'=>'nullable',
        ]));

        return back()->with('toast',[
            'type'=>'success',
            'message'=>'Hotspot updated successfully'
        ]);
    }

    public function destroy(Hotspot $hotspot)
    {
        $hotspot->delete();

        return back()->with('toast',[
            'type'=>'error',
            'message'=>'Hotspot deleted'
        ]);
    }

    // 🔥 PHASE 5 — TEST MIKROTIK CONNECTION
    public function testRouter(Hotspot $hotspot)
    {
        try {
            $mt = new MikroTikService($hotspot);
            $mt->test();

            return response()->json([
                'success'=>true,
                'message'=>'MikroTik connected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ]);
        }
    }
}
