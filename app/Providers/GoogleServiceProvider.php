<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $isMobileRequest = request()->is('api/mobile/*');

        config([
            'services.google.redirect' => $isMobileRequest
                ? env('GOOGLE_REDIRECT_URI_API')
                : env('GOOGLE_REDIRECT_URI_WEB'),
        ]);
    }
}
