<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    // Esta función revisa el permiso de la ruta
    // En terminos tecnicos, cuando se entra a una ruta con el middleware permiso, se ejecuta esta función
    // y deja pasar si el usuario tiene alguno de los permisos separados por |
    public function handle(Request $request, Closure $next, string $permisos): Response
    {
        foreach (explode('|', $permisos) as $permiso) {
            if ($request->user()->puede($permiso)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
