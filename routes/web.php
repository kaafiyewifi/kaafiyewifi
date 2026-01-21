<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\LocationController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    /**
     * Dashboard (Breeze default)
     * Redirect to admin.home
     */
    Route::get('/dashboard', function () {
        return redirect()->route('admin.home');
    })->name('dashboard');

    /**
     * Breeze Profile routes
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * Admin area (super_admin, admin, agent)
     */
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:super_admin,admin,agent')
        ->group(function () {

            // ✅ Admin Home (Dashboard)
            Route::get('/home', [DashboardController::class, 'index'])->name('home');

            // ✅ Phase 3: Locations (Full CRUD + Profile page)
            Route::resource('locations', LocationController::class);

            // ===== UI PLACEHOLDERS (Phase 4/5/6/7/9) =====
            Route::view('/hotspots', 'admin/hotspots/index')->name('hotspots.index');
            Route::view('/reports', 'admin/reports/index')->name('reports.index');
            Route::view('/audit', 'admin/audit/index')->name('audit.index');
        });

    /**
     * Super Admin only
     */
    Route::prefix('sa')
        ->name('sa.')
        ->middleware('role:super_admin')
        ->group(function () {

            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('/users/{user}/role', [UserManagementController::class, 'setRole'])->name('users.setRole');
        });
});

// Breeze auth routes (login/logout/register/etc.)
require __DIR__ . '/auth.php';
