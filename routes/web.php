<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

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
        ->middleware('role:super_admin|admin|agent') // ✅ FIXED
        ->group(function () {

            Route::get('/home', [DashboardController::class, 'index'])->name('home');

            // ✅ Locations
            Route::resource('locations', LocationController::class);

            // ✅ Users Management (permission based)
            Route::resource('users', UserController::class)
                ->except(['show'])
                ->middleware('permission:manage users');

            // ✅ UI placeholders
            Route::view('/hotspots', 'admin/hotspots/index')->name('hotspots.index');
            Route::view('/reports', 'admin/reports/index')->name('reports.index');
            Route::view('/audit', 'admin/audit/index')->name('audit.index');
        });

}); // ✅ this closes auth group

require __DIR__ . '/auth.php';
