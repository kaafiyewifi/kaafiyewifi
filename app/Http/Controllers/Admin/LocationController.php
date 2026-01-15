<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /* =====================
     * INDEX
     * ===================*/
    public function index()
    {
        $locations = Location::latest()->paginate(10);
        return view('admin.locations.index', compact('locations'));
    }

    /* =====================
     * SHOW (LOCATION PROFILE) ✅
     * ===================*/
    public function show(Location $location)
    {
        // Load related hotspots
        $location->load('hotspots');

        return view('admin.locations.show', compact('location'));
    }

    /* =====================
     * STORE (AJAX)
     * ===================*/
    public function store(Request $request)
    {
        $location = Location::create(
            $request->validate([
                'name' => 'required|string|max:255'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Location si guul leh ayaa loo daray',
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'created_at' => $location->created_at->format('d M Y'),
            ]
        ]);
    }

    /* =====================
     * UPDATE (AJAX)
     * ===================*/
    public function update(Request $request, Location $location)
    {
        $location->update(
            $request->validate([
                'name' => 'required|string|max:255'
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Location waa la update gareeyay',
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ]
        ]);
    }

    /* =====================
     * DESTROY
     * ===================*/
    public function destroy(Location $location)
    {
        $location->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Location waa la tirtiray'
        ]);
    }
}
