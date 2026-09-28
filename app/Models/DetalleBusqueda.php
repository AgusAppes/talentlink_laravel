<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleBusqueda extends Model
{
    protected $table = 'detalle_busquedas';

    public $timestamps = false;

    protected $fillable = [
        'busquedas_id',
        'descripcion',
        'cantidad_vacantes',
        'anios_experiencia',
        'modalidades_id',
        'ciudades_id',
        'provincias_id',
        'paises_id',
    ];

    // Esta función trae la solicitud de este detalle
    // En terminos tecnicos, cuando se necesita la búsqueda dueña del detalle, se ejecuta esta función
    // y busca la fila de busquedas con el busquedas_id
    public function busqueda(): BelongsTo
    {
        return $this->belongsTo(Busqueda::class, 'busquedas_id');
    }

    // Esta función trae la modalidad del puesto
    // En terminos tecnicos, cuando el listado muestra la modalidad, se ejecuta esta función
    // y busca la fila de modalidades con el modalidades_id
    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'modalidades_id');
    }

    // Esta función trae la ciudad de la solicitud
    // En terminos tecnicos, cuando el listado arma la ubicación, se ejecuta esta función
    // y busca la fila de ciudades con el ciudades_id
    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudades_id');
    }

    // Esta función trae la provincia de la solicitud
    // En terminos tecnicos, cuando se guardó la ciudad, se ejecuta esta función
    // y busca la fila de provincias con el provincias_id
    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincias_id');
    }

    // Esta función trae el país de la solicitud
    // En terminos tecnicos, cuando se guardó la ciudad, se ejecuta esta función
    // y busca la fila de paises con el paises_id
    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'paises_id');
    }
}
