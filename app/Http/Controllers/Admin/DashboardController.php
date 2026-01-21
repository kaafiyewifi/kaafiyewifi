<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Halkan kadib waxaan ku xireynaa DB (Locations, Hotspots, Customers, Revenue, iwm)
        $stats = [
            'locations' => 0,
            'hotspots' => 0,
            'online_hotspots' => 0,
            'active_users' => 0,
            'revenue_today' => 0,
        ];

        $quickStatus = [
            ['label' => 'Auth + Roles', 'ok' => true],
            ['label' => 'Admin Layout', 'ok' => true],
            ['label' => 'Dark/Light', 'ok' => true],
            ['label' => 'No 500 errors', 'ok' => true],
        ];

        $recentActivity = [
            ['title' => 'System ready', 'meta' => 'Dashboard loaded', 'time' => 'Just now'],
            ['title' => 'Next step', 'meta' => 'Locations & Hotspots', 'time' => 'Today'],
        ];

        return view('admin.home', compact('stats', 'quickStatus', 'recentActivity'));
    }
}
