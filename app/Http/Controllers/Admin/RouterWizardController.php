<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RouterWizardController extends Controller
{
    public function __construct(
        private readonly ProvisionTokenService $tokens
    ) {}

    /**
     * Step 1: Form page
     */
    public function stage1()
    {
        return view('admin.routers.wizard.stage1');
    }

    /**
     * Step 1: Save router (pending) then go to Step 2
     */
    public function storeStage1(Request $request)
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:120'],
        ]);

        $identity = trim($data['identity']);
        $pending  = $this->pendingStatusValue();

        $router = Router::create($this->buildRouterPayload($identity, $pending));

        $this->tryLogRouterCreatedEvent($router);

        return redirect()->route('admin.routers.wizard.stage2', $router);
    }

    /**
     * Step 2: Show provisioning command UI
     */
    public function stage2(Request $request, Router $router)
    {
        $router->refresh();

        // If already connected, jump to step 3
        if ($this->isConnected($router)) {
            return redirect()->route('admin.routers.service-setup', $router);
        }

        $identity = $router->identity
            ?? ($router->name ?? null)
            ?? ('Router-' . $router->id);

        // Create one-time token
        $data  = $this->tokens->create($router, 'v1', (int) config('app.provision_token_minutes', 20));
        $token = $data['token'] ?? null;

        if (!$token) {
            return back()->with('error', 'Unable to generate provisioning token.');
        }

        // Force provisioning to app URL (recommended)
        $base = rtrim((string) config('app.url'), '/'); // https://app.kaafiye.online
        $path = route('provision.script', ['token' => $token], false);
        $provisionUrl = $base . $path;

        // MikroTik command (RouterOS v7)
        $command = <<<MIKROTIK
/tool fetch mode=https check-certificate=no output=file \\
url="$provisionUrl" \\
dst-path=kaafiye.rsc; :delay 2s; /import kaafiye.rsc;
MIKROTIK;

        return view('admin.routers.wizard.provision', [
            'router'      => $router,
            'identity'    => $identity,
            'token'       => $token,
            'command'     => trim($command),
            'statusUrl'   => route('admin.routers.wizard.status', $router),
            'continueUrl' => route('admin.routers.service-setup', $router),
        ]);
    }

    /**
     * Step 2: AJAX polling endpoint (ADMIN AUTH REQUIRED)
     * GET /admin/routers/{router}/wizard/status
     */
    public function status(Request $request, Router $router)
    {
        $router->refresh();

        $status = $router->status ?? ($router->provisioning_status ?? 'pending');
        $connected = $this->isConnected($router);

        if ($request->expectsJson()) {
            return response()->json([
                'connected'    => $connected,
                'status'       => $status,
                'last_seen_at' => $this->asDateTimeString($router->last_seen_at),
                'message'      => $connected ? 'Device Connected Successfully!' : 'Waiting for command execution...',
                'hint'         => $connected
                    ? 'Device is online and ready. You can continue to Service Setup.'
                    : 'Run the provisioning command on MikroTik and wait...',
            ]);
        }

        return response(
            $connected ? 'connected' : 'pending',
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    /**
     * Decide if router is connected based on status OR recent last_seen_at
     */
    private function isConnected(Router $router): bool
    {
        $status = $router->status ?? ($router->provisioning_status ?? null);

        $connectedValues = ['connected', 'online', 'active', 'provisioned', 'completed'];

        $byStatus = $status
            ? in_array(strtolower((string) $status), $connectedValues, true)
            : false;

        // last_seen_at might be Carbon, string, or null
        $byLastSeen = false;
        $lastSeen = $this->toCarbon($router->last_seen_at);

        if ($lastSeen) {
            $byLastSeen = $lastSeen->gt(now()->subMinutes(3));
        }

        return $byStatus || $byLastSeen;
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if (!$value) return null;
        if ($value instanceof Carbon) return $value;

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private function asDateTimeString(mixed $value): ?string
    {
        $c = $this->toCarbon($value);
        return $c ? $c->toDateTimeString() : null;
    }

    private function pendingStatusValue(): string
    {
        if (enum_exists(\App\Enums\RouterStatus::class)) {
            $enum = \App\Enums\RouterStatus::class;

            foreach (['Pending', 'PENDING'] as $case) {
                if (defined("$enum::$case")) {
                    return constant("$enum::$case")->value;
                }
            }
        }

        return 'pending';
    }

    private function buildRouterPayload(string $identity, string $pending): array
    {
        $table = (new Router())->getTable();
        $payload = [];

        if (Schema::hasColumn($table, 'identity')) $payload['identity'] = $identity;
        if (Schema::hasColumn($table, 'name'))     $payload['name'] = $identity;

        if (Schema::hasColumn($table, 'status')) $payload['status'] = $pending;
        if (Schema::hasColumn($table, 'provisioning_status')) $payload['provisioning_status'] = $pending;

        if (Schema::hasColumn($table, 'api_port')) $payload['api_port'] = 8728;
        if (Schema::hasColumn($table, 'use_tls'))  $payload['use_tls'] = false;
        if (Schema::hasColumn($table, 'is_active')) $payload['is_active'] = true;

        if (Schema::hasColumn($table, 'uuid')) $payload['uuid'] = (string) Str::uuid();
        if (Schema::hasColumn($table, 'provision_token')) $payload['provision_token'] = Str::random(64);

        return $payload;
    }

    private function tryLogRouterCreatedEvent(Router $router): void
    {
        try {
            if (!Schema::hasTable('router_events')) return;
            if (!method_exists($router, 'events')) return;

            $router->events()->create([
                'type' => 'router.created',
                'payload' => [
                    'router_id' => $router->id,
                    'identity'  => $router->identity ?? ($router->name ?? null),
                ],
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }
}
