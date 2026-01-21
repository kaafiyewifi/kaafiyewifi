<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class RouterRegisterController extends Controller
{
    public function register(Request $request, string $token)
    {
        $this->authorizeToken($token);

        $data = $request->validate([
            'router_id'   => 'required|string|max:64',
            'router_name' => 'nullable|string|max:128',
            'public_key'  => 'required|string|max:128',
            'vpn_ip'      => 'required|string|max:32', // "172.16.5.2" or "172.16.5.2/12"
        ]);

        $vpnIpOnly = explode('/', $data['vpn_ip'])[0];

        $router = Router::updateOrCreate(
            ['router_id' => $data['router_id']],
            [
                'router_name'  => $data['router_name'] ?? null,
                'public_key'   => $data['public_key'],
                'vpn_ip'       => $vpnIpOnly,
                'last_seen_at' => now(),
            ]
        );

        $wgBin = config('kaafiye.wg_bin', '/usr/bin/wg');
        $wgIf  = config('kaafiye.wg_interface', 'wg0');

        $result = Process::run([
            'sudo', $wgBin, 'set', $wgIf,
            'peer', $data['public_key'],
            'allowed-ips', $vpnIpOnly . '/32',
        ]);

        if (!$result->successful()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to add WireGuard peer',
                'details' => $result->errorOutput(),
            ], 500);
        }

        return response()->json([
            'status'    => 'ok',
            'router_id' => $router->router_id,
            'vpn_ip'    => $router->vpn_ip,
        ]);
    }

    public function heartbeat(Request $request, string $token)
    {
        $this->authorizeToken($token);

        $data = $request->validate([
            'router_id'    => 'required|string|max:64',
            'active_users' => 'nullable|integer|min:0',
        ]);

        $router = Router::where('router_id', $data['router_id'])->first();

        if (!$router) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $router->update([
            'active_users' => $data['active_users'] ?? $router->active_users,
            'last_seen_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function authorizeToken(string $token): void
    {
        $expected = config('kaafiye.router_token');
        abort_unless($expected && hash_equals($expected, $token), 403);
    }
}
