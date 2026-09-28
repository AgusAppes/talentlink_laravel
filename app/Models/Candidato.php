<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Candidato extends Model
{
    protected $table = 'candidatos';

    public $timestamps = false;

    protected $fillable = [
        'usuarios_id',
        'nombre',
        'apellido',
        'fecha_nac',
        'ciudades_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarios_id');
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudades_id');
    }

    // Esta función trae las postulaciones del candidato
    // En terminos tecnicos, cuando el listado o el feed buscan sus postulaciones, se ejecuta esta función
    // y usa la tabla postulaciones_por_candidatos
    public function postulaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Postulacion::class,
            'postulaciones_por_candidatos',
            'candidatos_id',
            'postulaciones_id'
        );
    }
}
