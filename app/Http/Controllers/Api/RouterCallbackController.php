<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RouterCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string','max:200'],
            'router_name' => ['nullable','string','max:200'],
            'location_name' => ['nullable','string','max:200'],
            'hotspot_interface' => ['nullable','string','max:80'],
            'pppoe_interface' => ['nullable','string','max:80'],
            'api_port' => ['nullable','integer','min:1','max:65535'],
            'api_ssl_port' => ['nullable','integer','min:1','max:65535'],

            // Optional fields (if you later extend fetch payload)
            'identity' => ['nullable','string','max:200'],
            'routeros_version' => ['nullable','string','max:120'],
            'serial_number' => ['nullable','string','max:120'],
            'public_ip' => ['nullable','string','max:120'],
        ]);

        // Find matching router by checking hash (single-tenant, routers count not huge)
        $router = Router::all()->first(function ($r) use ($data) {
            return Hash::check($data['token'], $r->provision_token_hash);
        });

        if (!$router) {
            Log::warning('KAAFIYE: Router callback invalid token', ['ip' => $request->ip()]);
            return response()->json(['ok' => false, 'message' => 'Invalid token'], 401);
        }

        // Mark provisioned
        $router->update([
            'status' => 'provisioned',
            'provisioned_at' => now(),
            'identity' => $data['identity'] ?? $router->identity ?? $router->name,
            'routeros_version' => $data['routeros_version'] ?? $router->routeros_version,
            'serial_number' => $data['serial_number'] ?? $router->serial_number,
            'public_ip' => $data['public_ip'] ?? $router->public_ip,
            'last_error' => null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Provisioning confirmed']);
    }
}
