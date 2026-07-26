<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS (from your previous setup)
        URL::forceScheme('https');

        // Dynamically toggle Debugbar based on the domain
        if (app()->bound('debugbar')) {
            if (request()->getHost() === 'dev.belajarcerdas.id') {
                app('debugbar')->enable();
            } else {
                app('debugbar')->disable();
            }
        }
    }
}