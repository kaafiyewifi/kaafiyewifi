<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\Admin\{
    CustomerController,
    LocationController,
    RouterController,
    HotspotController,
    SubscriptionPlanController,
    CustomerSubscriptionController,
    HotspotWizardController,
    RouterStatusController
};

/*
|--------------------------------------------------------------------------
| Redirect Root
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('dashboard'));

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class,'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class,'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class,'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class,'index'])
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        /* ================= CORE ================= */
        Route::resource('customers', CustomerController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('routers', RouterController::class);

        /* ================= HOTSPOTS ================= */
        Route::resource('hotspots', HotspotController::class)
            ->only(['index','show','destroy']);

        /* ================= SUBSCRIPTIONS ================= */
        Route::resource('subscription-plans', SubscriptionPlanController::class);

        /* ================= ROUTER USERS ================= */
        Route::get(
            'router/online-users',
            [RouterController::class,'onlineUsers']
        )->name('router.online');

        /* ================= PLAN TOGGLE ================= */
        Route::post(
            'subscription-plans/{subscriptionPlan}/toggle',
            [SubscriptionPlanController::class,'toggleStatus']
        )->name('subscription-plans.toggle');

        /* ================= CUSTOMER SUB ================= */
        Route::get(
            'customers/{customer}/subscribe',
            [CustomerSubscriptionController::class,'create']
        )->name('customers.subscribe');

        Route::post(
            'customers/{customer}/subscribe',
            [CustomerSubscriptionController::class,'store']
        )->name('customers.subscribe.store');

        Route::prefix('subscriptions')->name('subs.')->group(function () {
            Route::post('{sub}/extend', [CustomerSubscriptionController::class,'extend'])->name('extend');
            Route::post('{sub}/pause',  [CustomerSubscriptionController::class,'pause'])->name('pause');
            Route::post('{sub}/resume', [CustomerSubscriptionController::class,'resume'])->name('resume');
            Route::post('{sub}/cancel', [CustomerSubscriptionController::class,'cancel'])->name('cancel');
        });

        /* ======================================================
         | HOTSPOT WIZARD FLOW + AUTO PUSH + VPN STATUS
         ======================================================*/
        Route::prefix('locations/{location}/hotspots')
            ->name('hotspots.')
            ->group(function () {

                Route::get('add', [HotspotWizardController::class,'step1'])
                    ->name('wizard.step1');

                Route::post('add', [HotspotWizardController::class,'storeStep1'])
                    ->name('wizard.store.step1');

                Route::get('{hotspot}/router', [HotspotWizardController::class,'step2'])
                    ->name('wizard.step2');

                Route::post('{hotspot}/router', [HotspotWizardController::class,'storeStep2'])
                    ->name('wizard.store.step2');

                Route::get('{hotspot}/done', [HotspotWizardController::class,'done'])
                    ->name('wizard.done');

                // ✅ AUTO PUSH SCRIPT
                Route::post('{hotspot}/auto-push', [HotspotWizardController::class,'autoPush'])
                    ->name('autoPush');

                // ✅ VPN STATUS CHECKER
                Route::get('{hotspot}/vpn-status', [HotspotWizardController::class,'vpnStatus'])
                    ->name('vpnStatus');
            });

        /* ================= ROUTER TEST ================= */
        Route::get(
            'hotspots/{hotspot}/test-router',
            [HotspotController::class,'testRouter']
        )->name('hotspots.testRouter');

        /* ================= ROUTER STATUS ================= */
        Route::get(
            'hotspots/{hotspot}/router-status',
            [RouterStatusController::class,'check']
        )->name('hotspots.routerStatus');

    });

});
