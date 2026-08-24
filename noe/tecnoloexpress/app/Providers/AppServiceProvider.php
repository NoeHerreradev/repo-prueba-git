<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        // En producción (p. ej. Vercel) el TLS se termina en el proxy y la app
        // recibe la petición como http, generando enlaces http:// que el
        // navegador bloquea en una página https (mixed content). Forzamos https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
