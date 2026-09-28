<?php

namespace App\Policies;

use App\Models\Oferta;
use App\Models\User;

class OfertaPolicy
{
    // Esta función dice quién puede ver las ofertas
    // En terminos tecnicos, cuando se abre /ofertas, se ejecuta esta función
    // y deja pasar a quien tiene el permiso ofertas.ver
    public function viewAny(User $user): bool
    {
        return $user->puede('ofertas.ver');
    }

    // Esta función dice quién puede publicar una oferta
    // En terminos tecnicos, cuando se abre o se envía el formulario de alta, se ejecuta esta función
    // y solo deja pasar al admin con permiso de crear ofertas
    public function create(User $user): bool
    {
        return $user->esAdmin() && $user->puede('ofertas.crear');
    }

    // Esta función dice quién puede cambiar el estado de la oferta
    // En terminos tecnicos, cuando el admin abre o guarda la edición, se ejecuta esta función
    // y solo deja pasar al admin con permiso de crear ofertas
    public function update(User $user, Oferta $oferta): bool
    {
        return $user->esAdmin() && $user->puede('ofertas.crear');
    }
}
