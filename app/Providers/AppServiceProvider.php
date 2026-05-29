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
        // Paksa semua URL asset menggunakan HTTPS jika diakses lewat ngrok/produksi
        if (str_contains(config('app.url'), 'ngrok-free.dev') || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}