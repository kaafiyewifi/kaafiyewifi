<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RouterStatus;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RouterController extends Controller
{
    private int $onlineWindowMinutes = 3;

    private function isSuperAdmin(): bool
    {
        return auth()->check()
            && method_exists(auth()->user(), 'hasRole')
            && auth()->user()->hasRole('super_admin');
    }

    private function assignedLocationIds(): Collection
    {
        $user = auth()->user();

        if (!$user || !method_exists($user, 'locations')) {
            return collect();
        }

        return $user->locations->pluck('id');
    }

    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        $onlineCutoff = now()->subMinutes($this->onlineWindowMinutes);

        $query = Router::query()
            ->with(array_values(array_filter([
                'latestMetric',
                method_exists(Router::class, 'services') ? 'services' : null,
            ])))
            ->latest();

        if (!$this->isSuperAdmin()) {
            $query->whereIn('location_id', $this->assignedLocationIds());
        }

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
                $query->where('status', $status);
            }
        }

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('identity', 'like', "%{$q}%")
                    ->orWhere('mgmt_host', 'like', "%{$q}%");
            });
        }

        $routers = $query->paginate($perPage)->appends($request->query());

        $baseQuery = Router::query();

        if (!$this->isSuperAdmin()) {
            $baseQuery->whereIn('location_id', $this->assignedLocationIds());
        }

        $total = (clone $baseQuery)->count();

        $onlineCount = (clone $baseQuery)
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', $onlineCutoff)
            ->count();

        $offlineCount = (clone $baseQuery)
            ->where(function ($qq) use ($onlineCutoff) {
                $qq->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', $onlineCutoff);
            })
            ->count();

        return view('admin.routers.index', compact('routers', 'total', 'onlineCount', 'offlineCount'));
    }

    public function show(Router $router)
    {
        if (!$this->isSuperAdmin()) {
            abort_unless(
                $this->assignedLocationIds()->contains($router->location_id),
                403
            );
        }

        $relations = ['latestMetric'];

        if (method_exists($router, 'services')) {
            $relations[] = 'services';
        }

        if (method_exists($router, 'credential')) {
            $relations[] = 'credential';
        }

        if (method_exists($router, 'events') && Schema::hasTable('router_events')) {
            $relations[] = 'events';
        }

        $router->loadMissing($relations);

        $webfigUrl = null;

        if (!empty($router->mgmt_host)) {
            $webfigUrl = "http://{$router->mgmt_host}";
        }

        return view('admin.routers.show', compact('router', 'webfigUrl'));
    }

    public function destroy(Router $router)
    {
        if (!$this->isSuperAdmin()) {
            abort_unless(
                $this->assignedLocationIds()->contains($router->location_id),
                403
            );
        }

        $this->safeEvent($router, 'router.deleted', [
            'identity' => $router->identity,
            'name' => $router->name,
        ]);

        $router->delete();

        return redirect()
            ->route('admin.routers.index')
            ->with('success', 'Router deleted successfully.');
    }

    public function stage1()
    {
        $locations = $this->isSuperAdmin()
            ? Location::query()->orderBy('name')->get(['id', 'name'])
            : auth()->user()->locations()->orderBy('name')->get(['id', 'name']);

        return view('admin.routers.wizard.stage1', compact('locations'));
    }

    public function storeStage1(Request $request)
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:120'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
        ]);

        $locationId = null;

        if ($this->isSuperAdmin()) {
            $locationId = $data['location_id'] ?? null;
        } else {
            $locationId = auth()->user()->locations()->value('locations.id');

            if (!$locationId) {
                return back()
                    ->withErrors(['location_id' => 'Admin-kan location looma xirin.'])
                    ->withInput();
            }
        }

        $router = Router::create([
            'tenant_id' => null,
            'location_id' => $locationId,
            'identity' => $data['identity'],
            'name' => $data['identity'],
            'mgmt_host' => null,
            'wg_ip' => null,
            'radius_secret' => bin2hex(random_bytes(16)),
            'api_port' => 8728,
            'use_tls' => false,
            'status' => RouterStatus::Pending->value,
            'notes' => null,
        ]);

        $this->safeEvent($router, 'router.created', ['identity' => $router->identity]);

        return redirect()->route('admin.routers.wizard.stage2', $router);
    }

    public function stage2(Router $router)
    {
        if (!$this->isSuperAdmin()) {
            abort_unless(
                $this->assignedLocationIds()->contains($router->location_id),
                403
            );
        }

        return view('admin.routers.wizard.stage2', compact('router'));
    }

    public function issueToken(Router $router, ProvisionTokenService $svc)
    {
        if (!$this->isSuperAdmin()) {
            abort_unless(
                $this->assignedLocationIds()->contains($router->location_id),
                403
            );
        }

        if (empty($router->radius_secret)) {
            $router->radius_secret = bin2hex(random_bytes(16));
            $router->save();
        }

        $result = $svc->create($router, scriptVersion: 'v1', ttlMinutes: 20);
        $plainToken = $result['token'];

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
            'ttl_minutes' => 20,
            'script_version' => 'v1',
        ]);

        return response()->json([
            'router_id' => $router->id,
            'identity' => $router->identity,
            'expires_at' => optional($result['provision']->expires_at)->toDateTimeString(),
            'command' => $cmd,
        ]);
    }

    private function safeEvent(Router $router, string $type, array $payload = []): void
    {
        if (!Schema::hasTable('router_events')) {
            return;
        }

        if (!method_exists($router, 'events')) {
            return;
        }

        try {
            $router->events()->create([
                'type' => $type,
                'payload' => $payload,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}