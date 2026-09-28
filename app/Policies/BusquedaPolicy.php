<?php

namespace App\Policies;

use App\Models\Busqueda;
use App\Models\User;

class BusquedaPolicy
{
    // Esta función dice quién puede ver el listado de solicitudes
    // En terminos tecnicos, cuando se abre /solicitudes, se ejecuta esta función
    // y deja pasar al admin o a la empresa que puede crear solicitudes
    public function viewAny(User $user): bool
    {
        return $user->puede('solicitudes.ver')
            || ($user->esEmpresa() && $user->puede('solicitudes.crear'));
    }

    // Esta función dice quién puede cargar una solicitud
    // En terminos tecnicos, cuando se abre o se envía el formulario de alta, se ejecuta esta función
    // y solo deja pasar a la empresa con permiso de crear
    public function create(User $user): bool
    {
        return $user->esEmpresa() && $user->puede('solicitudes.crear');
    }

    // Esta función dice quién puede cambiar el estado
    // En terminos tecnicos, cuando el admin envía el formulario de la fila, se ejecuta esta función
    // y solo deja pasar al admin con permiso de editar solicitudes
    public function cambiarEstado(User $user, Busqueda $busqueda): bool
    {
        return $user->esAdmin() && $user->puede('solicitudes.editar');
    }
}
