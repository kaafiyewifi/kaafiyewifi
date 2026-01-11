<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Location;
use Illuminate\Http\Request;

class HotspotController extends Controller
{
    public function index(){
        $hotspots = Hotspot::with('location')->latest()->get();
        $locations = Location::all();
        return view('admin.hotspots.index',compact('hotspots','locations'));
    }

    public function store(Request $r){
        Hotspot::create($r->validate([
            'location_id'=>'required',
            'name'=>'required',
            'ssid'=>'required',
            'max_users'=>'nullable|integer',
            'download_speed'=>'nullable|integer',
            'upload_speed'=>'nullable|integer',
            'speed_unit'=>'required'
        ]));
        return back();
    }

    public function update(Request $r, Hotspot $hotspot){
        $hotspot->update($r->all());
        return back();
    }

    public function destroy(Hotspot $hotspot){
        $hotspot->delete();
        return back();
    }
}
