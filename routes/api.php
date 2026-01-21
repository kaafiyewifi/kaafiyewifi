<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RouterRegisterController;

/*
|--------------------------------------------------------------------------
| Router API (Token in URL path)
|--------------------------------------------------------------------------
| Example:
| POST /api/router/register/<token>
| POST /api/router/heartbeat/<token>
*/
Route::post('/router/register/{token}', [RouterRegisterController::class, 'register']);
Route::post('/router/heartbeat/{token}', [RouterRegisterController::class, 'heartbeat']);
