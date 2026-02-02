<?php

// app/Http/Controllers/Routers/RotateRouterApiPasswordController.php
namespace App\Http\Controllers\Routers;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Routers\Contracts\RouterApi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RotateRouterApiPasswordController extends Controller
{
    public function __invoke(Request $request, Router $router, RouterApi $api)
    {
        // 1) ensure router has credentials record
        $cred = $router->credentials;
        if (!$cred || !$cred->username) {
            return back()->with('error', 'Router credentials missing.');
        }

        // 2) Generate NEW strong password
        $newPass = Str::password(20); // strong random

        // 3) Push to router via API (change password)
        // RouterOS: /user/set .id=... password=...
        $api->connect($router);

        // find user
        $users = $api->query('/user/print', ['?name' => $cred->username]);
        if (count($users) === 0) {
            $api->disconnect();
            return back()->with('error', 'API user not found on router.');
        }

        $id = $users[0]['.id'] ?? null;
        if (!$id) {
            $api->disconnect();
            return back()->with('error', 'Unable to identify router user id.');
        }

        // set new password on router
        $api->command('/user/set', [
            '.id' => $id,
            'password' => $newPass,
        ]);

        $api->disconnect();

        // 4) Update DB (encrypted cast will encrypt)
        $cred->password_encrypted = $newPass;
        $cred->save();

        // 5) Log event WITHOUT secrets
        $router->events()->create([
            'type' => 'credential.rotated',
            'payload' => [
                'username' => $cred->username,
                'by_user_id' => $request->user()->id,
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Router API password rotated successfully.');
    }
}
