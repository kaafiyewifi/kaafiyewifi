<?php

namespace App\Http\Controllers\Provision;

use App\Http\Controllers\Controller;
use App\Models\RouterLog;
use Illuminate\Http\Request;

class RouterLogController extends Controller
{
    public function index(Request $request, int $routerId)
    {
        $afterId = (int) $request->query('after', 0);

        return RouterLog::where('router_id', $routerId)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get([
                'id',
                'level',
                'message',
                'created_at'
            ]);
    }
}
