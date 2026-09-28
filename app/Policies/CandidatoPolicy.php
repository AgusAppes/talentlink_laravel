<?php

namespace App\Policies;

use App\Models\Candidato;
use App\Models\User;

class CandidatoPolicy
{
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
