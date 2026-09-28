<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    // Revisa el permiso en la ruta. Si vienen varios separados por |, alcanza con uno.
    // El segundo argumento, si es "empresa", deja afuera a ese rol aunque tenga el permiso.
    public function handle(Request $request, Closure $next, string $permisos, string $excluir = ''): Response
    {
        $usuario = $request->user();

        if ($excluir === 'empresa' && $usuario->esEmpresa()) {
            abort(403);
        }

        foreach (explode('|', $permisos) as $permiso) {
            if ($usuario->puede($permiso)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
