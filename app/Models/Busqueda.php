<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Busqueda extends Model
{
    protected $table = 'busquedas';

    public $timestamps = false;

    protected $fillable = [
        'nombre_puesto',
        'empresas_id',
        'estado_busqueda_id',
    ];

    // Esta función trae la empresa que cargó la solicitud
    // En terminos tecnicos, cuando el listado muestra la columna Empresa, se ejecuta esta función
    // y busca la fila de empresas con el empresas_id de esta búsqueda
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresas_id');
    }

    // Esta función trae el estado de la solicitud
    // En terminos tecnicos, cuando el listado muestra el badge, se ejecuta esta función
    // y busca la fila de estado_busqueda con el estado_busqueda_id
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoBusqueda::class, 'estado_busqueda_id');
    }

    // Esta función trae el detalle de la solicitud
    // En terminos tecnicos, cuando el listado necesita vacantes, modalidad o ciudad, se ejecuta esta función
    // y busca la fila de detalle_busquedas con este busquedas_id
    public function detalle(): HasOne
    {
        return $this->hasOne(DetalleBusqueda::class, 'busquedas_id');
    }

    // Esta función trae las habilidades de la solicitud
    // En terminos tecnicos, cuando se guardan las habilidades, se ejecuta esta función
    // y usa la tabla habilidades_por_busqueda
    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Habilidad::class,
            'habilidades_por_busqueda',
            'busquedas_id',
            'habilidades_id'
        );
    }
}
