<?php

use Illuminate\Support\Facades\Route;

// ✅ Rate limiting imports
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

// Profile
use App\Http\Controllers\ProfileController;

// Admin core
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\CustomerSubscriptionController;
use App\Http\Controllers\Admin\Customers\CustomerSessionController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ReportController;

// Routers (Admin)
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\LocationRouterController;
use App\Http\Controllers\Admin\RouterProvisionController;
use App\Http\Controllers\Admin\RouterWizardController;
use App\Http\Controllers\Admin\RouterCleanupController;

// Step 3 Service Setup (Admin Area)
use App\Http\Controllers\Routers\RouterServiceSetupController;

// Public Provisioning (GET script)
use App\Http\Controllers\Provision\ProvisionController;

// Vouchers (Admin)
use App\Http\Controllers\Vouchers\VoucherController;

// Hotspot Portal Files + Portal Auth (PUBLIC)
use App\Http\Controllers\Routers\HotspotFilesController;
use App\Http\Controllers\Routers\HotspotPortalAuthController;

// Router Actions (Admin)
use App\Http\Controllers\Routers\RouterActionsController;

// ✅ Rotate credentials (Admin-only)
use App\Http\Controllers\Routers\RotateRouterApiPasswordController;

/*
|--------------------------------------------------------------------------
| ✅ Rate Limiter Definitions
|--------------------------------------------------------------------------
*/
RateLimiter::for('provision', function (Request $request) {
    return [
        Limit::perMinute(30)->by($request->ip()),
        Limit::perMinute(10)->by('prov:global'),
    ];
});

// ✅ Wizard status polling limiter (PUBLIC JSON)
RateLimiter::for('wizard-status', function (Request $request) {
    return [
        Limit::perMinute(120)->by($request->ip()),
        Limit::perMinute(30)->by('wiz:status:global'),
    ];
});

/*
|--------------------------------------------------------------------------
| Public Routes (NO AUTH)
|--------------------------------------------------------------------------
*/

// Test endpoint (plain text)
Route::get('/provision-test', function () {
    return response(
        ":log info \"KAIFIYE TEST OK\";\n/system identity print;\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8']
    );
})->name('provision.test');

/**
 * ✅ Provision script endpoint (GET text/plain)
 * MikroTik: /tool fetch url="https://app.kaafiye.online/provision/{token}"
 *
 * NOTE:
 * - Callback endpoint waa GET|POST /api/provision/callback/{token} (routes/api.php)
 * - Heartbeat endpoint waa GET|POST /api/routers/heartbeat (routes/api.php)
 */
Route::middleware('throttle:provision')
    ->get('/provision/{token}', [ProvisionController::class, 'script'])
    ->where('token', '[A-Za-z0-9\-_]+')
    ->name('provision.script');

/**
 * ✅ PUBLIC: Wizard status polling (JSON)
 * IMPORTANT: This route must NOT require auth, otherwise it redirects to /login and JS polling fails.
 */
Route::middleware('throttle:wizard-status')
    ->get('/wizard/routers/{router}/status', [RouterWizardController::class, 'status'])
    ->whereNumber('router')
    ->name('wizard.router.status');

/*
|--------------------------------------------------------------------------
| Hotspot Portal Files (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::prefix('hotspot-files')->group(function () {
    Route::get('{router}/login.html', [HotspotFilesController::class, 'login'])
        ->whereNumber('router')
        ->name('hotspot.files.login');

    Route::get('{router}/alogin.html', [HotspotFilesController::class, 'alogin'])
        ->whereNumber('router')
        ->name('hotspot.files.alogin');

    Route::get('{router}/error.html', [HotspotFilesController::class, 'error'])
        ->whereNumber('router')
        ->name('hotspot.files.error');

    Route::get('{router}/logout.html', [HotspotFilesController::class, 'logout'])
        ->whereNumber('router')
        ->name('hotspot.files.logout');
});

/*
|--------------------------------------------------------------------------
| Hotspot Portal Login (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::post('/portal/{router}/login', [HotspotPortalAuthController::class, 'login'])
    ->whereNumber('router')
    ->name('portal.login');

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
})->name('root');

/*
|--------------------------------------------------------------------------
| Authenticated Area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('admin.home');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Routers Dashboard UX (OPTIONAL)
    |--------------------------------------------------------------------------
    */
    if (class_exists(\App\Http\Controllers\Routers\RouterDashboardController::class)) {
        Route::prefix('routers')
            ->name('routers.dashboard.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Routers\RouterDashboardController::class, 'index'])
                    ->name('index');

                Route::get('{router}', [\App\Http\Controllers\Routers\RouterDashboardController::class, 'show'])
                    ->whereNumber('router')
                    ->name('show');
            });
    }

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Admin Area
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:super_admin|admin|support'])
        ->group(function () {

            Route::post('/devices/disconnect', [CustomerController::class, 'disconnectDevice'])
                ->name('devices.disconnect');

            Route::get('/home', [DashboardController::class, 'index'])->name('home');

            // ✅ Invoices
            Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

            // ✅ Audit Logs
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

            Route::resource('locations', LocationController::class);

            Route::resource('users', UserController::class)
                ->except(['show'])
                ->middleware('role:super_admin|admin');

            Route::resource('customers', CustomerController::class);

            Route::put(
                '/customers/{customer}/password',
                [CustomerController::class, 'updatePassword']
            )->name('customers.password.update');

            Route::post(
                '/customers/{customer}/clear-stale-sessions',
                [CustomerSessionController::class, 'clearStaleSessions']
            )->name('customers.clear-stale-sessions');

            Route::resource('subscriptions', SubscriptionController::class);

            Route::get(
                '/customers/{customer}/subscribe',
                [CustomerSubscriptionController::class, 'create']
            )->name('customers.subscribe');

            Route::post(
                '/customers/{customer}/subscribe',
                [CustomerSubscriptionController::class, 'store']
            )->name('customers.subscribe.store');

            Route::get(
                '/subs/{subscription}/extend',
                [CustomerSubscriptionController::class, 'extend']
            )->name('subs.extend');

            Route::post(
                '/subs/{subscription}/extend',
                [CustomerSubscriptionController::class, 'extendPost']
            )->name('subs.extend.post');

            Route::post(
                '/subs/{subscription}/pause',
                [CustomerSubscriptionController::class, 'pause']
            )->name('subs.pause');

            Route::post(
                '/subs/{subscription}/resume',
                [CustomerSubscriptionController::class, 'resume']
            )->name('subs.resume');

            Route::post(
                '/subs/{subscription}/cancel',
                [CustomerSubscriptionController::class, 'cancel']
            )->name('subs.cancel');

            /*
            |------------------------------------------------------------------
            | Vouchers
            |------------------------------------------------------------------
            */
            Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
            Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
            Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');

            Route::get('/vouchers/bulk', [VoucherController::class, 'bulkForm'])->name('vouchers.bulkForm');
            Route::post('/vouchers/bulk', [VoucherController::class, 'bulkStore'])->name('vouchers.bulkStore');

            /*
            |------------------------------------------------------------------
            | Routers Wizard – SUPER ADMIN + ADMIN
            |------------------------------------------------------------------
            */
            Route::middleware('role:super_admin|admin')->group(function () {

                // Wizard – Stage 1
                Route::get('routers/link-mikrotik', [RouterWizardController::class, 'stage1'])
                    ->name('routers.wizard.stage1');

                Route::post('routers/link-mikrotik', [RouterWizardController::class, 'storeStage1'])
                    ->name('routers.wizard.stage1.store');

                // Wizard – Stage 2 (Provisioning)
                Route::get('routers/{router}/wizard/provision', [RouterWizardController::class, 'stage2'])
                    ->whereNumber('router')
                    ->name('routers.wizard.stage2');

                // (OPTIONAL) Keep admin status route for old code
                Route::get('routers/{router}/wizard/status', [RouterWizardController::class, 'status'])
                    ->whereNumber('router')
                    ->name('routers.wizard.status');
            });

            /*
            |------------------------------------------------------------------
            | Routers – SUPER ADMIN ONLY
            |------------------------------------------------------------------
            */
            Route::middleware('role:super_admin')->group(function () {

                Route::get('routers/cleanup', [RouterCleanupController::class, 'preview'])
                    ->name('routers.cleanup.preview');

                Route::post('routers/cleanup', [RouterCleanupController::class, 'run'])
                    ->name('routers.cleanup.run');

                // Router CRUD (except index/show)
                Route::resource('routers', RouterController::class)
                    ->except(['index', 'show']);

                Route::get('routers/{router}/provision-command', [RouterProvisionController::class, 'command'])
                    ->whereNumber('router')
                    ->name('routers.provision-command');

                Route::post('routers/{router}/provision-token', [RouterProvisionController::class, 'generate'])
                    ->whereNumber('router')
                    ->name('routers.provision-token');

                // Service Setup (Step 3)
                Route::get('routers/{router}/service-setup', [RouterServiceSetupController::class, 'show'])
                    ->whereNumber('router')
                    ->name('routers.service-setup');

                Route::post('routers/{router}/service-setup', [RouterServiceSetupController::class, 'store'])
                    ->whereNumber('router')
                    ->name('routers.service-setup.store');

                // Rotate API pass
                Route::post('routers/{router}/rotate-api-password', RotateRouterApiPasswordController::class)
                    ->whereNumber('router')
                    ->name('routers.rotate-api-pass');

                // Add Router from Location
                Route::prefix('locations/{location}')
                    ->name('locations.')
                    ->group(function () {

                        Route::get('routers/create', [LocationRouterController::class, 'create'])
                            ->name('routers.create');

                        Route::post('routers', [LocationRouterController::class, 'store'])
                            ->name('routers.store');
                    });
            });

            /*
            |------------------------------------------------------------------
            | Routers (List + Show) – All admin roles
            |------------------------------------------------------------------
            */
            Route::get('routers', [RouterController::class, 'index'])->name('routers.index');

            Route::get('routers/{router}', [RouterController::class, 'show'])
                ->whereNumber('router')
                ->name('routers.show');

            /*
            |------------------------------------------------------------------
            | Router Actions – All admin roles
            |------------------------------------------------------------------
            */
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

            Route::post('routers/{router}/winbox/regenerate', [RouterActionsController::class, 'regenerateWinbox'])
                ->whereNumber('router')
                ->name('routers.winbox.regenerate');

            Route::post('routers/{router}/reprovision', [RouterActionsController::class, 'reprovision'])
                ->whereNumber('router')
                ->name('routers.reprovision');

            Route::post('routers/{router}/sync/hotspot-files', [RouterActionsController::class, 'syncHotspotFiles'])
                ->whereNumber('router')
                ->name('routers.hotspot.sync');

            Route::post('routers/{router}/sync/time', [RouterActionsController::class, 'syncRouterTime'])
                ->whereNumber('router')
                ->name('routers.time.sync');

            Route::post('routers/{router}/restart', [RouterActionsController::class, 'restart'])
                ->whereNumber('router')
                ->name('routers.restart');

            Route::post('routers/{router}/disable', [RouterActionsController::class, 'disable'])
                ->whereNumber('router')
                ->name('routers.disable');

            Route::post('routers/{router}/enable', [RouterActionsController::class, 'enable'])
                ->whereNumber('router')
                ->name('routers.enable');

            // UI placeholders
            Route::view('hotspots', 'admin/hotspots/index')->name('hotspots.index');
        });
});

require __DIR__ . '/auth.php';