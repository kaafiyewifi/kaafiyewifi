<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Provisioning\ProvisionTokenService;
use Illuminate\Http\Request;

class RouterProvisionController extends Controller
{
    public function generate(Request $request, Router $router, ProvisionTokenService $tokens)
    {
        // Optional: allow admin to set expiry minutes
        $minutes = (int) ($request->input('expires_minutes', 10));
        $minutes = max(2, min($minutes, 60)); // 2–60 minutes

        [$rawToken, $tokenRow] = $tokens->createForRouter($router, $minutes);

        // Provisioning command admin will paste in MikroTik Terminal
        $url = route('provision.script', ['token' => $rawToken]);

        $command = <<<ROS
:do {
  :if ([/ping 8.8.8.8 count=3] = 0) do={ :error "No internet connection" }

  /tool fetch mode=https url="$url" dst-path=bootstrap.rsc;
  :delay 2s;
  /import bootstrap.rsc;

} on-error={ :put "Provisioning failed"; :put \$error; }
ROS;

        return response()->json([
            'router_id' => $router->id,
            'expires_at' => $tokenRow->expires_at->toIso8601String(),
            'command' => $command,
        ]);
    }
}
