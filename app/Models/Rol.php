<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    public $timestamps = false;

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
