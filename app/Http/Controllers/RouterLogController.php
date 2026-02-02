<?php

namespace App\Http\Controllers;

use App\Models\RouterLog;
use Illuminate\Http\Request;

class RouterLogController extends Controller
{
    public function index(int $routerId)
    {
        return RouterLog::where('router_id', $routerId)
            ->orderBy('id')
            ->get(['level', 'message', 'created_at']);
    }
}
