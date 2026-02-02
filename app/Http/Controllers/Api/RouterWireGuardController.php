<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RouterWireGuardController extends Controller
{
    /**
     * POST /api/router/wg-register
     *
     * Accepts:
     * - identity (required)
     * - wg_name (optional)
     * - wg_listen_port (optional)
     * - wg_public_key (required)
     * - wg_router_ip (optional)
     * - wg_vps_ip (optional)
     */
    public function register(Request $request)
    {
        $identity = trim((string) $request->input('identity', ''));
        $wgPublicKey = trim((string) $request->input('wg_public_key', ''));

        if ($identity === '' || $wgPublicKey === '') {
            return response()->json([
                'ok' => false,
                'error' => 'identity and wg_public_key are required',
            ], 422);
        }

        $router = Router::where('identity', $identity)->first();

        if (!$router) {
            return response()->json([
                'ok' => false,
                'error' => 'router not found',
            ], 404);
        }

        // Save WireGuard info (only set if provided)
        $router->wg_public_key = $wgPublicKey;

        if ($request->filled('wg_name')) {
            $router->wg_name = trim((string) $request->input('wg_name'));
        }

        if ($request->filled('wg_listen_port')) {
            $router->wg_listen_port = (int) $request->input('wg_listen_port');
        }

        if ($request->filled('wg_router_ip')) {
            $router->wg_router_ip = trim((string) $request->input('wg_router_ip'));
        }

        if ($request->filled('wg_vps_ip')) {
            $router->wg_vps_ip = trim((string) $request->input('wg_vps_ip'));
        }

        $router->wg_last_seen_at = Carbon::now();
        $router->save();

        return response()->json([
            'ok' => true,
            'identity' => $router->identity,
            'router_id' => $router->id,
            'wg_public_key' => $router->wg_public_key,
        ]);
    }
}
