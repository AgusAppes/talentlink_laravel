<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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

    // Esta función deja la paginación con el estilo de Bootstrap 5
    // En terminos tecnicos, se ejecuta al arrancar la aplicación y
    // hace que ->links() use las vistas de paginación de Bootstrap 5
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
