<?php

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;

class ProvisionReportController extends Controller
{
    public function store(Request $request, string $token)
    {
        // 1) Try resolve router by provisions table (if you use RouterProvision)
        $router = null;

        try {
            $router = Router::whereHas('provisions', function ($q) use ($token) {
                $q->where('token', $token);
            })->first();
        } catch (\Throwable $e) {
            // ignore if provisions relation/table not usable
        }

        // 2) Fallback: routers.provision_token (only if column exists)
        if (!$router) {
            try {
                $router = Router::where('provision_token', $token)->first();
            } catch (\Throwable $e) {
                // ignore if column doesn't exist
            }
        }

        if (!$router) {
            return response()->json(['ok' => false, 'message' => 'Invalid token'], 404);
        }

        $data = $request->validate([
            'step' => ['nullable','string','max:120'],
            'message' => ['required','string','max:500'],
            'status' => ['nullable','in:info,success,warning,error,done'],
            'meta' => ['nullable','array'],
        ]);

        $status = $data['status'] ?? 'info';

        // event type
        $type = $status === 'done' ? 'provisioning.done' : 'provisioning.step';

        // save event
        $router->events()->create([
            'type' => $type,
            'payload' => [
                'step' => $data['step'] ?? null,
                'message' => $data['message'],
                'status' => $status,
                'meta' => $data['meta'] ?? null,
            ],
            'created_at' => now(),
        ]);

        // quick status update
        if ($status === 'done') {
            $router->status = 'connected';
            $router->last_seen_at = now();
            $router->save();
        }

        return response()->json(['ok' => true]);
    }
}
