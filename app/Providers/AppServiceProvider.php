<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema;
use App\Services\Routers\Contracts\RouterApi;
use App\Services\Routers\Clients\Pear2RouterApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RouterApi::class, Pear2RouterApi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for older MySQL versions (utf8mb4 index length)
        Schema::defaultStringLength(191);

        // 🔐 Global password policy (Phase 0 security)
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->numbers();
        });
    }
}
