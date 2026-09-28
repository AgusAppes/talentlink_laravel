<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Esta función dice quién puede ver el listado de usuarios y de roles
    // En terminos tecnicos, cuando se abre /usuarios o /roles, se ejecuta esta función
    // y solo deja pasar al admin con permiso de ver usuarios
    public function viewAny(User $user): bool
    {
        return $user->esAdmin() && $user->puede('usuarios.ver');
    }

    // Esta función dice quién puede dar de alta un usuario
    // En terminos tecnicos, cuando se abre o se envía /usuarios/nuevo, se ejecuta esta función
    // y solo deja pasar al admin con permiso de administrar usuarios
    public function create(User $user): bool
    {
        return $user->esAdmin() && $user->puede('usuarios.administrar');
    }

    // Esta función dice quién puede editar los permisos de un rol
    // En terminos tecnicos, cuando se abre o se guarda /roles/{id}/permisos, se ejecuta esta función
    // y solo deja pasar al admin con permiso de administrar usuarios
    public function gestionarPermisos(User $user): bool
    {
        return $user->esAdmin() && $user->puede('usuarios.administrar');
    }
}
