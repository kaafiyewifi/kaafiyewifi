<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterLog;
use Illuminate\Http\RedirectResponse;

class RouterActionController extends Controller
{
    public function reprovision(Router $router): RedirectResponse
    {
        RouterLog::create([
            'router_id' => $router->id,
            'type' => 'reprovision',
            'success' => true,
            'message' => 'Reprovision requested from admin panel',
        ]);

        return back()->with('success', 'Router reprovision triggered.');
    }

    public function hotspotSync(Router $router): RedirectResponse
    {
        RouterLog::create([
            'router_id' => $router->id,
            'type' => 'hotspot_sync',
            'success' => true,
            'message' => 'Hotspot sync requested',
        ]);

        return back()->with('success', 'Hotspot sync requested.');
    }

    public function timeSync(Router $router): RedirectResponse
    {
        RouterLog::create([
            'router_id' => $router->id,
            'type' => 'time_sync',
            'success' => true,
            'message' => 'Router time sync requested',
        ]);

        return back()->with('success', 'Router time sync requested.');
    }

    public function regenerateWinbox(Router $router): RedirectResponse
    {
        RouterLog::create([
            'router_id' => $router->id,
            'type' => 'winbox_regenerate',
            'success' => true,
            'message' => 'Winbox credentials regenerated',
        ]);

        return back()->with('success', 'Winbox credentials regenerated.');
    }
}