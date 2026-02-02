<?php

// app/Http/Middleware/AllowProvisionFromTrustedNetworks.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowProvisionFromTrustedNetworks
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = config('routers.provision_allow_cidrs', []);
        if (empty($allowed)) return $next($request);

        $ip = $request->ip();
        foreach ($allowed as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) return $next($request);
        }
        abort(403, 'Forbidden');
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - (int)$mask)) - 1)) === ip2long($subnet);
    }
}
