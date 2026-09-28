<?php

namespace App\Policies;

use App\Models\Postulacion;
use App\Models\User;

class PostulacionPolicy
{
    // Esta función dice quién puede ver las postulaciones
    // En terminos tecnicos, cuando se abre /postulaciones, se ejecuta esta función
    // y deja pasar al admin o al candidato con permiso de ver postulaciones
    public function viewAny(User $user): bool
    {
        return ($user->esAdmin() || $user->esCandidato()) && $user->puede('postulaciones.ver');
    }

    // Esta función dice quién puede postularse
    // En terminos tecnicos, cuando el candidato envía el formulario del feed, se ejecuta esta función
    // y solo deja pasar al candidato con permiso de crear postulaciones
    public function create(User $user): bool
    {
        return $user->esCandidato() && $user->puede('postulaciones.crear');
    }

    // Esta función dice quién puede cambiar la etapa
    // En terminos tecnicos, cuando el admin envía el formulario de una fila, se ejecuta esta función
    // y solo deja pasar al admin con permiso de gestionar postulaciones
    public function cambiarEtapa(User $user, Postulacion $postulacion): bool
    {
        return $user->esAdmin() && $user->puede('postulaciones.gestionar');
    }
}
