<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class RouterHealthController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'status' => 'ok',
            'time'   => now()->toIso8601String(),
        ]);
    }
}
