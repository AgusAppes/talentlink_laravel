<?php

namespace App\Providers;

use Aws\Middleware;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use ReflectionMethod;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    // Esta función deja la paginación con el estilo de Bootstrap 5 y prepara R2
    // En terminos tecnicos, se ejecuta al arrancar la aplicación,
    // aplica las vistas de paginación de Bootstrap 5 y registra el disco s3 sin ACL
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        $this->configurarR2();
    }

    // Esta función deja el disco s3 usable con Cloudflare R2
    // En terminos tecnicos, se ejecuta al arrancar la aplicación y
    // quita el encabezado ACL de cada pedido, porque R2 rechaza esa subida
    private function configurarR2(): void
    {
        Storage::extend('s3', function ($app, array $config) {
            $crear = new ReflectionMethod($app->make('filesystem'), 'createS3Driver');
            $crear->setAccessible(true);
            $disco = $crear->invoke($app->make('filesystem'), $config);

            $disco->getClient()->getHandlerList()->appendBuild(
                Middleware::mapCommand(function ($comando) {
                    unset($comando['ACL']);

                    return $comando;
                }),
                'quitar-acl-r2'
            );

            return $disco;
        });
    }
}
