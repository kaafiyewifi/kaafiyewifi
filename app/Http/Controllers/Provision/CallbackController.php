<?php

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CallbackController extends Controller
{
    public function __construct(
        private readonly ProvisionTokenService $tokens
    ) {}

    public function handle(Request $request, string $token)
    {
        $data = $request->validate([
            'identity' => ['required', 'string', 'max:120'],
            'api_port' => ['nullable'],
            'mgmt_ip'  => ['nullable', 'string', 'max:64'],
        ]);

        $identity = trim((string) $data['identity']);
        $apiPort  = (isset($data['api_port']) && is_numeric($data['api_port'])) ? (int) $data['api_port'] : null;

        [$row, $router] = $this->tokens->findValidTokenAndRouter($token);

        if (!$row || !$router) {
            return response()->json([
                'ok' => false,
                'error' => 'invalid/expired token OR router not linked',
                'got' => ['token' => $token, 'identity' => $identity],
            ], 404);
        }

        $table = (new Router())->getTable();
        $updates = [];

        // identity fields
        foreach (['identity', 'router_identity'] as $col) {
            if (Schema::hasColumn($table, $col)) {
                $updates[$col] = $identity;
            }
        }

        if (Schema::hasColumn($table, 'name') && empty($router->name)) {
            $updates['name'] = $identity;
        }

        if (Schema::hasColumn($table, 'api_port') && $apiPort) {
            $updates['api_port'] = $apiPort;
        }

        // mgmt fields
        if (Schema::hasColumn($table, 'mgmt_ip')) {
            $updates['mgmt_ip'] = $data['mgmt_ip'] ?? null;
        }
        if (Schema::hasColumn($table, 'mgmt_host')) {
            $updates['mgmt_host'] = $data['mgmt_ip'] ?? $request->ip();
        }

        // status
        if (Schema::hasColumn($table, 'status')) {
            $updates['status'] = 'connected';
        }
        if (Schema::hasColumn($table, 'last_seen_at')) {
            $updates['last_seen_at'] = Carbon::now();
        }
        if (Schema::hasColumn($table, 'provisioned_at')) {
            $updates['provisioned_at'] = Carbon::now();
        }

        $router->forceFill($updates)->save();

        // ✅ consume token (so it can't be reused)
        $this->tokens->markUsed($row);

        return response()->json([
            'ok' => true,
            'router_id' => $router->id,
            'updated' => array_keys($updates),
        ], 200);
    }
}
