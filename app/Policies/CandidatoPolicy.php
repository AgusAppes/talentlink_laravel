<?php

namespace App\Policies;

use App\Models\Candidato;
use App\Models\User;

class CandidatoPolicy
{
    // Esta función dice quién puede ver el listado de candidatos
    // En terminos tecnicos, cuando se abre /candidatos, se ejecuta esta función
    // y solo deja pasar al admin con permiso de ver candidatos
    public function viewAny(User $user): bool
    {
        return $user->esAdmin() && $user->puede('candidatos.ver');
    }

    // Esta función dice quién puede ver el detalle de un candidato
    // En terminos tecnicos, cuando se abre /candidatos/{id}, se ejecuta esta función
    // y solo deja pasar al admin con permiso de ver candidatos
    public function view(User $user, Candidato $candidato): bool
    {
        return $user->esAdmin() && $user->puede('candidatos.ver');
    }

    // Esta función dice quién puede editar el perfil del candidato
    // En terminos tecnicos, cuando se abre o se envía /mi-perfil, se ejecuta esta función
    // y solo deja pasar al candidato dueño de esa fila, con permiso de editar
    public function update(User $user, Candidato $candidato): bool
    {
        return $user->esCandidato()
            && $user->puede('candidatos.editar')
            && (int) $candidato->usuarios_id === (int) $user->id;
    }
}
