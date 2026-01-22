<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CustomerController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', fn () => redirect()->route('admin.home'))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:super_admin|admin|agent'])
        ->group(function () {

            Route::get('/home', [DashboardController::class, 'index'])->name('home');

            Route::resource('locations', LocationController::class);

            Route::resource('users', UserController::class)
                ->except(['show'])
                ->middleware('permission:manage users');

            // ✅ Customer routes (Admin UI)
            Route::middleware('permission:manage customers')->group(function () {
                Route::resource('customers', CustomerController::class);
            });

            Route::view('/hotspots', 'admin/hotspots/index')->name('hotspots.index');
            Route::view('/reports', 'admin/reports/index')->name('reports.index');
            Route::view('/audit', 'admin/audit/index')->name('audit.index');
        });

});

require __DIR__ . '/auth.php';
