<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permiso' => \App\Http\Middleware\VerificarPermiso::class,
        ]);

        // Render termina HTTPS en su proxy. Sin esto Laravel arma los links en http.
        $middleware->trustProxies(at: '*');

        $middleware->redirectUsersTo(fn () => route(auth()->user()->rutaInicio()));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // El 419 es un formulario con la sesión vieja. Se vuelve a la pantalla con un aviso.
        $exceptions->respond(function ($response, $exception, $request) {
            if ($response->getStatusCode() !== 419 || $request->expectsJson()) {
                return $response;
            }

            return back()->with('error', 'La página expiró. Volvé a intentar.');
        });
    })->create();
