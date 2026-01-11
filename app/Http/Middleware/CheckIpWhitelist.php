<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IpWhitelist;

class CheckIpWhitelist
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if (!$user->roles->count()) {
            abort(403, 'No role assigned');
        }

        $role = $user->roles->first()->name;

        $allowed = IpWhitelist::where('role_name', $role)
            ->where('ip_address', $request->ip())
            ->exists();

        if (!$allowed) {
            abort(403, 'IP not allowed for this role');
        }

        return $next($request);
    }
}
