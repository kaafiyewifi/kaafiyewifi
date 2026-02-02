<?php

// app/Http/Controllers/Routers/RouterIndexController.php
namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\Request;

class RouterIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = Router::query()
            ->withCount(['services as enabled_services_count' => fn($x) => $x->where('is_enabled', true)])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('id');

        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }

        if ($search = $request->get('q')) {
            $q->where(function($w) use ($search) {
                $w->where('name','like',"%$search%")
                  ->orWhere('identity','like',"%$search%")
                  ->orWhere('mgmt_host','like',"%$search%");
            });
        }

        $routers = $q->paginate(15)->withQueryString();

        return view('routers.index', compact('routers'));
    }
}
