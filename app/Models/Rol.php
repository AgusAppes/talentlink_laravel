<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    public $timestamps = false;

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'roles_id');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(
            Permiso::class,
            'permisos_por_roles',
            'roles_id',
            'permisos_id'
        );
    }
}
