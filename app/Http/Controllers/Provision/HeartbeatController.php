<?php

namespace App\Http\Controllers\Provision;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HeartbeatController
{
    /**
     * Router heartbeat endpoint
     * Used to confirm router is alive and connected
     * SAFE: no auth, lightweight
     */
    public function handle(Request $request)
    {
        return response()->json([
            'status' => 'alive',
            'server_time' => now()->toDateTimeString(),
        ], Response::HTTP_OK);
    }
}
