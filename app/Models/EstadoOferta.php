<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoOferta extends Model
{
    protected $table = 'estado_ofertas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
