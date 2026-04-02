<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterMetric;
use App\Models\RouterLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class RouterHeartbeatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // JSON-only validation (prevents 302 redirects)
        $v = Validator::make($request->all(), [
            'identity'            => ['required', 'string', 'max:120'],

            // RouterOS fields (support both naming styles)
            'cpu_load'            => ['nullable', 'integer', 'min:0', 'max:100'],

            'free_memory'         => ['nullable', 'integer'],
            'total_memory'        => ['nullable', 'integer'],

            'free_hdd_space'      => ['nullable', 'integer'],
            'total_hdd_space'     => ['nullable', 'integer'],

            // allow alt names used by some scripts
            'free_mem'            => ['nullable', 'integer'],
            'total_mem'           => ['nullable', 'integer'],
            'free_disk'           => ['nullable', 'integer'],

            'uptime'              => ['nullable', 'string', 'max:50'],
            'version'             => ['nullable', 'string', 'max:50'],
            'board_name'          => ['nullable', 'string', 'max:80'],
            'architecture_name'   => ['nullable', 'string', 'max:80'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'error' => 'validation_failed',
                'messages' => $v->errors(),
            ], 422);
        }

        $data = $v->validated();
        $identity = trim((string) $data['identity']);

        // Map alternative names → your DB column names
        if (!isset($data['free_memory']) && isset($data['free_mem'])) {
            $data['free_memory'] = $data['free_mem'];
        }
        if (!isset($data['total_memory']) && isset($data['total_mem'])) {
            $data['total_memory'] = $data['total_mem'];
        }
        if (!isset($data['free_hdd_space']) && isset($data['free_disk'])) {
            $data['free_hdd_space'] = $data['free_disk'];
        }

        $routerTable = (new Router())->getTable();

        // Find router safely even if identity stored in other column
        $routerQ = Router::withoutGlobalScopes();
        $routerQ->where(function ($q) use ($identity, $routerTable) {
            foreach (['identity', 'name', 'router_identity'] as $col) {
                if (Schema::hasColumn($routerTable, $col)) {
                    $q->orWhere($col, $identity);
                }
            }
        });

        $router = $routerQ->first();

        if (!$router) {
            return response()->json([
                'ok' => false,
                'error' => 'router_not_found',
                'got' => ['identity' => $identity],
            ], 404);
        }

        // Update router heartbeat summary on routers table
        $routerUpdates = [];

        if (Schema::hasColumn($routerTable, 'status')) {
            $routerUpdates['status'] = 'online';
        }

        if (Schema::hasColumn($routerTable, 'last_seen_at')) {
            $routerUpdates['last_seen_at'] = now();
        }

        if (isset($data['cpu_load']) && Schema::hasColumn($routerTable, 'cpu_load')) {
            $routerUpdates['cpu_load'] = (int) $data['cpu_load'];
        }

        if (isset($data['free_memory']) && Schema::hasColumn($routerTable, 'free_memory')) {
            $routerUpdates['free_memory'] = (int) $data['free_memory'];
        }

        if (isset($data['total_memory']) && Schema::hasColumn($routerTable, 'total_memory')) {
            $routerUpdates['total_memory'] = (int) $data['total_memory'];
        }

        if (isset($data['free_hdd_space']) && Schema::hasColumn($routerTable, 'free_hdd_space')) {
            $routerUpdates['free_hdd_space'] = (int) $data['free_hdd_space'];
        }

        if (isset($data['total_hdd_space']) && Schema::hasColumn($routerTable, 'total_hdd_space')) {
            $routerUpdates['total_hdd_space'] = (int) $data['total_hdd_space'];
        }

        if (!empty($data['version']) && Schema::hasColumn($routerTable, 'routeros_version')) {
            $routerUpdates['routeros_version'] = $data['version'];
        }

        if (!empty($data['board_name']) && Schema::hasColumn($routerTable, 'board_name')) {
            $routerUpdates['board_name'] = $data['board_name'];
        }

        if (!empty($data['architecture_name']) && Schema::hasColumn($routerTable, 'architecture_name')) {
            $routerUpdates['architecture_name'] = $data['architecture_name'];
        }

        if (Schema::hasColumn($routerTable, 'last_heartbeat_payload')) {
            $routerUpdates['last_heartbeat_payload'] = [
                'identity'            => $identity,
                'cpu_load'            => $data['cpu_load'] ?? null,
                'free_memory'         => $data['free_memory'] ?? null,
                'total_memory'        => $data['total_memory'] ?? null,
                'free_hdd_space'      => $data['free_hdd_space'] ?? null,
                'total_hdd_space'     => $data['total_hdd_space'] ?? null,
                'uptime'              => $data['uptime'] ?? null,
                'version'             => $data['version'] ?? null,
                'board_name'          => $data['board_name'] ?? null,
                'architecture_name'   => $data['architecture_name'] ?? null,
                'received_at'         => now()->toDateTimeString(),
                'source_ip'           => $request->ip(),
                'method'              => $request->method(),
            ];
        }

        if (!empty($routerUpdates)) {
            $router->forceFill($routerUpdates)->save();
        }

        // Store metrics snapshot (only fill columns that exist)
        $metric = new RouterMetric();
        $metricTable = $metric->getTable();

        $metricFill = [
            'router_id'         => $router->id,
            'cpu_load'          => $data['cpu_load'] ?? null,
            'free_memory'       => $data['free_memory'] ?? null,
            'total_memory'      => $data['total_memory'] ?? null,
            'free_hdd_space'    => $data['free_hdd_space'] ?? null,
            'total_hdd_space'   => $data['total_hdd_space'] ?? null,
            'uptime'            => $data['uptime'] ?? null,
            'version'           => $data['version'] ?? null,
            'board_name'        => $data['board_name'] ?? null,
            'architecture_name' => $data['architecture_name'] ?? null,
            'collected_at'      => now(),
        ];

        // Filter fill to existing columns only (prevents SQL errors)
        $metricFillSafe = [];
        foreach ($metricFill as $col => $val) {
            if ($col === 'router_id' || Schema::hasColumn($metricTable, $col)) {
                $metricFillSafe[$col] = $val;
            }
        }

        try {
            RouterMetric::create($metricFillSafe);
        } catch (\Throwable $e) {
            // metrics failure should not break heartbeat
        }

        // Log heartbeat (ignore if RouterLog schema differs)
        try {
            RouterLog::create([
                'router_id' => $router->id,
                'type'      => 'heartbeat',
                'success'   => true,
                'message'   => 'Heartbeat metrics collected',
                'meta'      => [
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'identity' => $identity,
                ],
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json([
            'ok' => true,
            'router_id' => $router->id,
            'status' => 'online',
        ], 200);
    }
}