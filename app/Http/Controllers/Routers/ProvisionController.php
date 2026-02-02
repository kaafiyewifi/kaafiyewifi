<?php

// app/Http/Controllers/Routers/ProvisionController.php
namespace App\Http\Controllers\Routers;

use App\Enums\ProvisionStatus;
use App\Enums\RouterStatus;
use App\Http\Controllers\Controller;
use App\Services\Routers\ProvisionTokenService;
use App\Services\Routers\RscBuilderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProvisionController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        ProvisionTokenService $tokens,
        RscBuilderService $rsc
    ) {
        $provision = $tokens->findValidByToken($token);

        if (!$provision) {
            return response("Invalid/expired token.\n", 404, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $router = $provision->router;

        // Mark lifecycle
        $router->status = RouterStatus::Provisioning;
        $router->save();

        // One-time use
        $tokens->markUsed($provision);

        // Build script
        $script = $rsc->build($router, $provision);

        // Mark success on server-side provisioning record (script delivered)
        $provision->status = ProvisionStatus::Success;
        $provision->finished_at = now();
        $provision->save();

        // Update router last_seen (we assume fetch means router is online)
        $router->last_seen_at = now();
        $router->status = RouterStatus::Connected; // later: confirm via API polling
        $router->save();

        // Optional: log event
        $router->events()->create([
            'type' => 'provision.script_served',
            'payload' => [
                'script_version' => $provision->script_version,
                'ip' => $request->ip(),
                'ua' => (string) $request->userAgent(),
            ],
            'created_at' => now(),
        ]);

        return response($script, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kaafiye.rsc"',
        ]);
    }
}
