<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\Provision\CallbackController;
use App\Http\Controllers\Api\RouterWireGuardController;
use App\Http\Controllers\Provision\ProvisionReportController;
use App\Http\Controllers\Api\RouterHealthController;
use App\Http\Controllers\Api\RouterHeartbeatController;

/*
|--------------------------------------------------------------------------
| Rate Limiter for Router API
|--------------------------------------------------------------------------
*/
RateLimiter::for('router-api', function (Request $request) {
    return [
        Limit::perMinute(120)->by($request->ip()),
        Limit::perMinute(60)->by('router-api:global'),
    ];
});

/*
|--------------------------------------------------------------------------
| Router Public API (NO AUTH – MikroTik)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:router-api')->group(function () {

    // Health check
    Route::get('/health', RouterHealthController::class);

    // Provision token callback (POST + GET for RouterOS v7 safe)
    Route::post('/provision/callback/{token}', [CallbackController::class, 'handle'])
        ->where('token', '[A-Za-z0-9\-_]+');

    Route::get('/provision/callback/{token}', [CallbackController::class, 'handle'])
        ->where('token', '[A-Za-z0-9\-_]+');

    // Legacy callbacks (keep)
    Route::post('/routers/callback', [CallbackController::class, 'handle']);
    Route::post('/router/callback', [CallbackController::class, 'handle']);

    // ❤️ Heartbeat (POST + GET for RouterOS v7 safe)
    Route::post('/routers/heartbeat', [RouterHeartbeatController::class, 'store']);
    Route::get('/routers/heartbeat', [RouterHeartbeatController::class, 'store']); // ✅ added

    // WireGuard
    Route::post('/router/wg-register', [RouterWireGuardController::class, 'register']);

    // Provision progress report
    Route::post('/provision/report/{token}', [ProvisionReportController::class, 'store'])
        ->where('token', '[A-Za-z0-9\-_]+');
});
