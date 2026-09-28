<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoBusqueda extends Model
{
    protected $table = 'estado_busqueda';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
