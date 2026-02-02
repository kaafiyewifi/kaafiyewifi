<?php

// app/Http/Controllers/Routers/RouterProvisionCommandController.php
namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;

class RouterProvisionCommandController extends Controller
{
    public function __invoke(Router $router, ProvisionTokenService $tokens)
    {
        $data = $tokens->create($router, 'v1', 20);
        $token = $data['token'];

        $url = route('routers.provision', ['token' => $token]);
        $command = '/tool fetch mode=https url="'.$url.'" dst-path=kaafiye.rsc;:delay 2s;/import kaafiye.rsc;';

        return view('routers.provision', compact('router', 'command'));
    }
}
