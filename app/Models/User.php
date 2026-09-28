<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'usuarios';

    public $timestamps = false;

    protected $rememberTokenName = '';

    protected $fillable = [
        'roles_id',
        'correo',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'roles_id');
    }

    public function candidato(): HasOne
    {
        return $this->hasOne(Candidato::class, 'usuarios_id');
    }

    // Verifica si el usuario tiene el permiso especificado
    public function puede(string $permiso): bool
    {
        return $this->rol->permisos->contains('nombre', $permiso);
    }


    // Estas tres funciones son para verificar el rol del usuario
    public function esAdmin(): bool
    {
        return (int) $this->roles_id === 1;
    }

    public function esEmpresa(): bool
    {
        return (int) $this->roles_id === 2;
    }

    public function esCandidato(): bool
    {
        return (int) $this->roles_id === 3;
    }
}
