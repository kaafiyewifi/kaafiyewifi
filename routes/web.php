<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\HotspotController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\CustomerSubscriptionController;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED AREA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | ADMIN AREA
    |--------------------------------------------------------------------------
    | Mustaqbalka: ->middleware('role:admin')
    */
    Route::prefix('admin')
        ->name('admin.')
        //->middleware('role:admin')
        ->group(function () {

        /* =====================
         | CUSTOMERS
         |=====================*/
        Route::resource('customers', CustomerController::class);

        /* =====================
         | LOCATIONS
         |=====================*/
        Route::resource('locations', LocationController::class);

        /* =====================
         | ROUTERS
         |=====================*/
        Route::resource('routers', RouterController::class);

        /* =====================
         | HOTSPOTS
         |=====================*/
        Route::resource('hotspots', HotspotController::class);

        /* =====================
         | SUBSCRIPTION PLANS
         |=====================*/
        Route::resource('subscription-plans', SubscriptionPlanController::class);

        /* =====================
         | CUSTOMER SUBSCRIBE
         |=====================*/
        Route::post(
            'customers/{customer}/subscribe',
            [CustomerSubscriptionController::class, 'store']
        )->name('customers.subscribe');

        /* =====================
         | SUBSCRIPTION ACTIONS
         |=====================*/
        Route::prefix('subscriptions')->name('subs.')->group(function () {

            Route::post('{sub}/extend',
                [CustomerSubscriptionController::class, 'extend']
            )->name('extend');

            Route::post('{sub}/pause',
                [CustomerSubscriptionController::class, 'pause']
            )->name('pause');

            Route::post('{sub}/resume',
                [CustomerSubscriptionController::class, 'resume']
            )->name('resume');

            Route::post('{sub}/cancel',
                [CustomerSubscriptionController::class, 'cancel']
            )->name('cancel');

        });

    });

});
