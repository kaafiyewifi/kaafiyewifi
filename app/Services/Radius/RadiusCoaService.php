<?php

// app/Services/Radius/RadiusCoaService.php
namespace App\Services\Radius;

use App\Models\Router;

class RadiusCoaService
{
    public function disconnectUser(Router $router, string $username, ?string $sessionId = null): bool
    {
        $server = config('radius.server', '127.0.0.1');
        $secret = config('routers.radius_secret'); // same as Mikrotik radius secret
        $port   = 3799;

        // Minimal attributes
        $attrs = "User-Name = \"$username\"\n";
        if ($sessionId) {
            $attrs .= "Acct-Session-Id = \"$sessionId\"\n";
        }

        $cmd = "printf " . escapeshellarg($attrs) .
            " | radclient -x " . escapeshellarg("$server:$port") .
            " disconnect " . escapeshellarg($secret) . " 2>/dev/null";

        $out = shell_exec($cmd);
        return $out !== null; // Phase 6: parse output properly
    }
}
