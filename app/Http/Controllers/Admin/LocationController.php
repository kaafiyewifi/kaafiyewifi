<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(){
        $locations = Location::latest()->get();
        return view('admin.locations.index',compact('locations'));
    }

    public function store(Request $r){
        Location::create($r->validate([
            'name'=>'required'
        ]));
        return back();
    }

    public function update(Request $r, Location $location){
        $location->update($r->validate([
            'name'=>'required'
        ]));
        return back();
    }

    public function destroy(Location $location){
        $location->delete();
        return back();
    }
}
