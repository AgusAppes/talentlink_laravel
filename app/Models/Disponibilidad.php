<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';

    public $timestamps = false;

    protected $fillable = [
        'personal_rrhh_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];
}
