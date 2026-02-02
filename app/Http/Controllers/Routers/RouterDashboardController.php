<?php

namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;

class RouterDashboardController extends Controller
{
    public function index()
    {
        return view('routers.index');
    }

    public function show(Router $router)
    {
        return view('routers.show', compact('router'));
    }
}
