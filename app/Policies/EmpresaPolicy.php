<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    // Esta función dice quién puede editar el perfil de la empresa
    // En terminos tecnicos, cuando se abre o se envía /mi-empresa, se ejecuta esta función
    // y solo deja pasar a la empresa dueña de esa fila, con permiso de editar
    public function update(User $user, Empresa $empresa): bool
    {
        return $user->esEmpresa()
            && $user->puede('empresas.editar')
            && (int) $empresa->usuarios_id === (int) $user->id;
    }
}
