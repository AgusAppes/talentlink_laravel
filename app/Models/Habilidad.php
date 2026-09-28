<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habilidad extends Model
{
    protected $table = 'habilidades';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];
}
