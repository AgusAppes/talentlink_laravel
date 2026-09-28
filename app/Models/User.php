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
        // belongsTo significa que el usuario pertenece a un solo rol
        return $this->belongsTo(Rol::class, 'roles_id');
    }

    // Estas tres funciones son para obtener los modelos relacionados con el usuario
    // HasOne significa que el usuario tiene un solo candidato, empresa o personal de RRHH
    public function candidato(): HasOne
    {
        return $this->hasOne(Candidato::class, 'usuarios_id');
    }

    public function empresa(): HasOne
    {
        return $this->hasOne(Empresa::class, 'usuarios_id');
    }

    public function personalRrhh(): HasOne
    {
        return $this->hasOne(PersonalRrhh::class, 'usuarios_id');
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

    // Esta función arma el nombre que se muestra en el panel
    // En terminos tecnicos, cuando el navbar pide el nombre, se ejecuta esta función
    // y devuelve el perfil del rol (RRHH, empresa o candidato) o el correo si no hay perfil
    public function nombreVisible(): string
    {
        if ($this->esAdmin() && $this->personalRrhh) {
            return trim($this->personalRrhh->nombre.' '.$this->personalRrhh->apellido);
        }

        if ($this->esEmpresa() && $this->empresa) {
            return $this->empresa->nombre;
        }

        if ($this->esCandidato() && $this->candidato) {
            return trim($this->candidato->nombre.' '.$this->candidato->apellido);
        }

        return $this->correo;
    }

    // Esta función arma las iniciales del avatar
    // En terminos tecnicos, cuando el navbar dibuja el círculo, se ejecuta esta función
    // y toma la primera letra de las dos primeras palabras de nombreVisible()
    public function iniciales(): string
    {
        $palabras = explode(' ', trim($this->nombreVisible()));
        $iniciales = mb_strtoupper(mb_substr($palabras[0], 0, 1));

        if (isset($palabras[1]) && $palabras[1] !== '') {
            $iniciales .= mb_strtoupper(mb_substr($palabras[1], 0, 1));
        }

        return $iniciales;
    }

    // Esta función devuelve el nombre del rol en palabras
    // En terminos tecnicos, cuando el navbar muestra el rol, se ejecuta esta función
    // y traduce roles_id a Personal RRHH, Empresa o Candidato
    public function rolLegible(): string
    {
        if ($this->esAdmin()) {
            return 'Personal RRHH';
        }

        if ($this->esEmpresa()) {
            return 'Empresa';
        }

        return 'Candidato';
    }

    // Esta función dice a qué pantalla entra cada rol
    // En terminos tecnicos, cuando el login o el logo necesitan el inicio, se ejecuta esta función
    // y devuelve el nombre de ruta: dashboard, solicitudes.create u ofertas.index
    public function rutaInicio(): string
    {
        if ($this->esAdmin()) {
            return 'dashboard';
        }

        if ($this->esEmpresa()) {
            return 'solicitudes.create';
        }

        return 'ofertas.index';
    }
}
