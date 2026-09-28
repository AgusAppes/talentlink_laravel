<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $table = 'etapas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
