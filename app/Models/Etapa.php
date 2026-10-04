<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    public const EN_ENTREVISTA = 5;

    public const ENTREVISTA_CANCELADA = 6;

    protected $table = 'etapas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
