<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Postulacion extends Model
{
    protected $table = 'postulaciones';

    protected $fillable = [
        'ofertas_id',
        'etapas_id',
        'cv',
        'compatibilidad',
        'compatibilidad_detalle',
    ];

    protected $casts = [
        'compatibilidad' => 'integer',
        'compatibilidad_detalle' => 'array',
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

    // Esta función arma el texto del porcentaje para el listado
    // En terminos tecnicos, cuando la tabla de postulaciones muestra la compatibilidad, se ejecuta esta función
    // y distingue un porcentaje de una evaluación sin datos o todavía no hecha
    public function textoCompatibilidad(): string
    {
        if ($this->compatibilidad !== null) {
            return $this->compatibilidad.'%';
        }

        if ($this->compatibilidad_detalle) {
            return 'Sin datos';
        }

        return 'Sin evaluar';
    }

    // Esta función elige el color del badge según el porcentaje
    // En terminos tecnicos, cuando la tabla pinta la compatibilidad, se ejecuta esta función
    // y devuelve una clase de Bootstrap
    public function claseCompatibilidad(): string
    {
        if ($this->compatibilidad === null) {
            return 'text-bg-secondary';
        }

        if ($this->compatibilidad >= 70) {
            return 'text-bg-success';
        }

        if ($this->compatibilidad >= 40) {
            return 'text-bg-warning';
        }

        return 'text-bg-danger';
    }

    // Esta función borra la postulación del candidato
    // En terminos tecnicos, cuando cancela desde la web o la app, se ejecuta esta función
    // y elimina el CV del disco y la fila de postulaciones
    public function eliminar(): void
    {
        if ($this->cv) {
            Storage::disk(config('filesystems.cv_disk'))->delete($this->cv);
        }

        $this->delete();
    }
}
