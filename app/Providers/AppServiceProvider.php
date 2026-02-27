<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    public function boot(UrlGenerator $url): void
    {
        config(['gemini.request_timeout' => 120]);
        Vite::prefetch(concurrency: 3);

        // Force HTTPS on Render (avoids mixed content warnings)
        if (config('app.env') === 'production') {
            $url->forceScheme('https');
        }
    }
}
