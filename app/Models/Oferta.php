<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oferta extends Model
{
    protected $table = 'ofertas';

    public $timestamps = false;

    protected $fillable = [
        'busquedas_id',
        'personal_rrhh_id',
        'estado_ofertas_id',
    ];

    // Esta función trae la solicitud de la oferta
    // En terminos tecnicos, cuando el listado muestra el puesto, se ejecuta esta función
    // y busca la fila de busquedas con el busquedas_id
    public function busqueda(): BelongsTo
    {
        return $this->belongsTo(Busqueda::class, 'busquedas_id');
    }

    // Esta función trae quién publicó la oferta
    // En terminos tecnicos, cuando el listado de admin muestra "Publicada por", se ejecuta esta función
    // y busca la fila de personal_rrhh con el personal_rrhh_id
    public function personalRrhh(): BelongsTo
    {
        return $this->belongsTo(PersonalRrhh::class, 'personal_rrhh_id');
    }

    // Esta función trae el estado de la oferta
    // En terminos tecnicos, cuando el listado muestra el badge, se ejecuta esta función
    // y busca la fila de estado_ofertas con el estado_ofertas_id
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoOferta::class, 'estado_ofertas_id');
    }

    // Esta función trae las postulaciones de la oferta
    // En terminos tecnicos, cuando se revisa si el candidato ya se postuló, se ejecuta esta función
    // y busca las filas de postulaciones con este ofertas_id
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class, 'ofertas_id');
    }
}
