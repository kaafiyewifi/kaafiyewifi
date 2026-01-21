<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private function assertLocationAccess(Location $location): void
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return;
        }

        abort_unless($user->canAccessLocation($location->id), 403);
    }

    public function index()
    {
        $user = auth()->user();

        $q = Location::query();

        // scoping
        if (!$user->hasRole('super_admin')) {
            $q->whereIn('id', $user->allowedLocationIds());
        }

        $locations = $q->latest()->paginate(15);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        // Create location: allowed for super_admin (recommended)
        // Haddii aad rabto admin inuu sameeyo location, ka saar check-kan.
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $data = $request->validate([
            'name'    => ['required','string','max:120'],
            'status'  => ['required','in:active,inactive'],
            'address' => ['nullable','string','max:200'],
            'city'    => ['nullable','string','max:120'],
        ]);

        $location = Location::create($data); // code auto on model (kw001...)

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Location created.');
    }

    public function show(Location $location)
    {
        $this->assertLocationAccess($location);

        return view('admin.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        // edit: super_admin only (recommended)
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $data = $request->validate([
            'name'    => ['required','string','max:120'],
            'status'  => ['required','in:active,inactive'],
            'address' => ['nullable','string','max:200'],
            'city'    => ['nullable','string','max:120'],
        ]);

        $location->update($data);

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Location updated.');
    }

    public function destroy(Location $location)
    {
        abort_unless(auth()->user()->hasRole('super_admin'), 403);

        $location->delete();

        return redirect()
            ->route('admin.locations.index')
            ->with('success', 'Location deleted.');
    }
}
