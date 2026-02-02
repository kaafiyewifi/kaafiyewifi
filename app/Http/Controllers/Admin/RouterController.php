<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RouterStatus;
use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RouterController extends Controller
{
    // ✅ Online window (minutes)
    private int $onlineWindowMinutes = 3;

    public function index(Request $request)
    {
        $status  = $request->string('status')->toString(); // connected|offline|null (tabs)
        $q       = $request->string('q')->toString();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $onlineCutoff = now()->subMinutes($this->onlineWindowMinutes);

        $query = Router::query()
            ->with(array_values(array_filter([
                'latestMetric',
                // services optional
                method_exists(Router::class, 'services') ? 'services' : null,
            ])))
            ->latest();

        /**
         * ✅ Filter tabs:
         * - status=connected => show routers that are "fresh online"
         * - status=offline   => show routers that are "stale/offline"
         * - empty => all
         */
        if ($status !== '') {
            if ($status === RouterStatus::Connected->value || $status === 'connected') {
                $query->whereNotNull('last_seen_at')
                    ->where('last_seen_at', '>=', $onlineCutoff);
            } elseif ($status === RouterStatus::Offline->value || $status === 'offline') {
                $query->where(function ($qq) use ($onlineCutoff) {
                    $qq->whereNull('last_seen_at')
                        ->orWhere('last_seen_at', '<', $onlineCutoff);
                });
            } else {
                // fallback: if you add new statuses later
                $query->where('status', $status);
            }
        }

        // ✅ Search (safe columns only)
        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('identity', 'like', "%{$q}%")
                    ->orWhere('mgmt_host', 'like', "%{$q}%");
            });
        }

        $routers = $query->paginate($perPage)->appends($request->query());

        // ✅ Counters (All / Online / Offline) using last_seen_at freshness
        $total = Router::count();

        $onlineCount = Router::query()
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', $onlineCutoff)
            ->count();

        $offlineCount = Router::query()
            ->where(function ($qq) use ($onlineCutoff) {
                $qq->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $onlineCutoff);
            })
            ->count();

        return view('admin.routers.index', compact('routers', 'total', 'onlineCount', 'offlineCount'));
    }

    public function show(Router $router)
    {
        $relations = [
            'latestMetric',
        ];

        // optional relations (si aysan u jabin haddii aadan haysan)
        if (method_exists($router, 'services')) {
            $relations[] = 'services';
        }
        if (method_exists($router, 'credential')) {
            $relations[] = 'credential'; // ✅ singular
        }
        if (method_exists($router, 'events') && Schema::hasTable('router_events')) {
            $relations[] = 'events';
        }

        $router->loadMissing($relations);

        return view('admin.routers.show', compact('router'));
    }

    /**
     * ✅ Fix for: Call to undefined method RouterController::destroy()
     * If your routes use Route::resource('routers', RouterController::class),
     * this method is required.
     */
    public function destroy(Router $router)
    {
        $this->safeEvent($router, 'router.deleted', [
            'identity' => $router->identity,
            'name'     => $router->name,
        ]);

        $router->delete();

        return redirect()
            ->route('admin.routers.index')
            ->with('success', 'Router deleted successfully.');
    }

    // -------------------------
    // Wizard
    // -------------------------

    public function stage1()
    {
        return view('admin.routers.wizard.stage1');
    }

    public function storeStage1(Request $request)
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:120'],
        ]);

        $router = Router::create([
            'tenant_id' => null,
            'identity'  => $data['identity'],
            'name'      => $data['identity'],
            'mgmt_host' => null,
            'api_port'  => 8728,
            'use_tls'   => false,
            'status'    => RouterStatus::Pending->value,
            'notes'     => null,
        ]);

        $this->safeEvent($router, 'router.created', ['identity' => $router->identity]);

        return redirect()->route('admin.routers.wizard.stage2', $router);
    }

    public function stage2(Router $router)
    {
        return view('admin.routers.wizard.stage2', compact('router'));
    }

    public function issueToken(Router $router, ProvisionTokenService $svc)
    {
        $result = $svc->create($router, scriptVersion: 'v1', ttlMinutes: 20);
        $plainToken = $result['token'];

        // IMPORTANT: this route name must exist in your routes
        // Example:
        // Route::get('/provision/{token}', ...)->name('provision.script');
        $url = route('provision.script', ['token' => $plainToken], true);

        $cmd = <<<CMD
:do {
  :if ([/ping 8.8.8.8 count=3] = 0) do={ :error "No internet connection" }
  /tool fetch mode=https url="{$url}" dst-path=kaafiye.rsc;
  :delay 2s;
  /import kaafiye.rsc;
} on-error={ :put "Provisioning failed"; :put \$error; }
CMD;

        $this->safeEvent($router, 'provision.token_issued', [
            'ttl_minutes'    => 20,
            'script_version' => 'v1',
        ]);

        return response()->json([
            'router_id'  => $router->id,
            'identity'   => $router->identity,
            'expires_at' => optional($result['provision']->expires_at)->toDateTimeString(),
            'command'    => $cmd,
        ]);
    }

    private function safeEvent(Router $router, string $type, array $payload = []): void
    {
        if (!Schema::hasTable('router_events')) return;
        if (!method_exists($router, 'events')) return;

        try {
            $router->events()->create([
                'type'       => $type,
                'payload'    => $payload,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
