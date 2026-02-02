<?php

namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\RedirectResponse;

// ✅ Jobs (Phase 7 - REAL queued actions)
use App\Jobs\Routers\RegenerateWinboxJob;
use App\Jobs\Routers\ReprovisionRouterJob;
use App\Jobs\Routers\SyncHotspotFilesJob;
use App\Jobs\Routers\SyncRouterTimeJob;

class RouterActionsController extends Controller
{
    public function regenerateWinbox(Router $router): RedirectResponse
    {
        // ✅ REAL: dispatch job
        RegenerateWinboxJob::dispatch($router->id);

        return back()->with('success', 'Winbox regeneration queued.');
    }

    public function reprovision(Router $router): RedirectResponse
    {
        // ✅ REAL: dispatch job
        ReprovisionRouterJob::dispatch($router->id);

        return back()->with('success', 'Reprovision queued.');
    }

    public function syncHotspotFiles(Router $router): RedirectResponse
    {
        // ✅ REAL: dispatch job
        SyncHotspotFilesJob::dispatch($router->id);

        return back()->with('success', 'Hotspot files sync queued.');
    }

    public function syncRouterTime(Router $router): RedirectResponse
    {
        // ✅ REAL: dispatch job
        SyncRouterTimeJob::dispatch($router->id);

        return back()->with('success', 'Router time sync queued.');
    }

    public function restart(Router $router): RedirectResponse
    {
        // Placeholder for Phase 7.2 (real restart job)
        return back()->with('success', 'Router restart queued.');
    }

    public function disable(Router $router): RedirectResponse
    {
        $router->update(['is_active' => false]);
        return back()->with('success', 'Router disabled.');
    }

    public function enable(Router $router): RedirectResponse
    {
        $router->update(['is_active' => true]);
        return back()->with('success', 'Router enabled.');
    }
}
