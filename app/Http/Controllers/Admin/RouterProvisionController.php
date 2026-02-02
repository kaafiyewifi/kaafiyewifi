<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\ProvisionTokenService;

class RouterProvisionController extends Controller
{
    /**
     * Step 2 UI – Show provisioning command
     */
    public function command(Router $router, ProvisionTokenService $tokens)
    {
        $data = $tokens->create($router, 'v1', 20);

        $token = $data['token'];
        $url = route('provision.script', ['token' => $token]);

        $command = '/tool fetch mode=https url="' . $url . '" dst-path=kaafiye.rsc;:delay 2s;/import kaafiye.rsc;';

        return view('admin.routers.provision', compact('router', 'command'));
    }

    /**
     * Optional: regenerate token (POST)
     */
    public function generate(Router $router, ProvisionTokenService $tokens)
    {
        $tokens->create($router, 'v1', 20);

        return redirect()
            ->route('admin.routers.provision-command', $router)
            ->with('success', 'Provision token regenerated successfully');
    }
}
