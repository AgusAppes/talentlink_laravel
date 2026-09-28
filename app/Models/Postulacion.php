<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    public $timestamps = false;

    protected $fillable = [
        'ofertas_id',
        'etapas_id',
    ];

    // Esta función trae la oferta de la postulación
    // En terminos tecnicos, cuando el listado muestra el puesto, se ejecuta esta función
    // y busca la fila de ofertas con el ofertas_id
    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class, 'ofertas_id');
    }

    // Esta función trae la etapa de la postulación
    // En terminos tecnicos, cuando el listado muestra el badge, se ejecuta esta función
    // y busca la fila de etapas con el etapas_id
    public function etapa(): BelongsTo
    {
        return $this->belongsTo(Etapa::class, 'etapas_id');
    }

    // Esta función trae los candidatos de la postulación
    // En terminos tecnicos, cuando el listado muestra el nombre, se ejecuta esta función
    // y usa la tabla postulaciones_por_candidatos
    public function candidatos(): BelongsToMany
    {
        return $this->belongsToMany(
            Candidato::class,
            'postulaciones_por_candidatos',
            'postulaciones_id',
            'candidatos_id'
        );
    }
}
