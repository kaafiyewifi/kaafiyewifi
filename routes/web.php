<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserController;

// ✅ Routers controllers
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\LocationRouterController;

// ✅ Provisioning controllers
use App\Http\Controllers\Admin\RouterProvisionController;
use App\Http\Controllers\Provision\ProvisionController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/**
 * ✅ Public endpoints (Router will call these)
 * - No auth middleware (router itself calls them)
 */
Route::get('/provision/{token}', [ProvisionController::class, 'script'])->name('provision.script');
Route::get('/router/callback', [ProvisionController::class, 'callback'])->name('router.callback');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return redirect()->route('admin.home');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * Admin area (super_admin, admin, agent)
     */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:super_admin|admin|agent')
        ->group(function () {

            Route::get('/home', [DashboardController::class, 'index'])->name('home');

            // ✅ Locations (everyone in admin area can access)
            Route::resource('locations', LocationController::class);

            // ✅ Users Management (permission based)
            Route::resource('users', UserController::class)
                ->except(['show'])
                ->middleware('permission:manage users');

            /**
             * ✅ Routers
             * Requirement:
             * - Sidebar "Routers" menu: SUPER ADMIN ONLY
             * - Global routers list (admin/routers): SUPER ADMIN ONLY
             * - BUT Router show page is used from Location profile, so show must be accessible to admin/agent (scoped by location access in controller)
             */
            Route::middleware('role:super_admin')->group(function () {

                // ✅ Global routers CRUD (SUPER ADMIN ONLY)
                // NOTE: show is not included here on purpose (see route below).
                Route::resource('routers', RouterController::class)->except(['show']);

                // ✅ Provision Token Generator (SUPER ADMIN ONLY)
                // POST /admin/routers/{router}/provision-token
                Route::post('routers/{router}/provision-token', [RouterProvisionController::class, 'generate'])
                    ->name('routers.provision-token');

                // Add Router from inside a Location profile
                // GET  /admin/locations/{location}/routers/create
                // POST /admin/locations/{location}/routers
                Route::prefix('locations/{location}')
                    ->name('locations.')
                    ->group(function () {
                        Route::get('routers/create', [LocationRouterController::class, 'create'])
                            ->name('routers.create');

                        Route::post('routers', [LocationRouterController::class, 'store'])
                            ->name('routers.store');
                    });
            });

            // ✅ Router show (allowed for super_admin|admin|agent)
            // Controller should enforce access via router->location and user->canAccessLocation()
            Route::get('routers/{router}', [RouterController::class, 'show'])
                ->name('routers.show');

            // ✅ UI placeholders
            Route::view('/hotspots', 'admin/hotspots/index')->name('hotspots.index');
            Route::view('/reports', 'admin/reports/index')->name('reports.index');
            Route::view('/audit', 'admin/audit/index')->name('audit.index');
        });

}); // closes auth group

require __DIR__ . '/auth.php';
