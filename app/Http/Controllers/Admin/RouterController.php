<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Router;
use App\Models\Location;
use App\Services\MikroTikService;

class RouterController extends Controller
{
    /* =======================
     * INDEX
     * ======================= */
    public function index()
    {
        $routers = Router::with('location')
            ->latest()
            ->paginate(10);

        return view('admin.routers.index', compact('routers'));
    }

    /* =======================
     * CREATE
     * ======================= */
    public function create()
    {
        $locations = Location::all();
        return view('admin.routers.create', compact('locations'));
    }

    /* =======================
     * STORE
     * ======================= */
    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name'        => 'required|string|max:255',
            'ip_address'  => 'required|ip',
            'api_port'    => 'required|integer',
            'username'    => 'required|string',
            'password'    => 'required|string',
            'is_active'   => 'boolean',
        ]);

        Router::create($data);

        return redirect()
            ->route('admin.routers.index')
            ->with('success', 'Router created successfully');
    }

    /* =======================
     * EDIT
     * ======================= */
    public function edit(Router $router)
    {
        $locations = Location::all();
        return view('admin.routers.edit', compact('router', 'locations'));
    }

    /* =======================
     * UPDATE
     * ======================= */
    public function update(Request $request, Router $router)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'name'        => 'required|string|max:255',
            'ip_address'  => 'required|ip',
            'api_port'    => 'required|integer',
            'username'    => 'required|string',
            'password'    => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $router->update($data);

        return redirect()
            ->route('admin.routers.index')
            ->with('success', 'Router updated successfully');
    }

    /* =======================
     * DELETE
     * ======================= */
    public function destroy(Router $router)
    {
        $router->delete();

        return back()->with('success', 'Router deleted successfully');
    }

    /* =======================
     * ONLINE USERS (MikroTik)
     * ======================= */
    public function onlineUsers(MikroTikService $mikrotik)
    {
        $users = $mikrotik->getOnlineUsers();

        return view('admin.routers.online-users', compact('users'));
    }
}
